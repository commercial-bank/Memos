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

class IncomingMemos extends Component
{
    use ManageFavorites;

    // =========================================================
    // 1. PROPRIÉTÉS DU COMPOSANT
    // =========================================================

    // --- Recherche & Pagination ---
    public $search = '';

    // --- États des Modals ---
    public $isOpen = false;        // Aperçu
    public $isOpen2 = false;       // Édition (Brouillon)
    public $isOpen3 = false;       // Assignation / Envoi
    public $isOpen4 = false;       // Suppression
    public $isOpenReject = false;  // Rejet
    public $isOpenTrans = false;   // Transmission Secrétaire
    public $selectedMemo = null;

    // --- Données du Formulaire (Mémorandum) ---
    public $memo_id = null;
    
    #[Rule('required|string|max:255')]
    public string $object = '';

    #[Rule('required|string|max:255')]
    public string $concern = '';

    #[Rule('required|string')]
    public string $content = '';

    // --- Variables de Workflow (Envoi / Assignation) ---
    public $memo_type = 'standard'; // 'standard' ou 'projet'
    public $workflow_comment = '';
    public $selected_visa = '';
    public $managerData = null;     // ['original', 'effective', 'is_replaced']
    public $projectUsersList = [];  // Liste éligible mode projet
    public $selected_project_users = [];
    public $target_users_ids = [];

    // --- Variables de Rejet ---
    public $reject_comment = '';

    // --- Variables de Transmission (Secrétariat) ---
    public $transRecipients = [];
    public $generatedReference = '';

    // --- Données d'Affichage (Aperçu PDF/Vue) ---
    public $date;
    public $user_service;
    public $user_first_name;
    public $user_last_name;
    public $user_entity_name;

    // --- Options Statiques ---
    public $visaOptions = [
        'Vu' => 'Vu (Simple transmission)',
        'Vu & Accord' => 'Vu & D\'accord',
        'Vu & Pas d\'accord' => 'Vu & Pas d\'accord',
    ];

    // =========================================================
    // 2. ACTIONS DE NAVIGATION & OUVERTURE MODALS
    // =========================================================

    /**
     * Ouvre l'aperçu du mémo en lecture seule
     */
    public function viewMemo($id)
    {
        // On charge le mémo avec ses destinataires ET les entités liées
        $this->selectedMemo = Memo::with(['user', 'destinataires.entity'])->findOrFail($id);
        
        // On remplit les autres variables pour l'affichage
        $this->memo_id = $this->selectedMemo->id;
        $this->object = $this->selectedMemo->object;
        $this->concern = $this->selectedMemo->concern;
        $this->content = $this->selectedMemo->content;
        $this->date = $this->selectedMemo->created_at->format('d/m/Y');
        
        $this->user_entity_name = $this->selectedMemo->user->entity->name ?? 'Entité';
        $this->user_service = $this->selectedMemo->user->service;

        $this->isOpen = true;
    }

    /**
     * Remplit les variables d'affichage pour l'aperçu
     */
    private function fillMemoDataView($memo)
    {
        $this->memo_id = $memo->id;
        $this->object = $memo->object;
        $this->concern = $memo->concern;
        $this->content = $memo->content;
        $this->date = $memo->created_at->format('d/m/Y');
        $entity = Entity::find($memo->user->entity_id);
        $this->user_entity_name = $entity->name ?? 'Entité';
        $this->user_service = $memo->user->service;
    }

    public function closeModal() { $this->isOpen = false; }
    public function closeModalTrois() { $this->isOpen3 = false; }

    // =========================================================
    // 3. LOGIQUE D'ASSIGNATION & WORKFLOW D'ENVOI
    // =========================================================

    /**
     * Initialise le modal d'envoi avec calcul automatique du destinataire (N+1)
     */
    public function assignMemo($id)
    {
        $this->memo_id = $id;
        $memo = Memo::find($id);

        $this->reset(['workflow_comment', 'selected_visa', 'selected_project_users']);
        $this->memo_type = 'standard';

        $currentUser = Auth::user();
        $targetUser = null;

        // Analyse du contexte de remplacement
        $replacementContext = $this->getReplacementRights($memo);

        // --- SCÉNARIO A : L'ACTEUR EST UN REMPLAÇANT ---
        if ($replacementContext && $replacementContext['is_active']) {
            $titulaire = $replacementContext['original_user'];

            if ($titulaire->poste === 'Directeur') {
                $targetUser = User::where('entity_id', $titulaire->entity_id)->where('poste', 'Secretaire')->first();
                if (!$targetUser) $this->addError('general', "Aucune secrétaire trouvée pour le Directeur remplacé.");
            } else {
                if ($titulaire->manager_id) $targetUser = User::find($titulaire->manager_id);
                else $this->addError('general', "Le titulaire remplacé n'a pas de manager défini.");
            }
        } 
        // --- SCÉNARIO B : L'ACTEUR EST LE TITULAIRE ---
        else {
            if ($currentUser->poste === 'Directeur') {
                $targetUser = User::where('entity_id', $currentUser->entity_id)->where('poste', 'Secretaire')->first();
                if (!$targetUser) $this->addError('general', "Aucune secrétaire trouvée dans votre entité.");
            } else {
                if ($currentUser->manager_id) $targetUser = User::find($currentUser->manager_id);
            }
        }

        // Résolution de disponibilité du destinataire final
        if ($targetUser) {
            $this->managerData = $this->resolveUserAvailability($targetUser);
        } else {
            $this->managerData = null;
        }

        // Préparation de la liste pour le Mode Projet
        $excludeIds = [$currentUser->id];
        if ($replacementContext) $excludeIds[] = $replacementContext['original_user']->id;

        $this->projectUsersList = User::whereNotIn('id', $excludeIds)
            ->orderBy('last_name')
            ->get()
            ->map(fn($u) => $this->resolveUserAvailability($u));

        $this->isOpen3 = true;
    }

    /**
     * Exécute l'envoi effectif du mémo (Standard ou Projet)
     */
    public function sendMemo()
    {
        $this->validate([
            'selected_visa' => 'required',
            'workflow_comment' => 'nullable|string|max:1000',
            'selected_project_users' => 'required_if:memo_type,projet|array',
        ], [
            'selected_project_users.required_if' => 'En mode projet, veuillez sélectionner au moins un collaborateur.'
        ]);

        $memo = Memo::find($this->memo_id);
        $user = Auth::user();
        
        // Gestion de la mention P/O
        $replacementContext = $this->getReplacementRights($memo);
        $finalComment = $this->workflow_comment;

        if ($replacementContext && in_array('viser', $replacementContext['actions_allowed'])) {
            $titulaire = $replacementContext['original_user'];
            $finalComment = "[P/O " . $titulaire->poste . "] " . $this->workflow_comment;
        }

        $nextHolders = [];

        // Circuit Standard
        if ($this->memo_type === 'standard') {
            if ($this->managerData && isset($this->managerData['effective'])) {
                $nextHolders[] = $this->managerData['effective']->id;
            } else {
                $this->addError('general', "Erreur : Le destinataire n'est pas défini.");
                return;
            }
        }

        // Circuit Projet
        if ($this->memo_type === 'projet') {
            foreach ($this->selected_project_users as $userId) {
                $target = User::find($userId);
                if ($target) {
                    $avail = $this->resolveUserAvailability($target);
                    if ($avail && $avail['effective']) $nextHolders[] = $avail['effective']->id;
                }
            }
        }

        if (empty($nextHolders)) {
            $this->addError('general', 'Aucun destinataire valide trouvé.');
            return;
        }

        // Mise à jour DB
        $memo->previous_holders = [$user->id];
        $memo->current_holders = array_unique($nextHolders);
        $memo->status = 'envoyer';
        $memo->workflow_direction = 'sortant';
        $memo->workflow_comment = $finalComment;
        $memo->save();

        Historiques::create([
            'user_id' => $user->id,
            'memo_id' => $memo->id,
            'visa'    => $this->selected_visa,
            'workflow_comment' => $finalComment ?? 'R.A.S',
        ]);

        // Notifications
        foreach (User::whereIn('id', $nextHolders)->get() as $recipient) {
            try { $recipient->notify(new MemoActionNotification($memo, 'envoyer', $user)); } catch (\Exception $e) {}
        }

        $this->closeModalTrois();
        $this->dispatch('notify', message: "Le mémo a été transmis avec succès.");
    }

    // =========================================================
    // 4. LOGIQUE DE SIGNATURE ÉLECTRONIQUE (SD / DIR)
    // =========================================================

    public function sign($id)
    {
        $memo = Memo::findOrFail($id);
        $user = Auth::user();
        $replacementContext = $this->getReplacementRights($memo);
        
        $tokenBase = now()->timestamp . "-" . Str::upper(Str::random(10));
        $message = "";
        $historyData = "";

        // --- CAS : SIGNATURE PAR REMPLAÇANT ---
        if ($replacementContext && in_array('signer', $replacementContext['actions_allowed'])) {
            $titulaire = $replacementContext['original_user'];

            if ($titulaire->poste === 'Sous-Directeur') {
                if ($memo->signature_sd) return;
                $memo->signature_sd = 'SD-INT-' . $tokenBase;
                $message = "Signature Sous-Directeur (P/O) apposée.";
            } 
            elseif ($titulaire->poste === 'Directeur') {
                if ($memo->signature_dir) return;
                $memo->signature_dir = 'DIR-INT-' . $tokenBase;
                $memo->qr_code = (string) Str::uuid();
                $memo->status = 'envoyer';
                $message = "Signature Directeur (P/O) apposée.";
            }
            $historyData = "Signature P/O de M./Mme {$titulaire->last_name} ({$titulaire->poste}) par {$user->first_name} {$user->last_name}";
        } 
        // --- CAS : SIGNATURE PAR TITULAIRE ---
        else {
             if ($user->poste === 'Sous-Directeur') {
                $memo->signature_sd = 'SD-' . $tokenBase;
                $message = "Signature Sous-Directeur apposée.";
                $historyData = "Signature Sous-Directeur : " . $memo->signature_sd;
             } elseif ($user->poste === 'Directeur') {
                $memo->signature_dir = 'DIR-' . $tokenBase;
                $memo->qr_code = (string) Str::uuid();
                $memo->status = 'envoyer';
                $message = "Signature Directeur apposée.";
                $historyData = "Signature Directeur Finale : " . $memo->signature_dir;
             } else {
                 $this->dispatch('notify', message: "Droit de signature non autorisé.");
                 return;
             }
        }

        $memo->save();
        Historiques::create([
            'user_id' => $user->id, 'memo_id' => $memo->id, 'visa' => 'Signé', 'workflow_comment' => $historyData
        ]);
        $this->dispatch('notify', message: $message);
    }

    // =========================================================
    // 5. TRANSMISSION & ENREGISTREMENT (SECRÉTARIAT)
    // =========================================================

    public function transMemo($id)
    {
        $this->memo_id = $id;
        $memo = Memo::with('destinataires')->findOrFail($id);
    
        $targetEntityIds = $memo->destinataires->pluck('entity_id')->toArray();
        $rawSecretaries = User::whereIn('entity_id', $targetEntityIds)->where('poste', 'Secretaire')->get();

        $this->transRecipients = $rawSecretaries->map(fn($u) => $this->resolveUserAvailability($u));

        if ($this->transRecipients->isEmpty()) {
            $this->dispatch('notify', message: "Erreur : Aucune secrétaire trouvée.");
            return; 
        }

        $this->generatedReference = $this->generateSmartReference($memo);
        $this->isOpenTrans = true;
    }

        /**
     * Ferme le modal de transmission et réinitialise les données liées
     */
    public function closeTransModal()
    {
        $this->isOpenTrans = false;
        $this->generatedReference = '';
        $this->transRecipients = [];
        $this->resetErrorBag(); // Nettoie les messages d'erreur
    }

    public function confirmTrans()
    {
        $this->validate(['generatedReference' => 'required|string|max:50']);

        $memo = Memo::findOrFail($this->memo_id);
        $currentUser = Auth::user();
        $nextHoldersIds = $this->transRecipients->pluck('effective.id')->unique()->toArray();

        DB::transaction(function () use ($memo, $currentUser, $nextHoldersIds) {
            $finalRef = $this->generatedReference;
            
            BlocEnregistrements::create([
                'nature_memo' => 'Memo Sortant',
                'date_enreg' => now()->format('d/m/Y'),
                'reference' => $finalRef,
                'memo_id' => $memo->id,
                'user_id' => $currentUser->id,
            ]);

            $memo->update([
                'workflow_direction' => 'entrant', 
                'workflow_comment' => "Enregistré sous n° " . $finalRef,
                'current_holders' => $nextHoldersIds,
                'previous_holders' => [$currentUser->id], 
                'status' => 'transmis',
                'reference' => $finalRef,
            ]);

            Historiques::create([
                'user_id' => $currentUser->id, 'memo_id' => $memo->id, 'visa' => 'Enregistré & Transmis', 'workflow_comment' => "Réf: " . $finalRef
            ]);
        });

        // Notifications
        foreach (User::whereIn('id', $nextHoldersIds)->get() as $dest) {
            try { $dest->notify(new MemoActionNotification($memo, 'envoyer', $currentUser)); } catch (\Exception $e) {}
        }

        if ($currentUser->manager_id) {
            $mDispo = $this->resolveUserAvailability(User::find($currentUser->manager_id));
            if ($mDispo && $mDispo['effective']) {
                try { $mDispo['effective']->notify(new MemoActionNotification($memo, 'transmis', $currentUser)); } catch (\Exception $e) {}
            }
        }

        $this->isOpenTrans = false;
        $this->dispatch('notify', message: "Enregistré et transmis !");
    }

    /**
     * Algorithme de génération de référence intelligente
     */
    private function generateSmartReference($memo)
    {
        $currentYear = now()->year;
        $currentUser = Auth::user();
        $entityId = $currentUser->entity_id;

        $count = BlocEnregistrements::whereYear('created_at', $currentYear)
            ->whereHas('user', fn($q) => $q->where('entity_id', $entityId))
            ->count() + 1;
        
        $creator = User::with(['entity', 'sousDirection'])->find($memo->user_id);
        
        $refEntity = $creator->entity->ref ?? 'ENT';
        $refSD = $creator->sousDirection->ref ?? 'SD'; 
        $refDept = $this->abbreviate($creator->departement);
        $refService = $this->abbreviate($creator->service);
        $userInitials = Str::upper(substr($creator->first_name, 0, 1) . substr($creator->last_name, 0, 1));

        $validations = Historiques::where('memo_id', $memo->id)->where('visa', 'Vu & Accord')->pluck('user_id')->toArray();
        $baseNum = sprintf("%04d", $count);

        if (in_array($creator->id, $validations)) {
            return "{$baseNum}/{$refEntity}/{$refSD}/{$refDept}/{$refService}/{$userInitials}";
        } else {
            $n1 = $creator->manager_id ? User::find($creator->manager_id) : null;
            $n2 = $n1 && $n1->manager_id ? User::find($n1->manager_id) : null;

            if ($n1 && in_array($n1->id, $validations) && Str::contains($n1->poste, 'Service')) {
                return "{$baseNum}/{$refEntity}/{$refSD}/{$refDept}/{$refService}";
            } elseif ($n2 && in_array($n2->id, $validations) && Str::contains($n2->poste, 'Département')) {
                return "{$baseNum}/{$refEntity}/{$refSD}/{$refDept}";
            }
            return "{$baseNum}/{$refEntity}/{$refSD}";
        }
    }

    // =========================================================
    // 6. GESTION DU REJET
    // =========================================================

    public function askReject($id)
    {
        $this->memo_id = $id;
        $this->reject_comment = '';
        $this->resetValidation();
        $this->isOpenReject = true;
    }

    public function closeRejectModal() { $this->isOpenReject = false; }

    public function processReject()
    {
        $this->validate(['reject_comment' => 'required|string|min:5|max:500']);

        $memo = Memo::findOrFail($this->memo_id);
        $user = Auth::user();
        $replacementContext = $this->getReplacementRights($memo);
        $finalReason = $this->reject_comment;

        if ($replacementContext) {
            if (in_array('rejeter', $replacementContext['actions_allowed'])) {
                $titulaire = $replacementContext['original_user'];
                $finalReason = "[REJET P/O " . $titulaire->poste . "] " . $this->reject_comment;
            } else {
                $this->addError('reject_comment', "Action non autorisée en remplacement."); return;
            }
        }

        $previousHolders = is_string($memo->previous_holders) ? json_decode($memo->previous_holders, true) : $memo->previous_holders;
        $backToUserId = !empty($previousHolders) ? end($previousHolders) : $memo->user_id;

        $memo->update([
            'status' => 'rejeter',
            'workflow_direction' => 'sortant',
            'workflow_comment' => $finalReason,
            'current_holders' => [$backToUserId],
            'previous_holders' => [$user->id],
        ]);

        Historiques::create([
            'user_id' => $user->id, 'memo_id' => $memo->id, 'visa' => 'Rejeté', 'workflow_comment' => $finalReason,
        ]);

        if ($receiver = User::find($backToUserId)) {
            try { $receiver->notify(new MemoActionNotification($memo, 'rejeter', $user)); } catch (\Exception $e) {}
        }

        $this->closeRejectModal();
        $this->dispatch('notify', message: "Mémo renvoyé à l'expéditeur.");
    }

    // =========================================================
    // 7. SYSTÈME DE DÉLÉGATION & REMPLACEMENT (LOGIQUE COEUR)
    // =========================================================

    /**
     * Résout si un utilisateur est remplacé à la date du jour
     */
    private function resolveUserAvailability($user)
    {
        if (!$user) return null;
        $today = Carbon::now()->format('Y-m-d'); 

        $replacement = ReplacesUser::where('user_id', $user->id)
            ->where('date_begin_replace', '<=', $today)
            ->where('date_end_replace', '>=', $today)
            ->first();

        if ($replacement && ($replacingUser = User::find($replacement->user_id_replace))) {
            return ['original' => $user, 'effective' => $replacingUser, 'is_replaced' => true];
        }

        return ['original' => $user, 'effective' => $user, 'is_replaced' => false];
    }

    /**
     * Calcule les droits de remplacement de l'acteur courant sur un mémo précis
     */
    public function getReplacementRights($memo)
    {
        $user = Auth::user();
        $today = Carbon::now()->format('Y-m-d');
        $previousHolders = is_array($memo->previous_holders) ? $memo->previous_holders : json_decode($memo->previous_holders, true);
        
        if (empty($previousHolders)) return null;

        $lastSender = User::find(end($previousHolders));
        if (!$lastSender) return null;

        $replacements = ReplacesUser::where('user_id_replace', $user->id)
            ->where('date_begin_replace', '<=', $today)
            ->where('date_end_replace', '>=', $today)
            ->get();

        foreach ($replacements as $replacement) {
            $replacedUser = User::find($replacement->user_id);
            if (!$replacedUser) continue;

            // L'acteur courant remplace le manager de celui qui a envoyé le mémo
            if ($lastSender->manager_id == $replacedUser->id) {
                $rawActions = $replacement->action_replace;
                $actions = is_array($rawActions) ? array_map('strtolower', $rawActions) 
                           : explode(',', str_replace(' ', '', strtolower($rawActions)));

                return [
                    'is_active' => true,
                    'original_user' => $replacedUser,
                    'actions_allowed' => $actions,
                ];
            }
        }
        return null;
    }

    // =========================================================
    // 8. AUTRES MÉTHODES (FAVORIS, PDF, ABREVIATIONS)
    // =========================================================

    public function toggleFavorite($memoId)
    {
        $userId = Auth::id();
        $existing = \App\Models\Favoris::where('user_id', $userId)->where('memo_id', $memoId)->first();

        if ($existing) {
            $existing->delete();
            $this->dispatch('notify', message: "Retiré des favoris.");
        } else {
            \App\Models\Favoris::create(['user_id' => $userId, 'memo_id' => $memoId]);
            $this->dispatch('notify', message: "Ajouté aux favoris !");
        }
    }

    private function abbreviate($string)
    {
        if (empty($string)) return '';
        $clean = Str::lower(Str::replace(['.', "'", "’", "-"], ' ', Str::ascii($string)));
        $words = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY);
        
        if (count($words) === 1) return ($words[0] === 'departement') ? 'DP' : Str::upper($words[0]);

        $ignored = ['le', 'la', 'les', 'l', 'un', 'une', 'des', 'du', 'de', 'd', 'et', 'ou', 'a', 'au', 'aux', 'en', 'par', 'pour', 'sur', 'dans'];
        $acronym = '';

        foreach ($words as $word) {
            if (in_array($word, $ignored)) continue;
            $acronym .= ($word === 'departement') ? 'DP' : substr($word, 0, 1);
        }
        return Str::upper($acronym);
    }

    public function downloadMemoPDF()
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($this->memo_id);
        $recipientsByAction = $memo->destinataires->groupBy('action');

        $pathLogo = public_path('images/logo.jpg');
        $logoBase64 = file_exists($pathLogo) ? 'data:image/jpg;base64,' . base64_encode(file_get_contents($pathLogo)) : null;

        $qrCodeBase64 = null;
        if ($memo->qr_code) {
            $qrImage = QrCode::format('svg')->size(100)->generate(route('memo.verify', $memo->qr_code));
            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrImage);
        }

        $pdf = Pdf::loadView('pdf.memo-layout', [
            'memo' => $memo,
            'recipientsByAction' => $recipientsByAction,
            'logo' => $logoBase64,
            'qrCode' => $qrCodeBase64,
            'date' => $memo->created_at->format('d/m/Y'),
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(fn() => print($pdf->output()), 'Memo_' . $memo->id . '.pdf');
    }

    public function render()
    {
        $userId = Auth::id(); 

        $memos = Memo::with(['user', 'destinataires.entity'])
            ->where('workflow_direction', 'sortant')
            ->whereNotIn('status', ['transmis', 'rejeter']) 
            ->whereJsonContains('current_holders', $userId)
            ->withExists(['favoritedBy as is_favorited' => fn($q) => $q->where('user_id', $userId)])
            ->where(fn($q) => $q->where('object', 'like', '%'.$this->search.'%')->orWhere('concern', 'like', '%'.$this->search.'%'))
            ->orderBy('updated_at', 'desc')
            ->paginate(9);

        return view('livewire.memos.incoming-memos', ['memos' => $memos]); 
    }
}