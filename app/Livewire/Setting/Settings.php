<?php

namespace App\Livewire\Setting;

use App\Models\User;
use App\Models\Entity;
use Livewire\Component;
use App\Models\Memo;       
use App\Models\ReplacesUser;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Notifications\AdminNotification;

class Settings extends Component
{
    use WithPagination;

    // --- État Global ---
    public $activeTab = 'users'; 
    public $search = '';
    public $viewingMemoId = null;
    public $showDeactivationModal = false;
    public $blocking_reason = '';
    public $userToToggleId = null;

    // --- Variables Structures Unifiées ---
    public $showModal = false;
    public $isEditing = false;
    public $itemId = null;
    public $ref;
    public $name;
    public $type;       
    public $upper_id;   

    // --- Variables Audit ---
    public $selectedMemo = null;

    // --- Variables Remplacements ---
    public $replace_user_id;
    public $replace_actions = [];
    public $date_begin;
    public $date_end;
    public $userReplacements = [];

    // --- NOUVEAU : Variables Infos Professionnelles (Admin) ---
    public $poste;
    public $dir_id;
    public $sd_id;
    public $dep_id;
    public $serv_id;
    public $manager_id;

    public $directions_list = [];
    public $sd_list = [];
    public $dep_list = [];
    public $serv_list = [];

    public $showDeleteModal = false;
    public $structureToDeleteId = null;
    public $structureToDeleteName = '';

    public function updatingActiveTab()
    {
        $this->resetPage();
        $this->search = '';
        $this->resetValidation();
        $this->reset(['showModal', 'selectedMemo', 'userReplacements', 'viewingMemoId', 'itemId', 'ref', 'name', 'upper_id', 'poste', 'dir_id', 'sd_id', 'dep_id', 'serv_id', 'manager_id']); 
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // --- NOUVEAU : Logique de mise à jour dynamique des listes ---
    public function updatedDirId($value) {
        $this->sd_list = Entity::where('upper_id', $value)->get();
        $this->sd_id = $this->dep_id = $this->serv_id = null;
        $this->dep_list = $this->serv_list = [];
    }

    public function updatedSdId($value) {
        $this->dep_list = Entity::where('upper_id', $value)->get();
        $this->dep_id = $this->serv_id = null;
        $this->serv_list = [];
    }

    public function updatedDepId($value) {
        $this->serv_list = Entity::where('upper_id', $value)->get();
        $this->serv_id = null;
    }

    // =========================================================
    // LOGIQUE UTILISATEURS
    // =========================================================

    public function toggleAdmin($userId)
    {
        // 1. Trouver l'utilisateur ciblé
        $user = User::findOrFail($userId);

        // 2. Empêcher de s'auto-modifier ses propres droits
        if ($user->id !== auth()->id()) {
            
            // 3. Basculer l'état (Toggle)
            $user->update(['is_admin' => !$user->is_admin]);

            // 4. Préparer l'objet de la notification selon le nouvel état
            $object = $user->is_admin 
                ? "Attribution des privilèges Administrateur" 
                : "Révocation des privilèges Administrateur";

            // 5. ENVOYER LA NOTIFICATION
            try {
                $user->notify(new \App\Notifications\AdminNotification(
                    'admin',        // Type
                    auth()->user(), // Acteur (l'admin qui agit)
                    $user,          // Auteur (le user qui subit)
                    $object         // L'objet ajouté
                ));
            } catch (\Exception $e) {
                // Optionnel : \Log::error("Erreur notification admin: " . $e->getMessage());
            }

            $this->dispatch('notify', message: "Droits d'administration mis à jour.");
        }
    }

    public function confirmToggleStatus($userId)
    {
        $user = User::findOrFail($userId);
        if ($user->id === auth()->id()) return;
        if ($user->is_active) {
            $this->userToToggleId = $userId;
            $this->blocking_reason = '';
            $this->showDeactivationModal = true;
        } else {
            $user->update(['is_active' => true, 'blocking_reason' => null]);
            $this->dispatch('notify', message: "Compte réactivé.");
        }
    }

    public function processDeactivation()
    {
        $this->validate(['blocking_reason' => 'required|min:5|string']);
        $user = User::findOrFail($this->userToToggleId);
        $user->update(['is_active' => false, 'blocking_reason' => $this->blocking_reason]);
        $this->showDeactivationModal = false;
        $this->dispatch('notify', message: "Compte suspendu.");
    }

    // NOUVEAU : Sauvegarde des informations pro par l'admin
    public function saveUserProInfo()
    {
        $this->validate([
            'poste' => 'required',
            'dir_id' => 'required|exists:entities,id',
        ]);

        $user = User::findOrFail($this->itemId);
        
        $user->update([
            'poste'      => $this->poste,
            'dir_id'     => $this->dir_id,
            'sd_id'      => $this->sd_id,
            'dep_id'     => $this->dep_id,
            'serv_id'    => $this->serv_id,
            'manager_id' => $this->manager_id,
        ]);

        // 1. Définition de l'objet de la notification
        $object = "Mise à jour de vos informations professionnelles et de votre affectation";

        // 2. Envoi de la notification
        try {
            $user->notify(new AdminNotification(
                'profil',        // Type
                auth()->user(),  // Acteur (l'admin)
                $user,           // Auteur/Sujet (l'agent)
                $object          // L'objet ajouté
            ));
        } catch (\Exception $e) {
            // Optionnel : \Log::error("Erreur notification profil: " . $e->getMessage());
        }

        $this->dispatch('notify', message: "Informations professionnelles mises à jour et agent notifié.");
    }
    
    // =========================================================
    // LOGIQUE AUDIT
    // =========================================================

    public function openAuditDetails($memoId) {
        $this->viewingMemoId = $memoId;
        $this->selectedMemo = Memo::with(['user.entity', 'historiques.user', 'destinataires.entity', 'parent.user', 'replies.user'])->findOrFail($memoId);
    }

    public function closeAuditDetails() {
        $this->reset(['viewingMemoId', 'selectedMemo']);
    }

    // =========================================================
    // LOGIQUE STRUCTURES
    // =========================================================

    public function openCreateModal()
    {
        $this->reset(['ref', 'name', 'upper_id', 'itemId', 'isEditing', 'userReplacements']);
        $this->type = $this->activeTab;
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $this->itemId = $id;

        if ($this->activeTab === 'users') {
            $user = User::with('replacements.substitute')->findOrFail($id);
            $this->userReplacements = $user->replacements; 
            
            // Initialisation des champs Professionnels
            $this->poste = $user->poste;
            $this->dir_id = $user->dir_id;
            $this->sd_id = $user->sd_id;
            $this->dep_id = $user->dep_id;
            $this->serv_id = $user->serv_id;
            $this->manager_id = $user->manager_id;

            // Chargement des listes initiales
            $this->directions_list = Entity::where('type', 'Direction')->get();
            if ($this->dir_id) $this->sd_list = Entity::where('upper_id', $this->dir_id)->get();
            if ($this->sd_id) $this->dep_list = Entity::where('upper_id', $this->sd_id)->get();
            if ($this->dep_id) $this->serv_list = Entity::where('upper_id', $this->dep_id)->get();
        } 
        else {
            $model = Entity::findOrFail($id);
            $this->ref = $model->ref; 
            $this->name = $model->name; 
            $this->type = $model->type;
            $this->upper_id = $model->upper_id;
        }

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function saveStructure()
    {
        $this->validate([
            'ref' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'upper_id' => $this->activeTab !== 'Direction' ? 'required' : 'nullable'
        ]);

        Entity::updateOrCreate(
            ['id' => $this->itemId],
            [
                'ref' => $this->ref,
                'name' => $this->name,
                'type' => $this->activeTab,
                'upper_id' => $this->upper_id
            ]
        );

        $this->showModal = false;
        $this->dispatch('notify', message: "Structure enregistrée avec succès.");
    }

   

    // =========================================================
    // LOGIQUE REMPLACEMENTS
    // =========================================================

    public function addReplacement()
    {
        $this->validate([
            'replace_user_id' => 'required|exists:users,id|different:itemId',
            'date_begin'      => 'required|date',
            'date_end'        => 'required|date|after_or_equal:date_begin',
            'replace_actions' => 'required|array|min:1',
        ]);

        ReplacesUser::create([
            'user_id'            => $this->itemId,
            'user_id_replace'    => $this->replace_user_id,
            'action_replace'     => $this->replace_actions, 
            'date_begin_replace' => $this->date_begin,
            'date_end_replace'   => $this->date_end,
        ]);

        // 2. ENVOI DE LA NOTIFICATION
        $user = User::find($this->itemId);
        if ($user) {
            $object = "Nouvelle délégation d'intérim configurée sur votre compte";
            try {
                $user->notify(new \App\Notifications\AdminNotification(
                    'interims_delegations', 
                    auth()->user(), 
                    $user,
                    $object // 4ème argument ajouté
                ));
            } catch (\Exception $e) {
                // Silence ou log
            }
        }

        $this->userReplacements = ReplacesUser::where('user_id', $this->itemId)->with('substitute')->get();
        $this->reset(['replace_user_id', 'date_begin', 'date_end', 'replace_actions']);
        $this->dispatch('notify', message: 'Remplaçant ajouté.');
    }

    public function removeReplacement($replacementId)
    {
        $rep = ReplacesUser::find($replacementId);
        
        if ($rep && $rep->user_id == $this->itemId) {
            // 1. On récupère l'utilisateur
            $user = User::find($this->itemId);

            // 2. Suppression
            $rep->delete();

            // 3. ENVOI DE LA NOTIFICATION
            if ($user) {
                $object = "Retrait d'une délégation d'intérim sur votre compte";
                try {
                    $user->notify(new \App\Notifications\AdminNotification(
                        'interims_delegations', 
                        auth()->user(), 
                        $user,
                        $object // 4ème argument ajouté
                    ));
                } catch (\Exception $e) {
                    // Silence
                }
            }

            $this->userReplacements = ReplacesUser::where('user_id', $this->itemId)->with('substitute')->get();
            $this->dispatch('notify', message: 'Délégation supprimée.');
        }
    }

    // --- Modifiez ou ajoutez ces méthodes ---

    /**
     * Prépare la suppression et ouvre la modale
     */
    public function confirmDeleteStructure($id)
    {
        $structure = Entity::findOrFail($id);
        $this->structureToDeleteId = $id;
        $this->structureToDeleteName = $structure->name;
        $this->showDeleteModal = true;
    }

    /**
     * Exécute la suppression réelle
     */
    public function deleteStructure()
    {
        if ($this->structureToDeleteId) {
            $entity = Entity::find($this->structureToDeleteId);
            if ($entity) {
                $entity->delete();
                $this->dispatch('notify', message: "L'élément '{$this->structureToDeleteName}' a été supprimé.");
            }
            
            $this->reset(['showDeleteModal', 'structureToDeleteId', 'structureToDeleteName']);
        }
    }

    public function render()
    {
        $searchTerm = '%' . $this->search . '%';
        $stats = [];

        if ($this->activeTab === 'users') {
            $data = User::with('dir')
                ->where('last_name', 'like', $searchTerm)
                ->orWhere('email', 'like', $searchTerm)
                ->latest()->paginate(10);
        } 
        elseif ($this->activeTab === 'audit') {
            $stats = [
                'total' => Memo::count(),
                'integrity_rate' => Memo::count() > 0 ? round((Memo::whereNotNull('qr_code')->count() / Memo::count()) * 100) : 0,
                'today' => Memo::whereDate('created_at', now())->count(),
            ];
            $data = Memo::with('user')->where('object', 'like', $searchTerm)->latest()->paginate(10);
        } 
        else {
            $data = Entity::where('type', $this->activeTab)
                ->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm)->orWhere('ref', 'like', $searchTerm);
                })
                ->latest()->paginate(10);
        }

        return view('livewire.setting.settings', ['data' => $data, 'stats' => $stats]);
    }
}