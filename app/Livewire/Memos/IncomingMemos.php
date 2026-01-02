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
 * Logique partagée pour récupérer les données du PDF
 */
    private function getPdfData($memo)
    {
        // On cherche le directeur de l'entité du créateur du mémo
        $director = User::where('entity_id', $memo->user->entity_id)
                        ->where('poste', 'Directeur')
                        ->first();

        return [
            'memo'               => $memo,
            'recipientsByAction' => $memo->destinataires->groupBy('action'),
            'date'               => $memo->created_at->format('d/m/Y'),
            'logo'               => $this->getLogoBase64(),
            'director'           => $director, // On passe l'objet director à la vue
        ];
    }

    /**
     * Ouvre l'aperçu du mémo
     */
    public function viewMemo($id)
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($id);
        $this->memo_id = $memo->id;

        // Utilisation de la méthode partagée
        $pdf = Pdf::loadView('pdf.memo-layout', $this->getPdfData($memo))
              ->setPaper('a4', 'portrait');

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

    // 3. LOGIQUE INTELLIGENTE DE GÉNÉRATION DE RÉFÉRENCE
    private function generateSmartReference($memo)
    {
        

        $currentYear = now()->year;
    
        $count = BlocEnregistrements::where('nature_memo', 'Memo Sortant')
            ->where('user_id', Auth::id())
            ->whereYear('created_at', $currentYear)
            ->count() + 1;
        
        // 2. Données de base du créateur du mémo
        $creator = User::with(['entity', 'sousDirection'])->find($memo->user_id);
        
        // Préparation des segments
        $refEntity = $creator->entity->ref ?? 'ENT';
        $refSD = $creator->sousDirection->ref ?? 'SD'; // Assure-toi d'avoir la relation dans User
        $refDept = $creator->departement;
        $refService = $creator->service;
        $userInitials = Str::upper(substr($creator->first_name, 0, 1) . substr($creator->last_name, 0, 1));

        // 3. Vérification des Visas dans l'historique
        // On récupère tous les visas "Vu & Accord" pour ce mémo
        $validations = Historiques::where('memo_id', $memo->id)
                                  ->where('visa', 'Vu & Accord')
                                  ->pluck('user_id')
                                  ->toArray();

        // SCÉNARIO 1 : Le créateur a validé lui-même (Vu & Accord)
        if (in_array($creator->id, $validations)) {
            // Format : N°/Entity/SD/Dept/Service/Initiales
            return sprintf("%04d/%s/%s/%s/%s/%s", $count, $refEntity, $refSD, $refDept, $refService, $userInitials);
        }

        // Pour les étapes suivantes, on remonte la hiérarchie du créateur
        // Note: Cela suppose que la hiérarchie est bien définie via manager_id
        
        $n1 = $creator->manager_id ? User::find($creator->manager_id) : null;
        
        // SCÉNARIO 2 : Le N+1 (Chef Service ?) a validé
        // On vérifie si N1 existe, s'il a validé, et si son poste contient "Service"
        if ($n1 && in_array($n1->id, $validations) && Str::contains($n1->poste, 'Service')) {
             // Format : N°/Entity/SD/Dept/Service
             return sprintf("%04d/%s/%s/%s/%s", $count, $refEntity, $refSD, $refDept, $refService);
        }

        // On cherche le N+2 (Chef Département ?)
        $n2 = $n1 && $n1->manager_id ? User::find($n1->manager_id) : null;

        // SCÉNARIO 3 : Le N+2 (Chef Dept) a validé
        if ($n2 && in_array($n2->id, $validations) && Str::contains($n2->poste, 'Département')) {
             // Format : N°/Entity/SD/Dept
             return sprintf("%04d/%s/%s/%s", $count, $refEntity, $refSD, $refDept);
        }

        // SCÉNARIO 4 (Par défaut ou Sous-Directeur) : 
        // Si personne "en bas" n'a validé complètement, on garde la ref haute
        // Format : N°/Entity/SD
        return sprintf("%04d/%s/%s", $count, $refEntity, $refSD);
    }

    private function abbreviate($string)
    {
        if (empty($string)) return '';
        
        // Prend les premières lettres de chaque mot majuscule
        // Ex simple: juste les 3 premières lettres en majuscule
        // Tu peux faire une logique plus complexe avec des Regex
        return Str::upper(substr($string, 0, 3)); 
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
        
        // Récupération de l'historique des détenteurs
        $prev = is_array($memo->previous_holders) ? $memo->previous_holders : json_decode($memo->previous_holders ?? '[]', true);
        
        // CIBLE DU REJET : Le dernier expéditeur, sinon le créateur du mémo
        $backToId = !empty($prev) ? end($prev) : $memo->user_id;

        // Mise à jour du mémo
        $memo->update([
            'status' => 'rejeter',
            'workflow_direction' => 'sortant',
            'current_holders' => [$backToId],
            'workflow_comment' => $this->reject_comment
        ]);

        // Enregistrement dans l'historique des actions
        Historiques::create([
            'user_id' => $user->id, 
            'memo_id' => $memo->id, 
            'visa' => 'Rejeté', 
            'workflow_comment' => $this->reject_comment,
        ]);

        // NOTIFICATION : On récupère l'objet User du destinataire
        $recipient = User::find($backToId);
        if ($recipient) {
            try { 
                $recipient->notify(new MemoActionNotification($memo, 'rejeter', $user)); 
            } catch (\Exception $e) {
                // Silencieusement échoué ou logger l'erreur
            }
        }

        $this->closeRejectModal();
        $this->dispatch('notify', message: "Le mémo a été rejeté et renvoyé.");
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
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($this->memo_id);
        $pdf = Pdf::loadView('pdf.memo-layout', $this->getPdfData($memo));
        
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