<?php

namespace App\Livewire\Memos;

use Carbon\Carbon;
use App\Models\Memo;
use App\Models\User;
use App\Models\Entity;
use Livewire\Component;
use App\Models\Historiques;
use Illuminate\Support\Str;
use App\Models\Destinataires;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Models\BlocEnregistrements;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Notifications\MemoActionNotification;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class Incoming2Memos extends Component
{
    use WithPagination;
    use WithFileUploads;

    // =========================================================
    // 1. PROPRIÉTÉS DU COMPOSANT
    // =========================================================

    public $search = '';
    public $isViewingPdf = false;
    public $comment = ''; 
    public $date;

    // 1. Ajoutez ces propriétés en haut de la classe
    public $isDecisionModalOpen = false;
    public $decisionChoice = ''; // 'accord' ou 'refus'
    public $decisionComment = ''; // Optionnel : pour ajouter une note à la décision

    // --- Données d'Affichage ---
    public $user_service;
    public $user_first_name;
    public $user_last_name;
    public $user_entity_name;

    public $pdfBase64 = '';

    public $isOpen = false;                 
    public $isRegistrationModalOpen = false; 
    public $isTransModalOpen = false;        
    public $isCloseModalOpen = false;        
    public $isOpenTrans = false;             

    public $memo_id = null;
    
    #[Rule('required|string|max:255')]
    public string $object = '';

    #[Rule('required|string|max:255')]
    public string $concern = '';

    #[Rule('required|string')]
    public string $content = '';

    public $selections = [];

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

    public $memoIdToTrans = null;
    public $targetRecipients = [];    // Sera une liste d'objets stdClass
    public $selectedRecipients = [];  
    public $targetRoleName = '';      
    public $managerData = null; 
    public $memo_type = 'standard';
    public $projectUsersList = []; 
    public $selected_project_users = [];
    public $transRecipients = [];
    public $generatedReference = '';

    public $memoIdToClose = null;
    public $closingComment = ''; 

    public $isCreatingReply = false; 
    public $parent_id = null;        
    public $new_object = '';
    public $new_concern = '';
    public $new_content = '';
    public $recipients = [];         
    public $attachments = [];        
    public $newRecipientEntity = '';
    public $newRecipientAction = '';
    public $allEntities = []; 
    public $actionsList = ['Faire le nécessaire', 'Prendre connaissance', 'Prendre position', 'Décider'];

      // --- Gestion des Pièces Jointes ---
    #[Rule(['attachments.*' => 'nullable|file|max:10240'])] 


    public $existingAttachments = []; 


    // =========================================================
    // 2. INITIALISATION & NAVIGATION
    // =========================================================

    public function mount()
    {
        $this->allEntities = Entity::select('id', 'name')->orderBy('name')->get();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    private function getPdfData($memo)
    {
        // On cherche le directeur de l'entité du créateur du mémo
        $director = User::where('dir_id', $memo->user->dir_id)
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

    public function closeModal() 
    { 
        $this->isOpen = false; 
        $this->reset(['memo_id', 'content', 'object', 'user_entity_name']); 
    }

    // =========================================================
    // 3. LOGIQUE D'ENREGISTREMENT (SECRÉTARIAT)
    // =========================================================

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

    // =========================================================
    // 4. TRANSMISSION ET COTATION (HIÉRARCHIE)
    // =========================================================

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

        // CORRECTIF : On convertit en liste d'objets stdClass pour supporter la syntaxe $recipient->id dans le Blade
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
        $this->validate(['selectedRecipients' => 'required|array|min:1']);

        $memo = Memo::findOrFail($this->memo_id ?? $this->memoIdToTrans);
        $user = Auth::user(); 
        
        $holders = is_array($memo->current_holders) ? $memo->current_holders : (json_decode($memo->current_holders, true) ?? []);

        $holders = array_values(array_unique(array_merge(array_diff($holders, [$user->id]), array_map('intval', $this->selectedRecipients))));

        $memo->update(['current_holders' => $holders]);

        $recipients = User::whereIn('id', $this->selectedRecipients)->get();
        foreach ($recipients as $recipient) {
            Historiques::create([
                'user_id' => $user->id,
                'memo_id' => $memo->id,
                'visa'    => 'Coté / Transmis', 
                'workflow_comment' => $this->comment . " (Assigné à: " . $recipient->full_name . ")"
            ]);
            try { 
                $recipient->notify(new MemoActionNotification($memo, 'cotation', $user)); 
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

    // =========================================================
    // 5. GESTION DE LA CLÔTURE
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
        $this->reset(['memoIdToClose', 'closingComment']);
    }
    
    public function confirmCloseMemo()
    {
        if ($errorMessage = $this->checkDecisionBlock($this->memoIdToClose)) {
            $this->dispatch('notify', message: $errorMessage);
            $this->cancelCloseModal();
            return;
        }

        $user = Auth::user();
        $myDestinataireRecord = Destinataires::where('memo_id', $this->memoIdToClose)
            ->where('entity_id', $user->entity_id)
            ->first();

        if (!$myDestinataireRecord) {
            $this->dispatch('notify', message: "Erreur : Entité non destinataire.");
            return;
        }

        $myDestinataireRecord->update([
            'processing_status' => 'traiter',
            'completed_at' => now()
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

    // 2. Ajoutez cette méthode pour ouvrir le modal
    public function openDecisionModal($id, $choice)
    {
        $this->memo_id = $id;
        $this->decisionChoice = $choice;
        $this->decisionComment = '';
        $this->isDecisionModalOpen = true;
    }

    // 3. Modifiez la méthode submitDecision existante pour fermer le modal
    public function submitDecision($id, $decision) 
    {
        $user = Auth::user();
        $comment = $this->decisionComment ?: ($decision === 'accord' ? "Accord donné." : "Refus signifié.");

        // IMPORTANT : On récupère les entités de l'utilisateur (Direction et Sous-Direction)
        $userEntityIds = array_filter([$user->dir_id, $user->sd_id]);

        // On cherche l'enregistrement dans 'destinataires' qui correspond à l'entité du user
        $destRecord = Destinataires::where('memo_id', $id)
            ->whereIn('entity_id', $userEntityIds) // Correction ici : on utilise dir_id/sd_id
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
                // CAS ACCORD : On marque cette entité comme ayant pris sa décision
                $destRecord->update([
                    'processing_status' => 'decision_prise', 
                ]);

                // VÉRIFICATION : Est-ce que d'autres entités doivent encore traiter le mémo ?
                $isStillPending = Destinataires::where('memo_id', $id)
                    ->whereNotIn('processing_status', ['traiter', 'decision_prise', 'repondu'])
                    ->exists();

                // Si plus personne n'est en attente, on termine le mémo globalement
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

    // =========================================================
    // 6. GESTION DES RÉPONSES
    // =========================================================

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

        // 2. Détermination du destinataire du circuit de validation (N+1 ou Secrétariat)
        // Si c'est un directeur, on envoie à sa secrétaire, sinon au manager_id
       


        $newMemo = null;

        DB::transaction(function () use ($user, $targetId, &$newMemo) {
            // 3. Traitement des fichiers joints
            $filePaths = [];
            if ($this->attachments) {
                foreach ($this->attachments as $file) {
                    $filePaths[] = $file->store('attachments/memos', 'public');
                }
            }

            // 4. Mise à jour du Mémo Parent (Statut de réception de mon entité)
            // On récupère les IDs de mon entité (DIR/SD) pour marquer le parent comme répondu
            $userEntityIds = array_filter([$user->dir_id, $user->sd_id]);
            Destinataires::where('memo_id', $this->parent_id)
                ->whereIn('entity_id', $userEntityIds)
                ->update([
                    'processing_status' => 'repondu', 
                ]);
                
            // 5. Vérification de clôture globale du parent
            $isStillPending = Destinataires::where('memo_id', $this->parent_id)
                ->whereNotIn('processing_status', ['traiter', 'decision_prise', 'repondu'])
                ->exists();

            if (!$isStillPending) {
                Memo::where('id', $this->parent_id)->update(['workflow_direction' => 'terminer']);
            }

            // 6. Création du nouveau mémo (La Réponse)
            // IMPORTANT : current_holders contient l'auteur pour qu'il apparaisse dans ses "Mémos Sortants"
            $newMemo = Memo::create([
                'object'             => $this->new_object,
                'concern'            => $this->new_concern,
                'content'            => $this->new_content,
                'user_id'            => $user->id,
                'parent_id'          => $this->parent_id,
                'workflow_direction' => 'sortant',        
                'status'             => 'envoyer',       
                'current_holders'    => [$user->id], // L'auteur
                'treatment_holders'  => [$user->id],         
                'pieces_jointes'     => json_encode($filePaths),
            ]);

            // 7. Insertion des entités destinataires cibles de cette réponse
            $destData = array_map(fn($r) => [
                'memo_id'           => $newMemo->id,
                'entity_id'         => $r['entity_id'],
                'action'            => $r['action'],
                'processing_status' => 'en_cours',
                'created_at'        => now(),
                'updated_at'        => now()
            ], $this->recipients);
            Destinataires::insert($destData);

            // 8. Enregistrement de l'historique sur le mémo parent pour la traçabilité
            Historiques::create([
                'user_id'          => $user->id, 
                'memo_id'          => $this->parent_id,
                'visa'             => 'RÉPONDU', 
                'workflow_comment' => "Réponse émise (Nouveau Mémo ID #{$newMemo->reference})."
            ]);
        });

        // 9. Notification au destinataire (Validateur)
        if ($newMemo && $targetId) {
            $recipientUser = User::find($targetId);
            if ($recipientUser) {
                try {
                    $recipientUser->notify(new \App\Notifications\MemoActionNotification($newMemo, 'envoyer', $user));
                } catch (\Exception $e) {
                    \Log::error("Erreur notification réponse: " . $e->getMessage());
                }
            }
        }

        $this->dispatch('notify', message: "Réponse envoyée avec succès dans votre flux sortant.");
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

    // =========================================================
    // 7. FONCTIONS OUTILS & EXPORT
    // =========================================================

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

    public function downloadMemoPDF()
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($this->memo_id);
        $pdf = Pdf::loadView('pdf.memo-layout', $this->getPdfData($memo));
        
        return response()->streamDownload(fn() => print($pdf->output()), "Memo_{$memo->id}.pdf");
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

    

    // =========================================================
    // 8. RENDU FINAL
    // =========================================================

    public function render()
    {
        $user = Auth::user();
        
        // On prépare les IDs des entités de l'utilisateur (Direction et Sous-Direction)
        // array_filter permet d'ignorer les valeurs nulles
        $userEntityIds = array_filter([$user->dir_id, $user->sd_id]);

        $memos = Memo::with(['user', 'destinataires.entity','historiques.user'])
            ->where('workflow_direction', 'entrant')
            
            // 1. Vérifier que l'utilisateur est un détenteur actuel (Circuit de main en main)
            ->whereJsonContains('current_holders', $user->id)
            
            // 2. Vérifier que l'entité du user (DIR ou SD) est bien dans la liste des destinataires
            ->whereHas('destinataires', function($query) use ($userEntityIds) {
                $query->whereIn('entity_id', $userEntityIds);
            })
            
            // 3. Gestion de la recherche (avec le "use ($term)" pour éviter l'erreur de variable)
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