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
use App\Models\Destinataires;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Models\BlocEnregistrements;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Notifications\MemoActionNotification;
use Livewire\Attributes\Rule;

class Incoming2Memos extends Component
{
    // =========================================================
    // 1. PROPRIÉTÉS DU COMPOSANT
    // =========================================================

    // --- Recherche & UI ---
    public $search = '';
    public $comment = ''; 
    public $date;

    // --- États des Modals ---
    public $isOpen = false;                 // Aperçu
    public $isRegistrationModalOpen = false; // Enregistrement (Secrétaire)
    public $isTransModalOpen = false;        // Transmission / Cotation
    public $isCloseModalOpen = false;        // Clôture
    public $isOpenTrans = false;             // Transmission standard (éventuelle)

    // --- Données du Mémo Principal ---
    public $memo_id = null;
    
    #[Rule('required|string|max:255')]
    public string $object = '';

    #[Rule('required|string|max:255')]
    public string $concern = '';

    #[Rule('required|string')]
    public string $content = '';

    // --- Données de l'Utilisateur (Vue) ---
    public $user_service;
    public $user_first_name;
    public $user_last_name;
    public $user_entity_name;
    public $selections = [];

    // --- Logique d'Enregistrement (Secrétariat) ---
    #[Rule('required|string')]
    public $reg_reference = ''; 
    #[Rule('required|string')]
    public $reg_nature = '';    
    #[Rule('required|string')]
    public $reg_objet = '';     
    #[Rule('required|string')]
    public $reg_expediteur = ''; 
    #[Rule('required|string')]
    public $reg_date = '';

    // --- Logique de Transmission / Cotation ---
    public $memoIdToTrans = null;
    public $targetRecipients = [];    
    public $selectedRecipients = [];  
    public $targetRoleName = '';      
    public $managerData = null; 
    public $memo_type = 'standard';
    public $projectUsersList = []; 
    public $selected_project_users = [];
    public $transRecipients = [];
    public $generatedReference = '';

    // --- Logique de Clôture ---
    public $memoIdToClose = null;
    public $closingComment = ''; 

    // --- Logique de Réponse (Parent/Child) ---
    public $isCreatingReply = false; 
    public $parent_id = null;        
    public $new_object = '';
    public $new_concern = '';
    public $new_content = '';
    public $recipients = [];         
    public $attachments = [];        
    public $newRecipientEntity = '';
    public $newRecipientAction = '';
    public $actionsList = ['Faire le nécessaire', 'Prendre connaissance', 'Prendre position', 'Décider'];

    // =========================================================
    // 2. INITIALISATION & APERÇU
    // =========================================================

    public function viewMemo($id)
    {
        $memo = Memo::with('user')->findOrFail($id);
        $this->fillMemoDataView($memo);
        $this->isOpen = true;
    }

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

    // =========================================================
    // 3. LOGIQUE D'ENREGISTREMENT (SECRÉTARIAT)
    // =========================================================

    /**
     * Point d'entrée pour la transmission (Bouton Avion)
     */
    public function transMemo($id)
    {
        $this->resetValidation();
        $this->memoIdToTrans = $id;
        $this->comment = '';
        $this->selectedRecipients = [];
        
        $memo = Memo::with('user.entity')->find($id);
        if (!$memo) return;

        $user = Auth::user();
        $poste = Str::lower(trim($user->poste));

        // Si Secrétaire : Vérification d'enregistrement préalable
        if (Str::contains($poste, 'secretaire')) {
            $dejaEnregistre = BlocEnregistrements::where('memo_id', $id)
                ->where('user_id', $user->id)
                ->exists();

            if ($dejaEnregistre) {
                $this->dispatch('notify', message: "Ce mémo est déjà enregistré. Passage direct à la transmission.");
                $this->prepareTransmission($id);
                return;
            }

            // Pré-remplissage du modal d'enregistrement
            $this->reg_reference = $memo->reference ?? '';
            $this->reg_date = Carbon::now()->format('d/m/Y');
            $this->reg_objet = $memo->object; 
            $this->reg_nature = 'Memo Entrant'; 
            $this->reg_expediteur = $memo->user->entity->name ?? 'Entité Inconnue';

            $this->isRegistrationModalOpen = true; 
        } else {
            $this->prepareTransmission($id);
        }
    }

    /**
     * Sauvegarde l'enregistrement dans le chrono et continue vers la cotation
     */
    public function saveRegistrationAndContinue()
    {
        $this->validate([
            'reg_reference' => 'required|string',
            'reg_nature' => 'required|string',
            'reg_objet' => 'required|string',
            'reg_expediteur' => 'required|string',
            'reg_date' => 'required|string',
        ]);

        $user = Auth::user();

        BlocEnregistrements::create([
            'reference'   => $this->reg_reference,
            'date_enreg'  => $this->reg_date,
            'nature_memo' => $this->reg_nature,
            'objet'       => $this->reg_objet,
            'memo_id'     => $this->memoIdToTrans,
            'user_id'     => $user->id,
        ]);

        $memo = Memo::find($this->memoIdToTrans);
        if ($memo && empty($memo->reference)) {
            $memo->update(['reference' => $this->reg_reference]);
        }

        $this->isRegistrationModalOpen = false;
        $this->dispatch('notify', message: "Enregistrement effectué. Passage à la transmission.");

        $this->prepareTransmission($this->memoIdToTrans);
    }

    public function closeRegistrationModal()
    {
        $this->isRegistrationModalOpen = false;
        $this->memoIdToTrans = null;
    }

    // =========================================================
    // 4. LOGIQUE DE TRANSMISSION ET COTATION
    // =========================================================

    /**
     * Prépare la liste des destinataires selon la hiérarchie du poste
     */
    private function prepareTransmission($id)
    {
        $memo = Memo::find($id);
        $user = Auth::user();
        $poste = Str::lower(trim($user->poste)); 
        
        $query = User::query()
            ->where('entity_id', $user->entity_id)
            ->where('id', '!=', $user->id);

        // --- Détermination des cibles selon le poste ---
        if (Str::contains($poste, 'secretaire')) {
            $this->targetRoleName = 'Directeur';
            $query->where('poste', 'like', '%Directeur%')->where('poste', 'not like', '%Sous-Directeur%');
        }
        elseif (Str::contains($poste, 'directeur') && !Str::contains($poste, 'sous')) {
            $this->targetRoleName = 'Sous-Directeur';
            $query->where('poste', 'like', '%Sous-Directeur%');
        }
        elseif (Str::contains($poste, 'sous-directeur')) {
            $this->targetRoleName = 'Collaborateurs (Chefs de Dép. & autres)';
            if ($user->sous_direction_id) $query->where('sous_direction_id', $user->sous_direction_id);
            $query->where(fn($q) => $q->where('poste', 'not like', '%Directeur%')->where('poste', 'not like', '%Sous-Directeur%')->where('poste', 'not like', '%Secretaire%'));
        }
        elseif (Str::contains($poste, 'chef-departement')) {
            $this->targetRoleName = 'Collaborateurs (Chefs de Svc. & autres)';
            $query->where('departement', $user->departement)
                  ->where(fn($q) => $q->where('poste', 'not like', '%Directeur%')->where('poste', 'not like', '%Chef-Departement%')->where('poste', 'not like', '%Secretaire%'));
        }
        elseif (Str::contains($poste, 'chef-service')) {
            $this->targetRoleName = 'Collaborateurs du Service';
            $query->where('service', $user->service)
                  ->where(fn($q) => $q->where('poste', 'not like', '%Directeur%')->where('poste', 'not like', '%Chef-Departement%')->where('poste', 'not like', '%Chef-Service%')->where('poste', 'not like', '%Secretaire%'));
        }
        else {
            $this->dispatch('notify', message: "Votre poste ne permet pas la transmission automatique.");
            return;
        }

        $this->targetRecipients = $query->orderBy('poste', 'asc')->orderBy('first_name', 'asc')->get();

        if ($this->targetRecipients->isEmpty()) {
            $this->dispatch('notify', message: "Aucun destinataire éligible trouvé pour le groupe : {$this->targetRoleName}.");
            return;
        }

        if ($this->targetRecipients->count() === 1) {
            $this->selectedRecipients[] = $this->targetRecipients->first()->id;
        }

        $this->isTransModalOpen = true;
    }

    /**
     * Valide et exécute la transmission (Cotation)
     */
    public function confirmTransmission()
    {
        $this->validate([
            'selectedRecipients' => 'required|array|min:1',
        ], ['selectedRecipients.required' => 'Veuillez sélectionner au moins un destinataire.']);

        $memo = Memo::find($this->memoIdToTrans);
        if (!$memo) {
            $this->dispatch('notify', message: "Erreur : Mémo introuvable.");
            $this->closeTransModal();
            return;
        }

        $senderId = Auth::id();
        $senderUser = Auth::user(); 
        $holders = $memo->current_holders;
        
        // Normalisation JSON
        if (is_null($holders)) $holders = [];
        elseif (is_string($holders)) $holders = json_decode($holders, true) ?? [];

        // Retrait de l'expéditeur et ajout des nouveaux détenteurs
        $holders = array_values(array_diff($holders, [$senderId]));
        foreach ($this->selectedRecipients as $recipientId) {
            $rId = (int) $recipientId;
            if (!in_array($rId, $holders)) $holders[] = $rId;
        }

        $memo->current_holders = $holders;
        $memo->save();

        // Historique et Notifications
        $nextHolders = User::whereIn('id', $this->selectedRecipients)->get();
        foreach ($nextHolders as $recipient) {
            Historiques::create([
                'user_id' => $senderId,
                'memo_id' => $memo->id,
                'visa'    => 'Coté / Transmis', 
                'workflow_comment' => $this->comment . " (Pour: " . $recipient->first_name . " " . $recipient->last_name . ")"
            ]);

            try {
                $recipient->notify(new MemoActionNotification($memo, 'cotation', $senderUser));
            } catch (\Exception $e) {}
        }

        $this->dispatch('notify', message: "Mémo coté et transmis avec succès.");
        $this->closeTransModal();
    }

    public function closeTransModal()
    {
        $this->isTransModalOpen = false;
        $this->selectedRecipients = [];
        $this->comment = '';
        $this->memoIdToTrans = null;
        $this->targetRecipients = [];
    }

    // =========================================================
    // 5. GESTION DE LA CLÔTURE (FIN DE TRAITEMENT)
    // =========================================================

    public function openCloseModal($id)
    {
        $this->memoIdToClose = $id;
        $this->closingComment = '';
        $this->isCloseModalOpen = true;
    }

    public function cancelCloseModal()
    {
        $this->isCloseModalOpen = false;
        $this->memoIdToClose = null;
        $this->closingComment = '';
    }
    
    /**
     * Confirme le traitement local pour l'entité de l'utilisateur
     */
    public function confirmCloseMemo()
    {
        $errorMessage = $this->checkDecisionBlock($this->memoIdToClose);
        if ($errorMessage) {
            $this->dispatch('notify', message: $errorMessage);
            $this->cancelCloseModal();
            return;
        }

        $memo = Memo::find($this->memoIdToClose);
        $user = Auth::user();
        
        $myDestinataireRecord = Destinataires::where('memo_id', $memo->id)
            ->where('entity_id', $user->entity_id)
            ->first();

        if (!$myDestinataireRecord) {
            $this->dispatch('notify', message: "Erreur : Votre entité n'est pas destinataire.");
            return;
        }

        // Mise à jour locale
        $myDestinataireRecord->update([
            'processing_status' => 'traiter',
            'completed_at' => now()
        ]);

        

        // Historique
        Historiques::create([
            'user_id' => $user->id,
            'memo_id' => $memo->id,
            'visa'    => 'Terminé (Entité)',
            'workflow_comment' => $this->closingComment ?: "Traitement terminé pour " . $user->entity->name
        ]);

         // 3. LOGIQUE CRUCIALE : Vérification globale
        // On compte combien d'entités n'ont PAS encore fini (ni traiter, ni decision_prise, ni repondu)
        $pendingEntities = Destinataires::where('memo_id', $memo->id)
            ->whereNotIn('processing_status', ['traiter', 'decision_prise', 'repondu'])
            ->count();

        if ($pendingEntities === 0) {
            $memo->workflow_direction = "terminer";
        }

        $memo->save();
        $this->dispatch('notify', message: "Dossier traité pour votre entité.");
        $this->cancelCloseModal();
    }

    /**
     * Action spécifique pour les entités ayant l'action "Décider"
     */
    public function submitDecision($id, $decision) 
    {
        $memo = Memo::find($id);
        $user = Auth::user();

        $destRecord = Destinataires::where('memo_id', $memo->id)
            ->where('entity_id', $user->entity_id)
            ->where('action', 'like', '%Décider%')
            ->first();

        if (!$destRecord) return;

        $destRecord->update([
            'processing_status' => 'decision_prise', 
            'completed_at' => now()
        ]);

        $memo->save();

        Historiques::create([
            'user_id' => $user->id,
            'memo_id' => $memo->id,
            'visa'    => 'DÉCISION RENDUE',
            'workflow_comment' => "Décision : " . strtoupper($decision)
        ]);

        $this->dispatch('notify', message: "Décision enregistrée. Les autres entités peuvent maintenant clôturer.");
    }

    // =========================================================
    // 6. LOGIQUE DE RÉPONSE (CRÉATION D'UN NOUVEAU MÉMO LIÉ)
    // =========================================================

    public function replyMemo($id)
    {
        $errorMessage = $this->checkDecisionBlock($id);
        if ($errorMessage) {
            $this->dispatch('notify', message: $errorMessage);
            return; 
        }

        $parent = Memo::with('user.entity')->find($id);
        if (!$parent) return;

        $this->parent_id = $id;
        $this->new_object = "RE: " . $parent->object;
        $this->new_concern = "Réponse au mémo réf: " . ($parent->reference ?? 'N/A');
        $this->new_content = ""; 
        
        $this->recipients = [];
        if($parent->user && $parent->user->entity_id) {
            $this->recipients[] = [
                'entity_id' => $parent->user->entity_id,
                'entity_name' => $parent->user->entity->name,
                'action' => 'Faire le nécessaire'
            ];
        }

        $this->isCreatingReply = true; 
    }

    public function saveReply()
    {
        $this->validate([
            'new_object'  => 'required|string|max:255',
            'new_concern' => 'required|string|max:255',
            'new_content' => 'required',
            'recipients'  => 'required|array|min:1',
        ]);

        if (!$this->parent_id) {
            $this->dispatch('notify', message: "Erreur de lien parent.");
            return;
        }

        $currentUser = Auth::user();
        $poste = Str::lower(trim($currentUser->poste));
        $targetId = null;
        $targetType = ""; 

        // Détermination du circuit de validation (ton code existant)
        if (Str::contains($poste, 'directeur') && !Str::contains($poste, 'sous-directeur')) {
            $secretary = User::where('entity_id', $currentUser->entity_id)->where('poste', 'like', '%secretaire%')->where('is_active', true)->first();
            $targetId = $secretary ? $secretary->id : $currentUser->manager_id;
            $targetType = $secretary ? "au Secrétariat" : "au Manager";
        } else {
            $targetId = $currentUser->manager_id;
            $targetType = "au Manager pour validation";
        }

        if (!$targetId) {
            $this->dispatch('notify', message: "Aucun destinataire de validation trouvé.");
            return;
        }

        $newMemo = null;

        DB::transaction(function () use (&$newMemo, $currentUser, $targetId, $targetType) {
            
            // =========================================================
            // 1. MISE À JOUR DU MÉMO PARENT (STATUS DE L'ENTITÉ)
            // =========================================================
            $parentMemo = Memo::find($this->parent_id);
            
            if ($parentMemo) {
                // ON CHERCHE LA LIGNE DESTINATAIRE DU PARENT POUR TON ENTITÉ
                $myParentDestRecord = Destinataires::where('memo_id', $parentMemo->id)
                    ->where('entity_id', $currentUser->entity_id)
                    ->first();

                if ($myParentDestRecord) {
                    // MISE À JOUR DU STATUS DE TRAITEMENT À "REPONDU"
                    $myParentDestRecord->update([
                        'processing_status' => 'repondu',
                        'completed_at' => now()
                    ]);
                }

               

                // VÉRIFICATION GLOBALE : Clôturer le parent si TOUT LE MONDE a fini
                $pendingEntities = Destinataires::where('memo_id', $parentMemo->id)
                    ->whereNotIn('processing_status', ['traiter', 'decision_prise', 'repondu'])
                    ->count();

                if ($pendingEntities === 0) {
                    $parentMemo->workflow_direction = 'terminer';
                }

                $parentMemo->save();

                // Historique sur le parent pour dire qu'on a répondu
                Historiques::create([
                    'user_id' => $currentUser->id, 
                    'memo_id' => $parentMemo->id,
                    'visa' => 'RÉPONDU', 
                    'workflow_comment' => "Réponse émise par " . ($currentUser->entity->name ?? 'l\'entité')
                ]);
            }

            // =========================================================
            // 2. CRÉATION DU NOUVEAU MÉMO (LA RÉPONSE)
            // =========================================================
            $newMemo = Memo::create([
                'object'             => $this->new_object,
                'concern'            => $this->new_concern,
                'content'            => $this->new_content,
                'user_id'            => $currentUser->id,
                'parent_id'          => $this->parent_id,
                'workflow_direction' => 'sortant',        
                'status'             => 'envoyer',       
                'current_holders'    => [$targetId], 
            ]);

            foreach ($this->recipients as $item) {
                Destinataires::create([
                    'memo_id' => $newMemo->id, 
                    'entity_id' => $item['entity_id'],
                    'action' => $item['action'], 
                    'processing_status' => 'en_cours' // La réponse est "en cours" pour les nouveaux destinataires
                ]);
            }

            Historiques::create([
                'user_id' => $currentUser->id, 
                'memo_id' => $newMemo->id,
                'visa' => 'CRÉATION RÉPONSE', 
                'workflow_comment' => "Réponse au mémo #{$this->parent_id} transmise {$targetType}."
            ]);
        });

        if ($newMemo && $targetId) {
            $tUser = User::find($targetId);
            if ($tUser) $tUser->notify(new MemoActionNotification($newMemo, 'envoyer', $currentUser));
        }

        $this->dispatch('notify', message: "Réponse transmise avec succès.");
        $this->cancelReply();
    }

    public function cancelReply()
    {
        $this->isCreatingReply = false;
        $this->reset(['new_object', 'new_concern', 'new_content', 'recipients', 'parent_id']);
    }

    public function addRecipient()
    {
        $this->validate(['newRecipientEntity' => 'required', 'newRecipientAction' => 'required']);
        $entity = Entity::find($this->newRecipientEntity);
        $this->recipients[] = ['entity_id' => $entity->id, 'entity_name' => $entity->name, 'action' => $this->newRecipientAction];
        $this->reset(['newRecipientEntity', 'newRecipientAction']);
    }

    private function resetReplyForm() {
        $this->reset(['isCreatingReply', 'parent_id', 'new_object', 'new_concern', 'new_content', 'recipients']);
    }

    // =========================================================
    // 7. FONCTIONS OUTILS ET HELPERS
    // =========================================================

    /**
     * Vérifie si l'entité peut traiter le mémo ou si elle est bloquée par un décideur
     */
    private function checkDecisionBlock($memoId)
    {
        $user = Auth::user();
        $decisionMakers = Destinataires::where('memo_id', $memoId)
            ->where('action', 'like', '%Décider%')
            ->where('entity_id', '!=', $user->entity_id)
            ->get();

        foreach ($decisionMakers as $maker) {
            if ($maker->processing_status !== 'decision_prise') {
                $eName = $maker->entity->name ?? "l'entité responsable";
                return "Action impossible : Vous devez attendre la décision de {$eName}.";
            }
        }
        return null; 
    }

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
            'memo' => $memo, 'recipientsByAction' => $recipientsByAction,
            'logo' => $logoBase64, 'qrCode' => $qrCodeBase64, 'date' => $memo->created_at->format('d/m/Y'),
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(fn() => print($pdf->output()), 'Memo_' . $memo->id . '.pdf');
    }

    public function removeRecipient($index)
    {
        unset($this->recipients[$index]);
        $this->recipients = array_values($this->recipients);
    }

    // =========================================================
    // 8. RENDU
    // =========================================================

    public function render()
    {
        $userId = Auth::id(); 
        $memos = Memo::with(['user', 'destinataires.entity'])
            ->where('workflow_direction', 'entrant')
            ->whereJsonContains('current_holders', $userId)
            ->where(fn($q) => $q->where('object', 'like', '%'.$this->search.'%')->orWhere('concern', 'like', '%'.$this->search.'%'))
            ->orderBy('updated_at', 'desc')
            ->paginate(9);

        return view('livewire.memos.incoming2-memos', ['memos' => $memos]);
    }
}