<?php

namespace App\Livewire\Memos;

use Carbon\Carbon;
use App\Models\Memo;
use App\Models\User;
use App\Models\Entity;
use Livewire\Component;
use App\Models\References;
use App\Models\Historiques;
use App\Models\MemoHistory;
use Illuminate\Support\Str;
use App\Models\ReplacesUser;
use App\Models\BlocEnregistrements;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Notifications\MemoActionNotification;
use App\Traits\ManageFavorites;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;

class IncomingMemos extends Component
{
    use ManageFavorites, WithPagination;

    // =========================================================
    // 1. PROPRIÉTÉS DU COMPOSANT
    // =========================================================

    public $search = '';
    public $isViewingPdf = false;

    // --- États des Modals ---
    public $isOpen = false;        
    public $isOpen2 = false;       
    public $isOpen3 = false;       
    public $isOpen4 = false;       
    public $isOpenReject = false;  
    public $isOpenTrans = false;   
    public $selectedMemo = null;

    // --- Données du Formulaire ---
    public $memo_id = null;
    
    #[Rule('required|string|max:255')]
    public string $object = '';

    #[Rule('required|string|max:255')]
    public string $concern = '';

    #[Rule('required|string')]
    public string $content = '';

    // --- Variables de Workflow ---
    public $memo_type = 'standard'; 
    public $workflow_comment = '';
    public $selected_visa = '';
    public $managerData = null;     
    public $projectUsersList = [];  
    public $selected_project_users = [];
    public $target_users_ids = [];

    // --- Variables de Rejet ---
    public $reject_comment = '';

    // --- Variables de Transmission ---
    public $transRecipients = [];
    public $generatedReference = '';

    // --- Données d'Affichage ---
    public $date;
    public $user_service;
    public $user_first_name;
    public $user_last_name;
    public $user_entity_name;

    public $pdfBase64 = '';



    // --- Options Statiques ---
    public $visaOptions = [
        'Vu' => 'Vu (Simple transmission)',
        'Vu & Accord' => 'Vu & D\'accord',
        'Vu & Pas d\'accord' => 'Vu & Pas d\'accord',
    ];

    // =========================================================
    // 2. ACTIONS DE NAVIGATION
    // =========================================================

    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Ouvre l'aperçu du mémo
     */
    public function viewMemo($id)
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($id);
        $this->memo_id = $memo->id;

        $pdf = Pdf::loadView('pdf.memo-layout', [
            'memo'               => $memo,
            'recipientsByAction' => $memo->destinataires->groupBy('action'),
            'date'               => $memo->created_at->format('d/m/Y'),
            'logo'               => $this->getLogoBase64(),
        ])->setPaper('a4', 'portrait');

        $this->pdfBase64 = base64_encode($pdf->output());
        $this->isViewingPdf = true;
        $this->isEditing = false;
    }

    public function closePdfView()
    {
        $this->isViewingPdf = false;
        $this->pdfBase64 = '';
    }

    private function getLogoBase64() {
        $path = public_path('images/logo.jpg');
        return file_exists($path) ? 'data:image/jpg;base64,' . base64_encode(file_get_contents($path)) : null;
    }

    public function closeModal() 
    { 
        $this->isOpen = false; 
        $this->reset(['selectedMemo', 'memo_id', 'content', 'object']);
    }

    public function closeModalTrois() 
    { 
        $this->isOpen3 = false; 
        $this->reset(['projectUsersList', 'managerData', 'workflow_comment']);
    }

    // =========================================================
    // 3. LOGIQUE D'ASSIGNATION & WORKFLOW
    // =========================================================

    public function assignMemo($id)
    {
        $this->memo_id = $id;
        $memo = Memo::findOrFail($id);

        $this->reset(['workflow_comment', 'selected_visa', 'selected_project_users']);
        $this->memo_type = 'standard';

        $currentUser = Auth::user();
        $targetUser = null;
        $today = Carbon::now()->format('Y-m-d');

        // Optimisation SQL : Récupération groupée de tous les remplacements
        $activeReplacements = ReplacesUser::where('date_begin_replace', '<=', $today)
            ->where('date_end_replace', '>=', $today)
            ->get()
            ->keyBy('user_id');

        $replacementContext = $this->getReplacementRights($memo);

        if ($replacementContext && $replacementContext['is_active']) {
            $titulaire = $replacementContext['original_user'];
            if ($titulaire->poste === 'Directeur') {
                $targetUser = User::where('entity_id', $titulaire->entity_id)->where('poste', 'Secretaire')->first();
            } else {
                $targetUser = $titulaire->manager_id ? User::find($titulaire->manager_id) : null;
            }
        } else {
            if ($currentUser->poste === 'Directeur') {
                $targetUser = User::where('entity_id', $currentUser->entity_id)->where('poste', 'Secretaire')->first();
            } else {
                $targetUser = $currentUser->manager_id ? User::find($currentUser->manager_id) : null;
            }
        }

        // managerData doit être compatible Blade ($data['effective']->name)
        $this->managerData = $targetUser ? $this->resolveUserAvailability($targetUser, $activeReplacements) : null;

        $excludeIds = [$currentUser->id];
        if ($replacementContext) $excludeIds[] = $replacementContext['original_user']->id;

        // IMPORTANT : projectUsersList reste une Collection pour supporter ->first() dans le Blade
        $this->projectUsersList = User::whereNotIn('id', $excludeIds)
            ->orderBy('last_name')
            ->get()
            ->map(fn($u) => $this->resolveUserAvailability($u, $activeReplacements));

        $this->isOpen3 = true;
    }

    public function sendMemo()
    {
        $this->validate([
            'selected_visa' => 'required',
            'workflow_comment' => 'nullable|string|max:1000',
            'selected_project_users' => 'required_if:memo_type,projet|array',
        ]);

        $memo = Memo::findOrFail($this->memo_id);
        $user = Auth::user();
        $today = Carbon::now()->format('Y-m-d');
        
        $activeReplacements = ReplacesUser::where('date_begin_replace', '<=', $today)
            ->where('date_end_replace', '>=', $today)
            ->get()
            ->keyBy('user_id');

        $replacementContext = $this->getReplacementRights($memo);
        $finalComment = $this->workflow_comment;

        if ($replacementContext && in_array('viser', $replacementContext['actions_allowed'])) {
            $titulaire = $replacementContext['original_user'];
            $finalComment = "[P/O " . $titulaire->poste . "] " . $this->workflow_comment;
        }

        $nextHolders = [];

        if ($this->memo_type === 'standard' && $this->managerData) {
            $nextHolders[] = $this->managerData['effective']->id;
        }

        if ($this->memo_type === 'projet') {
            $users = User::whereIn('id', $this->selected_project_users)->get();
            foreach ($users as $u) {
                $avail = $this->resolveUserAvailability($u, $activeReplacements);
                if ($avail) $nextHolders[] = $avail['effective']->id;
            }
        }

        if (empty($nextHolders)) {
            $this->addError('general', 'Destinataire invalide.'); return;
        }

        $memo->update([
            'previous_holders' => [$user->id],
            'current_holders' => array_unique($nextHolders),
            'status' => 'envoyer',
            'workflow_direction' => 'sortant',
            'workflow_comment' => $finalComment
        ]);

        Historiques::create([
            'user_id' => $user->id,
            'memo_id' => $memo->id,
            'visa'    => $this->selected_visa,
            'workflow_comment' => $finalComment ?? 'R.A.S',
        ]);

        foreach (User::whereIn('id', $nextHolders)->get() as $recipient) {
            try { $recipient->notify(new MemoActionNotification($memo, 'envoyer', $user)); } catch (\Exception $e) {}
        }

        $this->closeModalTrois();
        $this->dispatch('notify', message: "Transmis avec succès.");
    }

    // =========================================================
    // 4. SIGNATURE ÉLECTRONIQUE
    // =========================================================

    public function sign($id)
    {
        $memo = Memo::findOrFail($id);
        $user = Auth::user();
        $replacementContext = $this->getReplacementRights($memo);
        
        $token = now()->timestamp . "-" . Str::upper(Str::random(8));
        $historyData = "";

        if ($replacementContext && in_array('signer', $replacementContext['actions_allowed'])) {
            $titulaire = $replacementContext['original_user'];
            if ($titulaire->poste === 'Sous-Directeur') {
                $memo->signature_sd = 'SD-INT-' . $token;
            } elseif ($titulaire->poste === 'Directeur') {
                $memo->signature_dir = 'DIR-INT-' . $token;
                $memo->qr_code = (string) Str::uuid();
                $memo->status = 'envoyer';
            }
            $historyData = "Signature P/O de {$titulaire->full_name} par {$user->full_name}";
        } else {
             if ($user->poste === 'Sous-Directeur') {
                $memo->signature_sd = 'SD-' . $token;
                $historyData = "Signé par SD";
             } elseif ($user->poste === 'Directeur') {
                $memo->signature_dir = 'DIR-' . $token;
                $memo->qr_code = (string) Str::uuid();
                $memo->status = 'envoyer';
                $historyData = "Signé par DIR (Final)";
             }
        }

        $memo->save();
        Historiques::create([
            'user_id' => $user->id, 'memo_id' => $memo->id, 'visa' => 'Signé', 'workflow_comment' => $historyData
        ]);
        $this->dispatch('notify', message: "Signature apposée.");
    }

    // =========================================================
    // 5. TRANSMISSION (SECRÉTARIAT)
    // =========================================================

    public function transMemo($id)
    {
        $this->memo_id = $id;
        $memo = Memo::with('destinataires')->findOrFail($id);
        $today = Carbon::now()->format('Y-m-d');
    
        $targetEntityIds = $memo->destinataires->pluck('entity_id')->toArray();
        $activeReplacements = ReplacesUser::where('date_begin_replace', '<=', $today)
            ->where('date_end_replace', '>=', $today)
            ->get()
            ->keyBy('user_id');

        $this->transRecipients = User::whereIn('entity_id', $targetEntityIds)
            ->where('poste', 'Secretaire')
            ->get()
            ->map(fn($u) => $this->resolveUserAvailability($u, $activeReplacements));

        if ($this->transRecipients->isEmpty()) {
            $this->dispatch('notify', message: "Aucun secrétariat cible trouvé."); return; 
        }

        $this->generatedReference = $this->generateSmartReference($memo);
        $this->isOpenTrans = true;
    }

    public function closeTransModal()
    {
        $this->isOpenTrans = false;
        $this->reset(['generatedReference', 'transRecipients']);
    }

    public function confirmTrans()
    {
        $this->validate(['generatedReference' => 'required|string']);

        $memo = Memo::findOrFail($this->memo_id);
        $user = Auth::user();
        $nextHoldersIds = collect($this->transRecipients)->pluck('effective.id')->unique()->toArray();

        DB::transaction(function () use ($memo, $user, $nextHoldersIds) {
            BlocEnregistrements::create([
                'nature_memo' => 'Memo Sortant',
                'date_enreg' => now()->format('d/m/Y'),
                'reference' => $this->generatedReference,
                'memo_id' => $memo->id,
                'user_id' => $user->id,
            ]);

            $memo->update([
                'workflow_direction' => 'entrant', 
                'current_holders' => $nextHoldersIds,
                'status' => 'transmis',
                'reference' => $this->generatedReference,
            ]);

            Historiques::create([
                'user_id' => $user->id, 'memo_id' => $memo->id, 'visa' => 'Enregistré', 'workflow_comment' => "Réf: " . $this->generatedReference
            ]);
        });

        $this->closeTransModal();
        $this->dispatch('notify', message: "Enregistré et transmis !");
    }

    private function generateSmartReference($memo)
{
    $year = now()->year;
    $memoCreator = \App\Models\User::find($memo->user_id);

    if (!$memoCreator) return null;

    // 1. CALCUL DU COMPTEUR
    // On compte les enregistrements dans 'blocs_enregistrements' pour l'entité du créateur cette année
    $count = \App\Models\BlocEnregistrements::whereYear('created_at', $year)
        ->whereHas('user', function($q) use ($memoCreator) {
            $q->where('entity_id', $memoCreator->entity_id);
        })
        ->count() + 1;

    $baseNum = sprintf("%04d", $count);

    // 2. RÉCUPÉRATION DE L'HISTORIQUE DES VISAS
    // On récupère tous les gens qui ont agi sur ce mémo
    $historiques = \App\Models\Historiques::with(['user.entity', 'user.sousDirection'])
        ->where('memo_id', $memo->id)
        ->get();

    // Variables pour construire les segments
    $entRef      = ""; // Segment Entité (Directeur)
    $sdRef       = ""; // Segment Sous-Direction (Sous-Directeur)
    $deptName    = ""; // Segment Département (Chef de Dept)
    $serviceName = ""; // Segment Service (Chef de Service)
    $initials    = ""; // Segment Initiales (Employé / Stagiaire)

    foreach ($historiques as $log) {
        $u = $log->user;
        if (!$u) continue;

        $poste = \Illuminate\Support\Str::lower($u->poste);
        $visa  = $log->visa; // Ex: "Vu & Accord"
        
        // On considère un accord si le mot "Accord" est présent dans le visa
        $isAccord = \Illuminate\Support\Str::contains($visa, 'Accord');

        // --- RÈGLE DIRECTEUR ---
        if (\Illuminate\Support\Str::contains($poste, 'Directeur') && !\Illuminate\Support\Str::contains($poste, 'sous')) {
            $entRef = $u->entity->ref ?? "";
        }
        
        // --- RÈGLE SOUS-DIRECTEUR ---
        elseif (\Illuminate\Support\Str::contains($poste, 'Sous-Directeur')) {
            $sdRef = $u->sousDirection->ref ?? "";
        }

        // --- RÈGLE CHEF DE DÉPARTEMENT ---
        elseif (\Illuminate\Support\Str::contains($poste, 'Chef-Departement')) {
            if ($isAccord) {
                $deptName = $u->departement;
            }
        }

        // --- RÈGLE CHEF DE SERVICE ---
        elseif (\Illuminate\Support\Str::contains($poste, 'Chef-Service')) {
            if ($isAccord) {
                $serviceName = $u->service;
            }
        }

        // --- RÈGLE EMPLOYÉ / STAGIAIRE ---
        elseif (
            \Illuminate\Support\Str::contains($poste, 'Employer') || 
            \Illuminate\Support\Str::contains($poste, 'stagiaire') || 
            \Illuminate\Support\Str::contains($poste, 'professionnel')
        ) {
            if ($isAccord) {
                $first = \Illuminate\Support\Str::substr($u->first_name, 0, 1);
                $last  = \Illuminate\Support\Str::substr($u->last_name, 0, 1);
                $initials = \Illuminate\Support\Str::upper($first . $last);
            }
        }
    }

    // 3. ASSEMBLAGE DE LA CHAÎNE
    // On construit le tableau dans l'ordre hiérarchique décroissant
    $segments = [];
    $segments[] = $baseNum;

    if (!empty($entRef))      $segments[] = \Illuminate\Support\Str::upper($entRef);
    if (!empty($sdRef))       $segments[] = \Illuminate\Support\Str::upper($sdRef);
    if (!empty($deptName))    $segments[] = \Illuminate\Support\Str::upper($deptName);
    if (!empty($serviceName)) $segments[] = \Illuminate\Support\Str::upper($serviceName);
    if (!empty($initials))    $segments[] = $initials;

    // Jointure avec des slashes, en filtrant les éléments vides au cas où
    return implode('/', array_filter($segments));
}

    // =========================================================
    // 6. GESTION DU REJET
    // =========================================================

    public function askReject($id)
    {
        $this->memo_id = $id;
        $this->reject_comment = '';
        $this->isOpenReject = true;
    }

    public function closeRejectModal() { $this->isOpenReject = false; }

    public function processReject()
    {
        $this->validate(['reject_comment' => 'required|min:5']);

        $memo = Memo::findOrFail($this->memo_id);
        $user = Auth::user();
        
        $prev = is_array($memo->previous_holders) ? $memo->previous_holders : json_decode($memo->previous_holders, true);
        $backTo = !empty($prev) ? end($prev) : $memo->user_id;

        $memo->update([
            'status' => 'rejeter',
            'workflow_direction' => 'sortant',
            'current_holders' => [$backTo],
            'workflow_comment' => $this->reject_comment
        ]);

        Historiques::create([
            'user_id' => $user->id, 'memo_id' => $memo->id, 'visa' => 'Rejeté', 'workflow_comment' => $this->reject_comment,
        ]);

        $this->closeRejectModal();
        $this->dispatch('notify', message: "Mémo rejeté.");
    }

    // =========================================================
    // 7. SYSTÈME DE REMPLACEMENT (Fixé pour Blade)
    // =========================================================

    private function resolveUserAvailability($user, $activeReplacements)
    {
        if (!$user) return null;

        $replacement = $activeReplacements->get($user->id);

        if ($replacement) {
            $replacingUser = User::find($replacement->user_id_replace);
            if ($replacingUser) {
                return [
                    'original'    => (object) $user->only(['id', 'first_name', 'last_name', 'poste', 'departement']),
                    'effective'   => (object) $replacingUser->only(['id', 'first_name', 'last_name', 'poste', 'departement']),
                    'is_replaced' => true
                ];
            }
        }

        return [
            'original'    => (object) $user->only(['id', 'first_name', 'last_name', 'poste', 'departement']),
            'effective'   => (object) $user->only(['id', 'first_name', 'last_name', 'poste', 'departement']),
            'is_replaced' => false
        ];
    }

    public function getReplacementRights($memo)
    {
        $user = Auth::user();
        $today = Carbon::now()->format('Y-m-d');
        $prev = is_array($memo->previous_holders) ? $memo->previous_holders : json_decode($memo->previous_holders, true);
        
        if (empty($prev)) return null;
        $lastSender = User::find(end($prev));
        if (!$lastSender) return null;

        $rep = ReplacesUser::where('user_id_replace', $user->id)
            ->where('date_begin_replace', '<=', $today)
            ->where('date_end_replace', '>=', $today)
            ->where('user_id', $lastSender->manager_id)
            ->first();

        if ($rep) {
            $replaced = User::find($rep->user_id);
            return [
                'is_active' => true,
                'original_user' => $replaced,
                'actions_allowed' => is_array($rep->action_replace) ? $rep->action_replace : explode(',', (string)$rep->action_replace),
            ];
        }
        return null;
    }

    // =========================================================
    // 8. PDF & FAVORIS
    // =========================================================

    public function downloadMemoPDF()
    {
        $memo = Memo::findOrFail($this->memo_id);
        $pdf = Pdf::loadView('pdf.memo-layout', [
            'memo' => $memo,
            'recipientsByAction' => $memo->destinataires->groupBy('action'),
            'date' => $memo->created_at->format('d/m/Y'),
            'logo' => $this->getLogoBase64(),
        ]);
        return response()->streamDownload(fn() => print($pdf->output()), "Memo_{$memo->id}.pdf");
    }

    public function render()
    {
        $memos = Memo::with(['user.entity', 'destinataires.entity'])
            ->where('workflow_direction', 'sortant')
            ->whereNotIn('status', ['transmis', 'rejeter']) 
            ->whereJsonContains('current_holders', Auth::id())
            ->when($this->search, function($q) {
                $term = '%'.$this->search.'%';
                $q->where(fn($sub) => $sub->where('object', 'like', $term)->orWhere('concern', 'like', $term));
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(9);

        return view('livewire.memos.incoming-memos', ['memos' => $memos]); 
    }
}