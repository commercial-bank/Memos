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

    // --- VARIABLES MODAL ASSIGNATION ---
    public $memo_type = 'standard'; // 'standard' ou 'projet'

    // Structure des données pour l'affichage : ['original' => User, 'effective' => User, 'is_replaced' => bool]
    public $managerData = null; 

    // Liste des utilisateurs éligibles pour le mode projet (Excluant Auth et N+1)
    public $projectUsersList = []; 

    // IDs sélectionnés en mode projet
    public $selected_project_users = [];

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
        $this->reset(['workflow_comment', 'selected_visa', 'selected_project_users']);
        $this->memo_type = 'standard'; // Par défaut

        $currentUser = Auth::user();

        // 1. GESTION DU N+1 (MANAGER)
        // On récupère le manager et on vérifie s'il est remplacé
        if ($currentUser->manager_id) {
            $manager = User::find($currentUser->manager_id);
            $this->managerData = $this->resolveUserAvailability($manager);
        } else {
            $this->managerData = null;
        }

        // 2. PRÉPARATION LISTE PROJET
        // Tous les users SAUF : Moi-même ET mon Manager (N+1)
        $excludeIds = [$currentUser->id];
        if ($this->managerData) {
            $excludeIds[] = $this->managerData['original']->id;
        }

        $this->projectUsersList = User::whereNotIn('id', $excludeIds)
                                      ->orderBy('last_name')
                                      ->get()
                                      ->map(function($user) {
                                          // On pré-calcule si ces users sont remplacés pour l'affichage
                                          return $this->resolveUserAvailability($user);
                                      });

        $this->isOpen3 = true;
    }

    private function resolveUserAvailability($user)
    {
        // Sécurité : si aucun utilisateur n'est passé (ex: pas de manager), on renvoie null
        if (!$user) return null;

        // 1. On récupère la date du jour
        // IMPORTANT : Le format doit être identique à ce qu'il y a dans ta DB (Y-m-d)
        $today = Carbon::now()->format('Y-m-d'); 

        // 2. La requête vérifie l'intervalle [Debut ..... AUJOURD'HUI ..... Fin]
        $replacement = ReplacesUser::where('user_id', $user->id) // On cherche si cet user est remplacé
            
            // La date de début doit être passée ou être aujourd'hui
            // (Ex: Si début = 01/12 et on est le 06/12 => "2025-12-01" <= "2025-12-06") -> VRAI
            ->where('date_begin_replace', '<=', $today)
            
            // ET la date de fin doit être dans le futur ou aujourd'hui
            // (Ex: Si fin = 31/12 et on est le 06/12 => "2025-12-31" >= "2025-12-06") -> VRAI
            ->where('date_end_replace', '>=', $today)
            
            ->first();

        // 3. Traitement du résultat
        if ($replacement) {
            // --- CAS : ON EST DANS LA PÉRIODE DE REMPLACEMENT ---
            
            // On cherche le user remplaçant
            $replacingUser = User::find($replacement->user_id_replace);

            // Sécurité supplémentaire : Si le compte du remplaçant a été supprimé entre temps
            if ($replacingUser) {
                return [
                    'original' => $user,           // L'utilisateur de base (Absent)
                    'effective' => $replacingUser, // Le remplaçant (Présent)
                    'is_replaced' => true
                ];
            }
        }

        // --- CAS : PAS DE REMPLACEMENT OU HORS PÉRIODE ---
        return [
            'original' => $user,
            'effective' => $user, // L'utilisateur reste lui-même
            'is_replaced' => false
        ];
    }


    public function sendMemo()
    {
        $this->validate([
            'selected_visa' => 'required',
            'workflow_comment' => 'nullable|string|max:1000',
            // Si projet, il faut au moins un destinataire
            'selected_project_users' => 'required_if:memo_type,projet|array',
        ], [
            'selected_project_users.required_if' => 'En mode projet, veuillez sélectionner au moins un collaborateur.'
        ]);

        $memo = Memo::find($this->memo_id);
        $currentUser = Auth::user();
        $nextHolders = [];

        // --- SCENARIO A : STANDARD (Envoi au N+1 ou son remplaçant) ---
        if ($this->memo_type === 'standard') {
            if ($this->managerData) {
                // On envoie à l'utilisateur effectif (le remplaçant s'il existe, sinon le manager)
                $nextHolders[] = $this->managerData['effective']->id;
            } else {
                $this->addError('general', "Vous n'avez pas de supérieur hiérarchique défini.");
                return;
            }
        }

        // --- SCENARIO B : PROJET (Envoi aux collaborateur sélectionnés ou leurs remplaçants) ---
        if ($this->memo_type === 'projet') {
            foreach ($this->selected_project_users as $userId) {
                // On revérifie la disponibilité au moment de l'envoi (sécurité)
                $targetUser = User::find($userId);
                $availability = $this->resolveUserAvailability($targetUser);
                
                if ($availability['effective']) {
                    $nextHolders[] = $availability['effective']->id;
                }
            }
        }

        if (empty($nextHolders)) {
            $this->addError('general', 'Aucun destinataire valide trouvé.');
            return;
        }

        // Sauvegarde DB
        $memo->previous_holders = [$currentUser->id];
        $memo->current_holders = array_unique($nextHolders); // Éviter doublons
        $memo->status = 'envoyer'; 
        $memo->workflow_comment = $this->workflow_comment; 
        // Optionnel : Sauvegarder le type de mémo si vous avez ajouté la colonne en base
        // $memo->type = $this->memo_type; 
        
        $memo->save();

        Historiques::create([
            'user_id' => $currentUser->id,
            'memo_id' => $memo->id,
            'visa'    => $this->selected_visa,
            'workflow_comment' => $this->workflow_comment ?? 'R.A.S',
        ]);

        $this->closeModalTrois();
        $this->dispatch('notify', message: "Le mémo ($this->memo_type) a été envoyé avec succès.");
    }

    public function closeModalTrois() { $this->isOpen3 = false; }

    

    public function render()
    {
        $memos = Memo::with(['destinataires.entity'])
            ->where('user_id', Auth::id())
            
            // UTILISATION DE whereIn POUR PLUSIEURS STATUTS
            ->whereIn('status', ['envoyer', 'rejeter','transmit']) 
            
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