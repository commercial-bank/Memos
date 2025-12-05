<?php

namespace App\Livewire\Memos;

use Carbon\Carbon;
use App\Models\Memo;
use App\Models\User;
use App\Models\Entity;
use Livewire\Component;
use App\Models\Historiques;
use App\Models\MemoHistory;
use App\Models\WrittenMemo;
use App\Models\ReplacesUser;
use Illuminate\Support\Facades\Auth;

class DocsMemos extends Component
{

     // --- RECHERCHE & DATATABLE ---
    public $search = '';

    // --- MODALS STATES ---
    public $isOpen = false; 
    public $isOpen3 = false; 
    public $isOpenHistory = false; 

    // --- CHAMPS DU MÉMO (SCHEMA DB) ---
    public $memo_id = null;
    public $memoHistory = [];

     // --- DATA VIEW (Aperçu) ---
    public $date;
    public $user_service;
    public $user_first_name;
    public $user_last_name;
    public $user_entity_name;

    // --- VARIABLES REJET ---
    public $isOpenReject = false;
    public $reject_comment = '';

    // --- VARIABLES POUR L'ENVOI (WORKFLOW) ---
    public $workflow_comment = '';
    public $selected_visa = ''; 
    public $target_users_ids = []; // Pour ajouter d'autres destinataires en plus du N+1

    // Pour l'affichage dans le modal
    public $nPlusOneUser = null;
    public $effectiveReceiver = null; // Celui qui reçoit vraiment (N+1 ou Remplaçant)
    public $isReplaced = false;
    public $usersList = []; // Liste de tous les users pour choix multiple


    

    #[Rule('required|string|max:255')]
    public string $object = '';

    #[Rule('required|string|max:255')]
    public string $concern = '';

    #[Rule('required|string')]
    public string $content = '';

    public function viewMemo($id) {
        $memo = Memo::with('user')->findOrFail($id);
        $this->fillMemoDataView($memo);
        $this->isOpen = true;
    }

     public function viewHistory($id)
    {
        // 1. On récupère les historiques liés à ce mémo
        // 2. On charge la relation 'user' pour afficher le nom (pas juste l'ID)
        // 3. On trie du plus récent au plus ancien
        $this->memoHistory = Historiques::with('user')
            ->where('memo_id', $id)
            ->orderBy('created_at', 'desc') 
            ->get();

        // 4. On ouvre le modal
        $this->isOpenHistory = true;
    }

    /**
     * Ferme le modal historique
     */
    public function closeHistoryModal()
    {
        $this->isOpenHistory = false;
        $this->memoHistory = []; // Nettoyage
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

    // GESTION MODALS
    public function closeModal() 
    { 
        $this->isOpen = false; 
    }

    
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

    public function closeModalTrois() { $this->isOpen3 = false; }

    

    public function render()
    {
        $memos = Memo::with(['destinataires.entity'])
            ->where('user_id', Auth::id())
            
            // UTILISATION DE whereIn POUR PLUSIEURS STATUTS
            ->whereIn('status', ['envoyer', 'rejeter']) 
            
            ->where(function($query) {
                $query->where('object', 'like', '%'.$this->search.'%')
                    ->orWhere('concern', 'like', '%'.$this->search.'%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        return view('livewire.memos.docs-memos', [
                'memos' => $memos,
        ]);
    }

           
}