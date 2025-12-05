<?php

namespace App\Livewire\Memos;

use Carbon\Carbon;
use App\Models\Memo;
use App\Models\User;
use App\Models\Entity;
use Livewire\Component;
use App\Models\Historiques;
use Illuminate\Support\Str;
use App\Models\ReplacesUser;
use Livewire\WithPagination;
use App\Models\Destinataires;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DraftedMemos extends Component
{
    use WithPagination;
    use WithFileUploads;

    // --- RECHERCHE & DATATABLE ---
    public $search = '';

    // --- MODALS STATES ---
    public $isOpen = false;      // Aperçu
    public $isOpen2 = false;     // Édition (Le gros formulaire)
    public $isOpen3 = false;     // Assignation simple (si conservé)
    public $isOpen4 = false;     // Suppression

    // --- CHAMPS DU MÉMO (SCHEMA DB) ---
    public $memo_id = null;

    // --- VARIABLES POUR L'ENVOI (WORKFLOW) ---
    public $workflow_comment = '';
    public $selected_visa = ''; 
    public $target_users_ids = []; // Pour ajouter d'autres destinataires en plus du N+1
    
    // Pour l'affichage dans le modal
    public $nPlusOneUser = null;
    public $effectiveReceiver = null; // Celui qui reçoit vraiment (N+1 ou Remplaçant)
    public $isReplaced = false;
    public $usersList = []; // Liste de tous les users pour choix multiple

   

    // Options de visa
    public $visaOptions = [
        'Vu' => 'Vu (Simple transmission)',
        'Vu & Accord' => 'Vu & D\'accord',
        'Vu & Pas d\'accord' => 'Vu & Pas d\'accord',
    ];
    
    #[Rule('required|string|max:255')]
    public string $object = '';

    #[Rule('required|string|max:255')]
    public string $concern = '';

    #[Rule('required|string')]
    public string $content = '';

    // --- GESTION DES DESTINATAIRES (TABLEAU DYNAMIQUE) ---
    public $recipients = []; // Contient: ['entity_id', 'entity_name', 'action']
    public $newRecipientEntity = '';
    public $newRecipientAction = '';
    public $allEntities = []; // Liste pour le select
    public $actionsList = ['Faire le nécessaire', 'Prendre connaissance', 'Prendre position', 'Décider'];

    // --- GESTION DES PIÈCES JOINTES ---
    public $newAttachments = []; // Fichiers temporaires uploadés (Livewire)
    public $existingAttachments = []; // Chemins des fichiers déjà en base (JSON)

    // --- DATA VIEW (Aperçu) ---
    public $date;
    public $user_service;
    public $user_first_name;
    public $user_last_name;
    public $user_entity_name;
    public $selections = []; // Pour modal assignation simple

    public function mount()
    {
        // On charge les entités une seule fois pour les selects
        $this->allEntities = Entity::orderBy('name')->get(); 
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // =================================================================
    // LOGIQUE D'ÉDITION (MODAL 2)
    // =================================================================

    public function editMemo($id)
    {
        $memo = Memo::with(['user', 'destinataires.entity'])->findOrFail($id);
        
        $this->memo_id = $memo->id;
        $this->object = $memo->object;
        $this->concern = $memo->concern ?? '';
        $this->content = $memo->content;
        
        // 1. Charger les pièces jointes existantes (JSON)
        $pj = $memo->pieces_jointes;
        if (is_string($pj)) { $pj = json_decode($pj, true); }
        $this->existingAttachments = is_array($pj) ? $pj : [];
        $this->newAttachments = []; // Reset uploads

        // 2. Charger les destinataires existants dans le tableau local
        $this->recipients = $memo->destinataires->map(function($dest) {
            return [
                'entity_id' => $dest->entity_id,
                'entity_name' => $dest->entity->name ?? 'Inconnu',
                'action' => $dest->action
            ];
        })->toArray();

        // Data pour l'aperçu si besoin
        $this->date = $memo->created_at->format('d/m/Y');   
        $entity = Entity::find($memo->user->entity_id);
        $this->user_entity_name = $entity->name ?? 'Entité';

        $this->resetValidation();
        $this->isOpen2 = true;
    }

    // Ajouter un destinataire dans la liste temporaire
    public function addRecipient()
    {
        $this->validate([
            'newRecipientEntity' => 'required',
            'newRecipientAction' => 'required'
        ]);

        $entity = $this->allEntities->firstWhere('id', $this->newRecipientEntity);

        // Vérifier doublon
        foreach ($this->recipients as $r) {
            if ($r['entity_id'] == $this->newRecipientEntity) {
                $this->addError('newRecipientEntity', 'Ce destinataire est déjà ajouté.');
                return;
            }
        }

        $this->recipients[] = [
            'entity_id' => $entity->id,
            'entity_name' => $entity->name, // ou $entity->title selon votre modèle
            'action' => $this->newRecipientAction
        ];

        // Reset inputs
        $this->newRecipientEntity = '';
        $this->newRecipientAction = '';
    }

    // Retirer un destinataire de la liste temporaire
    public function removeRecipient($index)
    {
        unset($this->recipients[$index]);
        $this->recipients = array_values($this->recipients); // Réindexer
    }

    // Retirer une PJ existante
    public function removeExistingAttachment($index)
    {
        // Optionnel : Supprimer le fichier physiquement si vous voulez
        // Storage::delete($this->existingAttachments[$index]); 
        
        unset($this->existingAttachments[$index]);
        $this->existingAttachments = array_values($this->existingAttachments);
    }

    // Retirer une nouvelle PJ (upload en cours)
    public function removeNewAttachment($index)
    {
        array_splice($this->newAttachments, $index, 1);
    }

    public function save()
    {
        $this->validate();

        // 1. Gestion des fichiers
        $finalAttachments = $this->existingAttachments;

        foreach ($this->newAttachments as $file) {
            // Stocker dans 'attachments/memos' par exemple
            $path = $file->store('attachments/memos', 'public');
            $finalAttachments[] = $path; // On stocke juste le chemin ou un objet
        }

        // 2. Mise à jour ou Création du Mémo
        $memo = Memo::updateOrCreate(
            ['id' => $this->memo_id],
            [
                'object' => $this->object,
                'concern' => $this->concern,
                'content' => $this->content,
                'pieces_jointes' => json_encode($finalAttachments), // Cast array to JSON
                'user_id' => Auth::id(),
                // 'status' => 'brouillon' // reste brouillon
            ]
        );

        // 3. Synchronisation des destinataires (Suppression des anciens -> Création des nouveaux)
        Destinataires::where('memo_id', $memo->id)->delete();

        foreach ($this->recipients as $recipient) {
            Destinataires::create([
                'memo_id' => $memo->id,
                'entity_id' => $recipient['entity_id'],
                'action' => $recipient['action']
            ]);
        }

        $this->closeModalDeux();
        $this->dispatch('notify', message: "Mémo modifié avec succès !");
    }

    // =================================================================
    // AUTRES FONCTIONS (Aperçu, Assign, Delete) - inchangées
    // =================================================================
    
    public function viewMemo($id) {
        $memo = Memo::with('user')->findOrFail($id);
        $this->fillMemoDataView($memo);
        $this->isOpen = true;
    }

    private function fillMemoDataView($memo) {
        // Logique simplifiée pour l'aperçu lecture seule
        $this->memo_id = $memo->id;
        $this->object = $memo->object;
        $this->concern = $memo->concern;
        $this->content = $memo->content;
        $this->date = $memo->created_at->format('d/m/Y');
        $entity = Entity::find($memo->user->entity_id);
        $this->user_entity_name = $entity->name ?? 'Entité';
        $this->user_service = $memo->user->service;
    }

    public function deleteMemo($id) { $this->memo_id = $id; $this->isOpen4 = true; }
    
    public function del() {
        $memo = Memo::find($this->memo_id);
        if ($memo && $memo->user_id === Auth::id()) {
            $memo->delete();
        }
        $this->closeModalQuatre();
        $this->dispatch('notify', message: "Supprimé avec succès !");
    }


 

    // GESTION MODALS
    public function closeModal() { $this->isOpen = false; }
    public function closeModalDeux() { 
        $this->isOpen2 = false; 
        $this->reset(['object', 'concern', 'content', 'recipients', 'newAttachments', 'existingAttachments']); 
    }
    public function closeModalTrois() { $this->isOpen3 = false; }
    public function closeModalQuatre() { $this->isOpen4 = false; }

    // =================================================================
    // LOGIQUE D'ASSIGNATION / ENVOI (MODAL 3)
    // =================================================================

    public function assignMemo($id)
    {
        $this->memo_id = $id;
        
        // Reset des champs du formulaire
        $this->workflow_comment = '';
        $this->selected_visa = 'Vu'; // Valeur par défaut
        $this->target_users_ids = [];
        $this->isReplaced = false;

        // 1. Récupérer le Manager (N+1) de l'utilisateur connecté
        $currentUser = Auth::user();
        $managerId = $currentUser->manager_id; 

        if ($managerId) {
            $this->nPlusOneUser = User::find($managerId);
            
            if ($this->nPlusOneUser) {
                // 2. Vérifier si ce manager est actuellement remplacé
                // Note : On suppose que les dates sont stockées au format 'Y-m-d' dans la DB
                $today = Carbon::now()->format('Y-m-d'); 

                $replacement = ReplacesUser::where('user_id', $managerId)
                    ->where('date_begin_replace', '<=', $today)
                    ->where('date_end_replace', '>=', $today)
                    ->first();

                if ($replacement) {
                    // CAS : Le manager est absent et remplacé
                    $this->isReplaced = true;
                    $this->effectiveReceiver = User::find($replacement->user_id_replace);
                } else {
                    // CAS : Le manager est présent
                    $this->effectiveReceiver = $this->nPlusOneUser;
                }
            }
        } else {
            // Pas de manager défini dans la table users
            $this->nPlusOneUser = null;
            $this->effectiveReceiver = null;
        }

        // 3. Charger la liste des autres utilisateurs (sauf soi-même)
        // Pour permettre d'envoyer en copie ou à d'autres personnes
        $this->usersList = User::where('id', '!=', Auth::id())
                               ->orderBy('last_name')
                               ->orderBy('first_name')
                               ->get();

        $this->isOpen3 = true;
    }

    public function sendMemo()
    {
        $this->validate([
            'selected_visa' => 'required', // Le visa est obligatoire
            'workflow_comment' => 'nullable|string|max:1000',
        ]);

        $memo = Memo::find($this->memo_id);
        $currentUser = Auth::user();

        // --- 1. Calcul des Destinataires (Current Holders) ---
        $nextHolders = [];

         // A. Ajouter le destinataire hiérarchique
        if ($this->effectiveReceiver) {
            // Plus besoin de (string), on garde l'ID tel quel (entier ou string selon la DB)
            $nextHolders[] = $this->effectiveReceiver->id;
        }

        /// B. Ajouter les destinataires manuels
        if (!empty($this->target_users_ids)) {
            foreach ($this->target_users_ids as $uid) {
                // On s'assure que $uid est un entier pour la cohérence (optionnel, mais propre)
                $uidClean = is_numeric($uid) ? (int)$uid : $uid;

                if (!in_array($uidClean, $nextHolders)) {
                    $nextHolders[] = $uidClean;
                }
            }
        }

        // Sécurité : Si personne n'est ciblé
        if (empty($nextHolders)) {
            $this->addError('effectiveReceiver', 'Veuillez sélectionner au moins un destinataire.');
            return;
        }

        // --- 2. Mise à jour du Mémo ---
        
        $memo->previous_holders = [$currentUser->id];
        $memo->current_holders = $nextHolders;

        $memo->status = 'envoyer'; 
        $memo->workflow_comment = $this->workflow_comment; 
        
        $memo->save();

        Historiques::firstOrCreate(
            [
                // Critères de recherche (Ce qui rend l'action unique)
                'user_id' => $currentUser->id,
                'memo_id' => $memo->id,
                'visa'    => $this->selected_visa,
                'workflow_comment' => $this->workflow_comment ?? 'R.A.S',
            ]
        );
        // --- 4. Fin ---
        $this->closeModalTrois();
        $this->dispatch('notify', message: "Le mémo a été envoyer avec succès.");
    }

   
    

    public function render()
    {
        $memos = Memo::with(['destinataires.entity'])
                   ->where('user_id', Auth::id())
                   ->where('status', 'document') // <--- La condition est ajoutée ici
                   ->where(function($query) {
                       $query->where('object', 'like', '%'.$this->search.'%')
                             ->orWhere('concern', 'like', '%'.$this->search.'%');
                   })
                   ->orderBy('created_at', 'desc')
                   ->paginate(9);

        return view('livewire.memos.drafted-memos', [
            'memos' => $memos,
            'entities' => $this->allEntities // Passe pour le select du modal
        ]);
    }
}