<?php

namespace App\Livewire\Setting;

use App\Models\User;
use App\Models\Entity;
use App\Models\Memo;       
use App\Models\Historiques; 
use Livewire\Component;
use App\Models\ReplacesUser;
use Livewire\WithPagination;
use App\Models\SousDirection;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode; 

class Settings extends Component
{
    use WithPagination;

    // =========================================================
    // 1. PROPRIÉTÉS DU COMPOSANT
    // =========================================================

    // --- État Global ---
    public $activeTab = 'users'; 
    public $search = '';
    public $viewingMemoId = null;
    public $showDeactivationModal = false;
    public $blocking_reason = '';
    public $userToToggleId = null;

    // --- Variables Structures (Entités/SD) ---
    public $showModal = false;
    public $isEditing = false;
    public $itemId = null;
    public $ref;
    public $name;

    // --- Variables Audit ---
    public $auditMemoId = null;
    public $auditHistory = [];
    public $selectedMemo = null;
    public $showAuditModal = false;

    // --- Variables Remplacements ---
    public $replace_user_id;
    public $replace_actions = [];
    public $date_begin;
    public $date_end;
    public $userReplacements = [];

    // =========================================================
    // 2. NAVIGATION ET RECHERCHE
    // =========================================================

    /**
     * Hook appelé avant le changement d'onglet
     * Optimisation : Nettoie la mémoire et réinitialise la pagination
     */
    public function updatingActiveTab()
    {
        $this->resetPage();
        $this->search = '';
        $this->resetValidation();
        // Reset des données lourdes pour alléger le transfert réseau
        $this->reset(['showAuditModal', 'selectedMemo', 'userReplacements', 'viewingMemoId']); 
    }

    /**
     * Hook appelé lors de la saisie dans la recherche
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // =========================================================
    // 3. LOGIQUE UTILISATEURS
    // =========================================================

    public function toggleAdmin($userId)
    {
        $user = User::findOrFail($userId);
        if ($user->id !== auth()->id()) {
            $user->update(['is_admin' => !$user->is_admin]);
            $this->dispatch('notify', message: "Droits d'administration mis à jour.");
        }
    }

    public function confirmToggleStatus($userId)
    {
        $user = User::findOrFail($userId);
        
        if ($user->id === auth()->id()) {
            return; // On ne se bloque pas soi-même
        }

        if ($user->is_active) {
            // Si l'utilisateur est actif, on demande le motif pour le bloquer
            $this->userToToggleId = $userId;
            $this->blocking_reason = '';
            $this->showDeactivationModal = true;
        } else {
            // Si l'utilisateur est déjà inactif, on le réactive directement
            $user->update([
                'is_active' => true,
                'blocking_reason' => null
            ]);
            $this->dispatch('notify', message: "Compte réactivé avec succès.");
        }
    }

    public function processDeactivation()
    {
        $this->validate([
            'blocking_reason' => 'required|min:5|string'
        ], [
            'blocking_reason.required' => 'Le motif est obligatoire pour désactiver un compte.'
        ]);

        $user = User::findOrFail($this->userToToggleId);
        $user->update([
            'is_active' => false,
            'blocking_reason' => $this->blocking_reason
        ]);

        $this->showDeactivationModal = false;
        $this->dispatch('notify', message: "Le compte a été suspendu.");
    }

    // =========================================================
    // 4. LOGIQUE AUDIT / SUPERVISION
    // =========================================================

    /**
     * Ouvre les détails complets d'un mémo pour audit
     * Optimisation : Eager loading massif pour éviter les requêtes N+1
     */
    public function openAuditDetails($memoId) {
        $this->viewingMemoId = $memoId;
        $this->selectedMemo = Memo::with([
            'user.entity', 
            'historiques.user', 
            'destinataires.entity',
            'parent.user', 
            'replies.destinataires.entity', 
            'replies.user'
        ])->findOrFail($memoId);
    }

    /**
     * Ferme l'audit et libère la mémoire
     */
    public function closeAuditDetails() {
        $this->reset(['viewingMemoId', 'selectedMemo']);
    }

    // =========================================================
    // 5. LOGIQUE STRUCTURES (CRUD)
    // =========================================================

    protected function rules()
    {
        $table = $this->activeTab === 'entities' ? 'entities' : 'sous_direction'; 
        return [
            'ref' => 'required|string|max:50|unique:'.$table.',ref,'.($this->itemId),
            'name' => 'required|string|max:255',
        ];
    }

    public function openCreateModal()
    {
        $this->reset(['ref', 'name', 'itemId', 'isEditing', 'userReplacements']);
        $this->showModal = true;
    }

    /**
     * Prépare l'édition d'un utilisateur ou d'une structure
     */
    public function openEditModal($id)
    {
        $this->resetValidation();
        
        if ($this->activeTab === 'users') {
            $model = User::with('replacements.substitute')->findOrFail($id);
            $this->userReplacements = $model->replacements; 
            $this->itemId = $model->id; 
        } 
        else {
            $model = $this->activeTab === 'entities' ? Entity::findOrFail($id) : SousDirection::findOrFail($id);
            $this->itemId = $model->id;
            $this->ref = $model->ref; 
            $this->name = $model->name; 
        }

        $this->isEditing = true;
        $this->showModal = true;
    }

    // =========================================================
    // 6. GESTION DES REMPLAÇANTS
    // =========================================================

    public function addReplacement()
    {
        $this->validate([
            'replace_user_id' => 'required|exists:users,id|different:itemId',
            'date_begin'      => 'required|date',
            'date_end'        => 'required|date|after_or_equal:date_begin',
            'replace_actions' => 'required|array|min:1',
        ], [
            'replace_user_id.required' => 'Veuillez sélectionner un remplaçant.',
            'replace_user_id.different'=> 'L\'utilisateur ne peut pas se remplacer lui-même.',
            'replace_actions.required' => 'Veuillez choisir au moins une action autorisée.',
            'date_end.after_or_equal'  => 'La date de fin doit être postérieure à la date de début.'
        ]);

        ReplacesUser::create([
            'user_id'            => $this->itemId,
            'user_id_replace'    => $this->replace_user_id,
            'action_replace'     => $this->replace_actions, 
            'date_begin_replace' => $this->date_begin,
            'date_end_replace'   => $this->date_end,
        ]);

        // Rafraichissement optimisé de la liste
        $this->userReplacements = ReplacesUser::where('user_id', $this->itemId)->with('substitute')->get();
        $this->reset(['replace_user_id', 'date_begin', 'date_end', 'replace_actions']);
        $this->dispatch('notify', message: 'Remplaçant ajouté avec succès.');
    }

    public function removeReplacement($replacementId)
    {
        $rep = ReplacesUser::find($replacementId);
        if ($rep && $rep->user_id == $this->itemId) {
            $rep->delete();
            $this->userReplacements = ReplacesUser::where('user_id', $this->itemId)->with('substitute')->get();
            $this->dispatch('notify', message: 'Remplacement supprimé.');
        }
    }

    public function saveStructure()
    {
        $this->validate();

        if ($this->activeTab === 'entities') {
            $model = $this->isEditing ? Entity::find($this->itemId) : new Entity();
        } else {
            $model = $this->isEditing ? SousDirection::find($this->itemId) : new SousDirection();
        }

        $model->ref = $this->ref;
        $model->name = $this->name;
        $model->save();

        $this->showModal = false;
        $this->dispatch('notify', message: ($this->isEditing ? 'Modification' : 'Création') . ' effectuée avec succès !');
    }

    public function deleteStructure($id)
    {
        if ($this->activeTab === 'entities') {
            Entity::findOrFail($id)->delete();
        } else {
            SousDirection::findOrFail($id)->delete();
        }
        $this->dispatch('notify', message: 'Élément supprimé.');
    }

    // =========================================================
    // 7. RENDU ET PERFORMANCE
    // =========================================================

    public function render()
    {
        $data = [];
        $stats = [];
        $searchTerm = '%' . $this->search . '%';

        // 1. CHARGEMENT DONNÉES UTILISATEURS (Optimisé avec Entity)
        if ($this->activeTab === 'users') {
            $data = User::query()
                ->with('entity') // Eager loading pour le nom de l'entité
                ->where(function($q) use ($searchTerm) {
                    $q->where('first_name', 'like', $searchTerm)
                      ->orWhere('last_name', 'like', $searchTerm) 
                      ->orWhere('email', 'like', $searchTerm);
                })
                ->latest()
                ->paginate(10);
        } 
        // 2. CHARGEMENT DONNÉES AUDIT (SUPERVISION)
        elseif ($this->activeTab === 'audit') {
            // Optimisation : On calcule toutes les stats en une passe
            $stats = [
                'total' => Memo::count(),
                'verified' => Memo::whereNotNull('qr_code')->count(),
                'integrity_rate' => Memo::count() > 0 ? round((Memo::whereNotNull('qr_code')->count() / Memo::count()) * 100) : 0,
                'pending' => Memo::where('status', 'document')->count(),
                'today' => Memo::whereDate('created_at', now())->count(),
            ];

            // Liste des mémos avec eager loading de l'auteur
            $data = Memo::with('user')
                ->where(function($q) use ($searchTerm) {
                    $q->where('object', 'like', $searchTerm)
                      ->orWhere('reference', 'like', $searchTerm);
                })
                ->latest()
                ->paginate(10);
        }
        // 3. CHARGEMENT STRUCTURES
        elseif ($this->activeTab === 'entities') {
            $data = Entity::query()
                ->where('name', 'like', $searchTerm)
                ->orWhere('ref', 'like', $searchTerm)
                ->latest()
                ->paginate(10);
        } 
        else { // sous_directions
            $data = SousDirection::query()
                ->where('name', 'like', $searchTerm)
                ->orWhere('ref', 'like', $searchTerm)
                ->latest()
                ->paginate(10);
        }

        return view('livewire.setting.settings', [
            'data' => $data,
            'stats' => $stats 
        ]);
    }
}