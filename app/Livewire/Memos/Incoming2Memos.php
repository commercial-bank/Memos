<?php

namespace App\Livewire\Memos;

use Carbon\Carbon;
use App\Models\Memo;
use App\Models\User;
use App\Models\Entity;
use Livewire\Component;
use App\Models\DraftedMemo;
use App\Models\Historiques;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use App\Models\Destinataires;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Models\BlocEnregistrements;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Notifications\MemoActionNotification;

class Incoming2Memos extends Component
{
    use WithPagination;
    use WithFileUploads;

    // =================================================================================================
    // 1. PROPRIÉTÉS D'ÉTAT DE L'INTERFACE (UI) & MODALS
    // =================================================================================================

    public $search = '';
    
    // --- États d'ouverture des Modals ---
    public $isOpen = false;                 // Modal générique
    public $isOpenHistory = false;          // Modal Historique
    public $isViewingPdf = false;           // Modal Visionneuse PDF
    
    // --- Modals Workflow Entrant ---
    public $isRegistrationModalOpen = false; // Modal d'enregistrement (Secrétariat)
    public $isTransModalOpen = false;        // Modal de transmission
    public $isOpenTrans = false;             // (Similaire, conservé selon instruction)
    public $isCloseModalOpen = false;        // Modal de clôture simple
    public $isDecisionModalOpen = false;     // Modal de Prise de Décision
    public $isCreatingReply = false;         // Mode Réponse


    // =================================================================================================
    // 2. PROPRIÉTÉS DE DONNÉES DU MÉMO (AFFICHAGE & FORMULAIRE)
    // =================================================================================================

    public $memo_id = null;
    
    // --- Champs principaux ---
    #[Rule('required|string|max:255')]
    public string $object = '';

    #[Rule('required|string|max:255')]
    public string $concern = '';

    #[Rule('required|string')]
    public string $content = '';

    // --- Données d'affichage (Vue) ---
    public $date;
    public $user_service;
    public $user_first_name;
    public $user_last_name;
    public $user_entity_name;
    public $pdfBase64 = '';
    public $memoHistory = [];
    public $selections = []; // Variable conservée


    // =================================================================================================
    // 3. PROPRIÉTÉS DE WORKFLOW (TRANSMISSION & ENREGISTREMENT)
    // =================================================================================================

    // --- Variables d'Enregistrement (Secrétariat) ---
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

    // --- Variables de Transmission ---
    public $memoIdToTrans = null;
    public $targetRecipients = [];    // Liste d'objets stdClass (cibles potentielles)
    public $selectedRecipients = [];  // IDs sélectionnés
    public $targetRoleName = '';      // Titre du rôle cible (ex: Directeur)
    public $comment = '';             // Commentaire de transmission
    public $transRecipients = [];
    public $managerData = null; 
    
    // --- Variables de Clôture & Décision ---
    public $memoIdToClose = null;
    public $closingComment = ''; 
    public $decisionChoice = '';      // 'accord' ou 'refus'
    public $decisionComment = '';     // Note de décision

    // --- Configuration ---
    public $memo_type = 'standard';
    public $projectUsersList = []; 
    public $selected_project_users = [];
    public $generatedReference = '';


    // =================================================================================================
    // 4. PROPRIÉTÉS DE RÉPONSE & PIÈCES JOINTES
    // =================================================================================================

    // --- Réponse ---
    public $parent_id = null;        
    public $new_object = '';
    public $new_concern = '';
    public $new_content = '';
    
    // --- Destinataires & Listes ---
    public $recipients = [];         
    public $newRecipientEntity = '';
    public $newRecipientAction = '';
    public $allEntities = []; 
    public $actionsList = ['Faire le nécessaire', 'Prendre connaissance', 'Prendre position', 'Décider'];

    // --- Pièces Jointes ---
    #[Rule(['attachments.*' => 'nullable|file|max:10240'])] 
    public $attachments = [];        
    public $existingAttachments = []; 


    // =================================================================================================
    // 5. INITIALISATION & NAVIGATION
    // =================================================================================================

    public function mount()
    {
        $this->allEntities = Entity::select('id', 'name')->orderBy('name')->get();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // --- Fermeture des Modals Génériques ---

    public function closeModal() 
    { 
        $this->isOpen = false; 
        $this->reset(['memo_id', 'content', 'object', 'user_entity_name']); 
    }

    public function closeHistoryModal() 
    { 
        $this->isOpenHistory = false; $this->memoHistory = []; 
    }

    public function closePdfView()
    {
        $this->isViewingPdf = false;
        $this->pdfBase64 = '';
    }


    // =================================================================================================
    // 6. LOGIQUE D'AFFICHAGE & PDF
    // =================================================================================================

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

    private function getPdfData($memo)
    {
        // On cherche le directeur de l'entité du créateur du mémo
        $director = User::where('dir_id', $memo->user->dir_id)
                        ->where('poste', 'Directeur')
                        ->whereNull('manager_id') 
                        ->first();

        if (!$director) {
            $director = User::where('dir_id', $memo->user->dir_id)
                            ->where('poste', 'Directeur')
                            ->first();
        }

        return [
            'memo'               => $memo,
            'recipientsByAction' => $memo->destinataires->groupBy('action'),
            'date'               => $memo->created_at->format('d/m/Y'),
            'logo'               => $this->getLogoBase64(),
            'director'           => $director, 
        ];
    }

    public function viewHistory($id)
    {
        $this->memo_id = $id;
        
        $allRelatedMemoIds = Memo::where('parent_id', $id)
            ->orWhere('id', $id)
            ->pluck('id')
            ->toArray();

        $this->memoHistory = Historiques::with(['user', 'memo'])
            ->whereIn('memo_id', $allRelatedMemoIds)
            ->orderBy('created_at', 'desc') 
            ->get();

        $this->isOpenHistory = true;
    }

    private function getLogoBase64() {
        $path = public_path('images/logo.jpg');
        return file_exists($path) ? 'data:image/jpg;base64,' . base64_encode(file_get_contents($path)) : null;
    }

    private function fillMemoDataView($memo)
    {
        $this->memo_id = $memo->id;
        $this->object = $memo->object;
        $this->concern = $memo->concern;
        $this->content = $memo->content;
        $this->date = $memo->created_at->format('d/m/Y');
        $this->user_entity_name = $memo->user->entity->name ?? 'Entité';
        $this->user_service = $memo->user->service;
    }

    public function downloadMemoPDF()
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($this->memo_id);
        $pdf = Pdf::loadView('pdf.memo-layout', $this->getPdfData($memo));
        
        return response()->streamDownload(fn() => print($pdf->output()), "Memo_{$memo->id}.pdf");
    }


    // =================================================================================================
    // 7. LOGIQUE D'ENREGISTREMENT (SECRÉTARIAT)
    // =================================================================================================

    public function transMemo($id)
    {
        $this->resetValidation();
        $this->memoIdToTrans = $id;
        $this->comment = '';
        $this->selectedRecipients = [];
        
        $memo = Memo::with('user.entity')->find($id);
        if (!$memo) return;

        $user = Auth::user();
        $poste = Str::lower(trim($user->poste?->value));

        if (Str::contains($poste, 'secretaire')) {
            $dejaEnregistre = BlocEnregistrements::where('memo_id', $id)
                ->where('user_id', $user->id)
                ->exists();

            if ($dejaEnregistre) {
                $this->dispatch('notify', message: "Dossier déjà enregistré. Cotation...");
                $this->prepareTransmission($id);
                return;
            }

            $this->reg_reference = $memo->reference ?? '';
            $this->reg_date = Carbon::now()->format('d/m/Y');
            $this->reg_objet = $memo->object; 
            $this->reg_nature = 'Memo Entrant'; 
            $this->reg_expediteur = $memo->user->dir->name ?? 'Entité Inconnue';

            $this->isRegistrationModalOpen = true; 
        } else {
            $this->prepareTransmission($id);
        }
    }

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
        $this->prepareTransmission($this->memoIdToTrans);
    }

    public function closeRegistrationModal()
    {
        $this->isRegistrationModalOpen = false;
        $this->memoIdToTrans = null;
    }


    // =================================================================================================
    // 8. TRANSMISSION ET COTATION (HIÉRARCHIE)
    // =================================================================================================

    private function prepareTransmission($id)
    {
        $user = Auth::user();
        $poste = Str::lower(trim($user->poste->value)); 
        
        $query = User::query()
            ->select('id', 'first_name', 'last_name', 'poste')
            ->where('dir_id', $user->dir_id)
            ->where('id', '!=', $user->id);

        if (Str::contains($poste, 'secretaire')) {
            $this->targetRoleName = 'Directeur';
            $query->where('poste', 'like', '%Directeur%')->where('poste', 'not like', '%Sous-Directeur%');
        }
        elseif (Str::contains($poste, 'directeur') && !Str::contains($poste, 'sous')) {
            $this->targetRoleName = 'Sous-Directeur';
            $query->where('poste', 'like', '%Sous-Directeur%');
        }
        elseif (Str::contains($poste, 'sous-directeur')) {
            $this->targetRoleName = 'Collaborateurs (Dép/Svc)';
            if ($user->sous_direction_id) $query->where('sous_direction_id', $user->sous_direction_id);
            $query->where(fn($q) => $q->where('poste', 'not like', '%Directeur%')->where('poste', 'not like', '%Secretaire%'));
        }
        elseif (Str::contains($poste, 'chef-departement')) {
            $this->targetRoleName = 'Chefs de Service & Collaborateurs';
            $query->where('departement', $user->departement)
                  ->where(fn($q) => $q->where('poste', 'not like', '%Directeur%')->where('poste', 'not like', '%Chef-Departement%')->where('poste', 'not like', '%Secretaire%'));
        }
        elseif (Str::contains($poste, 'chef-service')) {
            $this->targetRoleName = 'Collaborateurs du Service';
            $query->where('service', $user->service)
                  ->where(fn($q) => $q->where('poste', 'not like', '%Directeur%')->where('poste', 'not like', '%Chef-Departement%')->where('poste', 'not like', '%Chef-Service%')->where('poste', 'not like', '%Secretaire%'));
        }
        else {
            $this->dispatch('notify', message: "Transmission automatique indisponible pour ce poste.");
            return;
        }

        // CORRECTIF : On convertit en liste d'objets stdClass
        $this->targetRecipients = $query->orderBy('first_name')->get()->map(function($u) {
            return (object) $u->toArray();
        })->all();

        if (empty($this->targetRecipients)) {
            $this->dispatch('notify', message: "Aucun destinataire éligible trouvé.");
            return;
        }

        if (count($this->targetRecipients) === 1) {
            $this->selectedRecipients[] = $this->targetRecipients[0]->id;
        }

        $this->isTransModalOpen = true;
    }

    public function confirmTransmission()
    {
        // Validation
        $this->validate(['selectedRecipients' => 'required|array|min:1']);

        // Récupération
        $memo = Memo::findOrFail($this->memo_id ?? $this->memoIdToTrans);
        $user = Auth::user(); 
        
        // Conversion des destinataires choisis en entiers
        $newRecipientsIds = array_map('intval', $this->selectedRecipients);

        // 1. GESTION DES DÉTENTEURS (CURRENT_HOLDERS)
        $existingHolders = is_array($memo->current_holders) 
            ? $memo->current_holders 
            : (json_decode($memo->current_holders, true) ?? []);

        // Fusion sans doublons
        $updatedCurrentHolders = array_values(array_unique(array_merge($existingHolders, $newRecipientsIds)));

        // 2. GESTION DES TRAITEURS (TREATMENT_HOLDERS)
        $currentTreatmentHolders = is_array($memo->treatment_holders) 
            ? $memo->treatment_holders 
            : (json_decode($memo->treatment_holders, true) ?? []);

        // A. On retire l'utilisateur courant de la liste des traiteurs
        $remainingHolders = array_diff($currentTreatmentHolders, [$user->id]);

        // B. On ajoute les nouveaux destinataires aux restants
        $mergedTreatmentHolders = array_merge($remainingHolders, $newRecipientsIds);

        // C. Nettoyage
        $updatedTreatmentHolders = array_values(array_unique($mergedTreatmentHolders));

        // 3. SAUVEGARDE EN BASE
        $memo->update([
            'current_holders'   => $updatedCurrentHolders,
            'treatment_holders' => $updatedTreatmentHolders
        ]);

        // 4. HISTORIQUE ET NOTIFICATIONS
        $recipients = User::whereIn('id', $this->selectedRecipients)->get();
        
        foreach ($recipients as $recipient) {
            Historiques::create([
                'user_id' => $user->id,
                'memo_id' => $memo->id,
                'visa'    => 'Coté / Transmis', 
                'workflow_comment' => $this->comment . " (Assigné à: " . $recipient->first_name . ")"
            ]);
            try { 
                $recipient->notify(new MemoActionNotification($memo, 'cotation', $user)); 
                $this->sendEmailNotification($memo, $recipient, $user);
            } catch (\Exception $e) {}
        }

        $this->dispatch('notify', message: "Mémo transmis avec succès.");
        $this->closeTransModal();
    }

    public function closeTransModal()
    {
        $this->isTransModalOpen = false;
        $this->reset(['selectedRecipients', 'comment', 'memoIdToTrans', 'targetRecipients']);
    }


    // =================================================================================================
    // 9. GESTION DE LA CLÔTURE & DÉCISION
    // =================================================================================================

    public function openCloseModal($id)
    {
        $this->memoIdToClose = $id;
        $this->closingComment = '';
        $this->isCloseModalOpen = true;
    }

    public function cancelCloseModal()
    {
        $this->isCloseModalOpen = false;
        $this->reset(['memoIdToClose', 'closingComment']);
    }
    
    public function confirmCloseMemo()
    {
        // 1. Correction de la vérification du blocage
        if ($errorMessage = $this->checkDecisionBlock($this->memoIdToClose)) {
            $this->dispatch('notify', message: $errorMessage);
            $this->cancelCloseModal();
            return;
        }

        $user = Auth::user();

        // Gestion des multiples entités (Direction / Sous-Dir)
        $userEntityIds = array_filter([$user->dir_id, $user->sd_id]);

        if (empty($userEntityIds)) {
            $this->dispatch('notify', message: "Erreur : Votre profil n'est lié à aucune entité (Direction/SD).");
            return;
        }

        // On cherche si L'UNE des entités de l'utilisateur est destinataire
        $myDestinataireRecord = Destinataires::where('memo_id', $this->memoIdToClose)
            ->whereIn('entity_id', $userEntityIds) 
            ->first();

        if (!$myDestinataireRecord) {
            $this->dispatch('notify', message: "Erreur : Entité non destinataire.");
            return;
        }

        $myDestinataireRecord->update([
            'processing_status' => 'traiter',
        ]);

        Historiques::create([
            'user_id' => $user->id,
            'memo_id' => $this->memoIdToClose,
            'visa'    => 'Terminé (Entité)',
            'workflow_comment' => $this->closingComment ?: "Traitement finalisé par l'entité."
        ]);

        $pending = Destinataires::where('memo_id', $this->memoIdToClose)
            ->whereNotIn('processing_status', ['traiter', 'decision_prise', 'repondu'])
            ->exists();

        if (!$pending) {
            Memo::where('id', $this->memoIdToClose)->update(['workflow_direction' => 'terminer']);
        }

        $this->dispatch('notify', message: "Dossier traité.");
        $this->cancelCloseModal();
    }

    public function openDecisionModal($id, $choice)
    {
        $this->memo_id = $id;
        $this->decisionChoice = $choice;
        $this->decisionComment = '';
        $this->isDecisionModalOpen = true;
    }

    public function submitDecision($id, $decision) 
    {
        $user = Auth::user();
        $comment = $this->decisionComment ?: ($decision === 'accord' ? "Accord donné." : "Refus signifié.");

        // IMPORTANT : On récupère les entités de l'utilisateur
        $userEntityIds = array_filter([$user->dir_id, $user->sd_id]);

        // On cherche l'enregistrement dans 'destinataires' qui correspond à l'entité du user
        $destRecord = Destinataires::where('memo_id', $id)
            ->whereIn('entity_id', $userEntityIds) 
            ->where(function($q) {
                $q->where('action', 'like', '%Décider%')
                ->orWhere('action', 'like', '%Prendre position%');
            })
            ->first();

        if (!$destRecord) {
            $this->dispatch('notify', message: "Erreur : Droits de décision non trouvés pour votre entité.");
            return;
        }

        DB::transaction(function () use ($id, $user, $comment, $decision, $destRecord) {
            if ($decision === 'refus') {
                // CAS REFUS : On clôture tout immédiatement
                Destinataires::where('memo_id', $id)->update([
                    'processing_status' => 'traiter', 
                    'completed_at' => now()
                ]);
                Memo::where('id', $id)->update(['workflow_direction' => 'terminer']);
                
                $visa = 'REFUS DÉCISIF';
            } else {
                // CAS ACCORD
                $destRecord->update([
                    'processing_status' => 'decision_prise', 
                ]);

                // VÉRIFICATION : Est-ce que d'autres entités doivent encore traiter le mémo ?
                $isStillPending = Destinataires::where('memo_id', $id)
                    ->whereNotIn('processing_status', ['traiter', 'decision_prise', 'repondu'])
                    ->exists();

                if (!$isStillPending) {
                    Memo::where('id', $id)->update(['workflow_direction' => 'terminer']);
                }
                
                $visa = 'DÉCISION RENDUE (ACCORD)';
            }

            // Enregistrement dans l'historique
            Historiques::create([
                'user_id' => $user->id,
                'memo_id' => $id,
                'visa'    => $visa,
                'workflow_comment' => $comment
            ]);
        });

        $this->isDecisionModalOpen = false;
        $this->dispatch('notify', message: "Décision enregistrée et dossier mis à jour.");
    }


    // =================================================================================================
    // 10. GESTION DES RÉPONSES
    // =================================================================================================

    public function replyMemo($id)
    {
        if ($error = $this->checkDecisionBlock($id)) {
            $this->dispatch('notify', message: $error);
            return; 
        }

        $parent = Memo::with('user.entity')->find($id);
        if (!$parent) return;

        $this->parent_id = $id;
        $this->new_object = "RE: " . $parent->object;
        $this->new_concern = "Réponse à réf: " . ($parent->reference ?? 'N/A');
        
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
        // 1. Validation rigoureuse
        $this->validate([
            'new_object'  => 'required|string|max:255',
            'new_concern' => 'required|string|max:255',
            'new_content' => 'required',
            'recipients'  => 'required|array|min:1',
            'attachments.*' => 'nullable|file|max:10240', 
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($user) {
            // 2. Traitement des fichiers joints
            $filePaths = [];
            if ($this->attachments) {
                foreach ($this->attachments as $file) {
                    $filePaths[] = $file->store('attachments/memos', 'public');
                }
            }

            // 3. Mise à jour du Mémo Parent (Entrant)
            $userEntityIds = array_filter([$user->dir_id, $user->sd_id]);
            
            Destinataires::where('memo_id', $this->parent_id)
                ->whereIn('entity_id', $userEntityIds)
                ->update([
                    'processing_status' => 'repondu',
                ]);
                
            // 4. Vérification de clôture globale du parent (Entrant)
            $isStillPending = Destinataires::where('memo_id', $this->parent_id)
                ->whereNotIn('processing_status', ['traiter', 'decision_prise', 'repondu'])
                ->exists();

            if (!$isStillPending) {
                Memo::where('id', $this->parent_id)->update(['workflow_direction' => 'terminer']);
            }

            // 5. Création du BROUILLON (DraftedMemo)
            $draft = DraftedMemo::create([
                'object'             => $this->new_object,
                'concern'            => $this->new_concern,
                'content'            => $this->new_content,
                'user_id'            => $user->id,
                'parent_id'          => $this->parent_id, 
                'workflow_direction' => 'sortant',        
                'status'             => 'brouillon',       
                'current_holders'    => [$user->id], 
                'pieces_jointes'     => json_encode($filePaths),
                'destinataires'      => json_encode($this->recipients), 
            ]);

            // 6. Enregistrement de l'historique sur le mémo parent
            Historiques::create([
                'user_id'          => $user->id, 
                'memo_id'          => $this->parent_id,
                'visa'             => 'RÉPONSE (BROUILLON)', 
                'workflow_comment' => "Projet de réponse initié (Brouillon)."
            ]);
        });

        $this->dispatch('notify', message: "Memo de réponse enregistré dans vos brouillons.");
        $this->cancelReply();
    }

    public function cancelReply()
    {
        $this->isCreatingReply = false;
        $this->reset(['new_object', 'new_concern', 'new_content', 'recipients', 'parent_id']);
    }

    public function removeAttachment($index)
    {
        array_splice($this->attachments, $index, 1);
    }


    // =================================================================================================
    // 11. HELPERS & UTILITAIRES
    // =================================================================================================

    private function checkDecisionBlock($memoId)
    {
        $user = Auth::user();
        $block = Destinataires::where('memo_id', $memoId)
            ->where('action', 'like', '%Décider%')
            ->where('entity_id', '!=', $user->entity_id)
            ->where('processing_status', '!=', 'decision_prise')
            ->with('entity')
            ->first();

        return $block ? "En attente de décision : " . $block->entity->name : null;
    }

    public function addRecipient()
    {
        $this->validate(['newRecipientEntity' => 'required', 'newRecipientAction' => 'required']);
        $entity = $this->allEntities->firstWhere('id', $this->newRecipientEntity);
        $this->recipients[] = ['entity_id' => $entity->id, 'entity_name' => $entity->name, 'action' => $this->newRecipientAction];
        $this->reset(['newRecipientEntity', 'newRecipientAction']);
    }

    public function removeRecipient($index)
    {
        unset($this->recipients[$index]);
        $this->recipients = array_values($this->recipients);
    }


    // =================================================================================================
    // 12. GESTION DES EMAILS
    // =================================================================================================

      /**
     * Envoie l'email de Rejet ou de Retour via PHPMailer
     */
    private function sendMemoEmailNotification($memo, $recipient, $actor, $title, $color, $actionLabel)
    {
        if (empty($recipient->email)) return;

        try {
            $mail = new PHPMailer(true);

            // Configuration SMTP
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST', 'smtp.gie.local');
            $mail->SMTPAuth = false;
            $mail->Port = env('MAIL_PORT', 25);
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
            $mail->CharSet = 'UTF-8';
            
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            // Expéditeur
            $mail->setFrom(
                env('MAIL_FROM_ADDRESS', 'cbc_infos@groupecommercialbank.com'),
                env('MAIL_FROM_NAME', 'CBC MEMOS')
            );

            // Destinataire
            $mail->addAddress($recipient->email, $recipient->first_name . ' ' . $recipient->last_name);

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = "$title : " . $memo->object;
            
            $mail->Body = $this->buildSendMemoEmailBody($memo, $recipient, $actor, $title, $color, $actionLabel);
            
            // Texte brut (Fallback)
            $mail->AltBody = "Bonjour, votre mémo '{$memo->object}' a été : $actionLabel par {$actor->first_name} {$actor->last_name}.\nMotif : {$this->reject_comment}";

            $mail->send();

        } catch (Exception $e) {
            Log::error("Erreur envoi email send mémo #{$memo->id}: " . $mail->ErrorInfo);
        }
    }

    /**
     * Construit le HTML de l'email (Design Rouge ou Orange)
     */
    private function buildSendMemoEmailBody($memo, $recipient, $actor, $title, $color, $actionLabel)
    {
        $recipientName = $recipient->first_name . ' ' . $recipient->last_name;
        $actorName = $actor->first_name . ' ' . $actor->last_name;
        $actorPoste = $actor->poste->value ?? $actor->poste; // Gère Enum ou String
        
        $memoUrl = route('dashboard', [
            'view' => 'memos-content', 
            'tab' =>  'document'
        ]);

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f5; }
                .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .header { background-color: {$color}; padding: 30px; text-align: center; }
                .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: bold; text-transform: uppercase; }
                .content { padding: 30px; }
                .alert-box { background-color: #fff1f2; border-left: 4px solid {$color}; padding: 15px; margin: 20px 0; border-radius: 4px; }
                .info-label { font-size: 12px; font-weight: bold; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }
                .info-value { font-size: 15px; color: #111827; margin-bottom: 12px; font-weight: 500; }
                .comment-box { background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 15px; border-radius: 6px; font-style: italic; color: #4b5563; margin-top: 5px; }
                .btn { display: inline-block; padding: 12px 24px; background-color: {$color}; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
                .footer { background-color: #1f2937; color: #9ca3af; padding: 20px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>{$title}</h1>
                </div>
                
                <div class='content'>
                    <p>Bonjour <strong>{$recipientName}</strong>,</p>
                    
                    <p>Le statut de votre mémorandum a été mis à jour par <strong>{$actorName}</strong> ({$actorPoste}).</p>
                    
                    <div class='alert-box'>
                        <div class='info-label'>Objet du Mémo</div>
                        <div class='info-value'>{$memo->object}</div>
                        
                        <div class='info-label'>Action</div>
                        <div class='info-value' style='color: {$color};'>{$actionLabel}</div>
                    </div>

                    <div class='info-label'>Motif / Commentaire :</div>
                    <div class='comment-box'>
                        « {$this->reject_comment} »
                    </div>

                    <div style='text-align: center;'>
                        <a href='{$memoUrl}' class='btn'>Accéder au Document</a>
                    </div>
                </div>
                
                <div class='footer'>
                    <p><strong>Commercial Bank Cameroun</strong> - Système de Gestion des Mémos</p>
                    <p>Ceci est un message automatique, merci de ne pas répondre.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }


    // =================================================================================================
    // 13. RENDU FINAL
    // =================================================================================================

    public function render()
    {
        $user = Auth::user();
        
        // On prépare les IDs des entités de l'utilisateur (Direction et Sous-Direction)
        $userEntityIds = array_filter([$user->dir_id, $user->sd_id]);

        $memos = Memo::with(['user', 'destinataires.entity','historiques.user'])
            ->where('workflow_direction', 'entrant')
            
            // 1. Vérifier que l'utilisateur est un détenteur actuel
            ->whereJsonContains('current_holders', $user->id)
            
            // 2. Vérifier que l'entité du user (DIR ou SD) est bien dans la liste des destinataires
            ->whereHas('destinataires', function($query) use ($userEntityIds) {
                $query->whereIn('entity_id', $userEntityIds);
            })
            
            // 3. Gestion de la recherche
            ->when($this->search, function($q) {
                $term = '%'.$this->search.'%';
                $q->where(function($sub) use ($term) {
                    $sub->where('object', 'like', $term)
                        ->orWhere('concern', 'like', $term);
                });
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(9);

        return view('livewire.memos.incoming2-memos', ['memos' => $memos]);
    }
    
}