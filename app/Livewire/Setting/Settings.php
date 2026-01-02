<?php

namespace App\Livewire\Setting;

use App\Models\User;
use App\Models\Entity;
use App\Models\Memo;       
use Livewire\Component;
use App\Models\ReplacesUser;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

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
    public $type;       // Nouveau : Gère le type Direction, SD, etc.
    public $upper_id;   // Nouveau : Gère le lien hiérarchique

    // --- Variables Audit ---
    public $selectedMemo = null;

    // --- Variables Remplacements ---
    public $replace_user_id;
    public $replace_actions = [];
    public $date_begin;
    public $date_end;
    public $userReplacements = [];

    public function updatingActiveTab()
    {
        $this->resetPage();
        $this->search = '';
        $this->resetValidation();
        $this->reset(['showModal', 'selectedMemo', 'userReplacements', 'viewingMemoId', 'itemId', 'ref', 'name', 'upper_id']); 
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // =========================================================
    // LOGIQUE UTILISATEURS (INCHANGÉE)
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

    // =========================================================
    // LOGIQUE AUDIT (INCHANGÉE)
    // =========================================================

    public function openAuditDetails($memoId) {
        $this->viewingMemoId = $memoId;
        $this->selectedMemo = Memo::with(['user.entity', 'historiques.user', 'destinataires.entity', 'parent.user', 'replies.user'])->findOrFail($memoId);
    }

    public function closeAuditDetails() {
        $this->reset(['viewingMemoId', 'selectedMemo']);
    }

    // =========================================================
    // LOGIQUE STRUCTURES (MISE À JOUR : TABLE UNIQUE ENTITIES)
    // =========================================================

    public function openCreateModal()
    {
        $this->reset(['ref', 'name', 'upper_id', 'itemId', 'isEditing', 'userReplacements']);
        // Le type est défini par l'onglet actif
        $this->type = $this->activeTab;
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $this->itemId = $id;

        if ($this->activeTab === 'users') {
            $model = User::with('replacements.substitute')->findOrFail($id);
            $this->userReplacements = $model->replacements; 
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

    public function deleteStructure($id)
    {
        Entity::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Élément supprimé.');
    }

    // =========================================================
    // LOGIQUE REMPLACEMENTS (INCHANGÉE)
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

        $this->userReplacements = ReplacesUser::where('user_id', $this->itemId)->with('substitute')->get();
        $this->reset(['replace_user_id', 'date_begin', 'date_end', 'replace_actions']);
        $this->dispatch('notify', message: 'Remplaçant ajouté.');
    }

    public function removeReplacement($replacementId)
    {
        $rep = ReplacesUser::find($replacementId);
        if ($rep && $rep->user_id == $this->itemId) {
            $rep->delete();
            $this->userReplacements = ReplacesUser::where('user_id', $this->itemId)->with('substitute')->get();
        }
    }

    // =========================================================
    // RENDU (MISE À JOUR : FILTRAGE PAR TYPE)
    // =========================================================

    public function render()
    {
        $searchTerm = '%' . $this->search . '%';
        $stats = [];

        if ($this->activeTab === 'users') {
            $data = User::with('entity')
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
            // Filtrage dynamique : Direction, Sous-Direction, Departement, Service
            $data = Entity::where('type', $this->activeTab)
                ->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm)->orWhere('ref', 'like', $searchTerm);
                })
                ->latest()->paginate(10);
        }

        return view('livewire.setting.settings', ['data' => $data, 'stats' => $stats]);
    }
}