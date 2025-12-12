<?php

namespace App\Livewire\Setting;

use App\Models\User;
use App\Models\Entity;
use App\Models\Memo;       // <--- AJOUT
use App\Models\Historiques; // <--- AJOUT
use Livewire\Component;
use App\Models\ReplacesUser;
use Livewire\WithPagination;
use App\Models\SousDirection;
use Illuminate\Validation\Rule;

class Settings extends Component
{
    use WithPagination;

    // --- ÉTAT GLOBAL ---
    // Ajout de 'audit' dans les onglets
    public $activeTab = 'users'; // 'users', 'entities', 'sous_directions', 'audit'
    public $search = '';

    // --- VARIABLES STRUCTURES (Entités/SD) ---
    public $showModal = false;
    public $isEditing = false;
    public $itemId = null;
    public $ref;
    public $name;

    // --- VARIABLES AUDIT (Nouveau) ---
    public $auditMemoId = null;
    public $auditHistory = [];
    public $selectedMemo = null;
    public $showAuditModal = false;

    // --- VARIABLES REMPLACEMENTS ---
    public $replace_user_id;
    public $replace_actions = [];
    public $date_begin;
    public $date_end;
    public $userReplacements = [];

    // --- NAVIGATION & RECHERCHE ---
    
    public function updatedActiveTab()
    {
        $this->resetPage();
        $this->search = '';
        $this->resetValidation();
        // Reset des modales spécifiques
        $this->showAuditModal = false; 
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    // --- LOGIQUE UTILISATEURS ---
    public function toggleAdmin($userId)
    {
        $user = User::findOrFail($userId);
        if ($user->id !== auth()->id()) {
            $user->update(['is_admin' => !$user->is_admin]);
            $this->dispatch('notify', message: "Droits d'administration mis à jour.");
        }
    }

    public function toggleStatus($userId)
    {
        $user = User::findOrFail($userId);
        if ($user->id !== auth()->id()) {
            $user->update(['is_active' => !$user->is_active]);
            $this->dispatch('notify', message: "Statut du compte mis à jour.");
        }
    }

    // --- LOGIQUE AUDIT / SUPERVISION (NOUVEAU) ---

    public function openAuditDetails($memoId)
    {
        $this->selectedMemo = Memo::with('user')->find($memoId);
        
        // Récupération de l'historique chronologique
        $this->auditHistory = Historiques::where('memo_id', $memoId)
            ->with('user') // Assurez-vous que la relation user existe dans Historique
            ->orderBy('created_at', 'desc')
            ->get();

        $this->showAuditModal = true;
    }

    // --- LOGIQUE STRUCTURES ---

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
        $this->reset(['ref', 'name', 'itemId', 'isEditing']);
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        
        if ($this->activeTab === 'users') {
            $model = User::with('replacements.substitute')->find($id);
            $this->userReplacements = $model->replacements; 
            $this->itemId = $model->id; 
        } 
        else {
            $model = $this->activeTab === 'entities' ? Entity::find($id) : SousDirection::find($id);
            $this->itemId = $model->id;
            $this->ref = $model->ref; 
            $this->name = $model->name; 
        }

        $this->isEditing = true;
        $this->showModal = true;
    }

    // --- LOGIQUE DE GESTION DES REMPLACANTS ---

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
            Entity::find($id)->delete();
        } else {
            SousDirection::find($id)->delete();
        }
        $this->dispatch('notify', message: 'Élément supprimé.');
    }

    // --- RENDER ---

    public function render()
    {
        $data = [];
        $stats = [];

        // 1. CHARGEMENT DONNÉES UTILISATEURS
        if ($this->activeTab === 'users') {
            $data = User::query()
                ->where(function($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%') 
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } 
        // 2. CHARGEMENT DONNÉES AUDIT (SUPERVISION)
        elseif ($this->activeTab === 'audit') {
            // Stats globales pour le dashboard admin
            $stats = [
                'total' => Memo::count(),
                'pending' => Memo::where('status', 'document')->orWhere('status', 'like', '%cours%')->count(), // Ajuster selon vos statuts réels
                'signed' => Memo::whereNotNull('signature_dir')->count(),
                'today' => Memo::whereDate('created_at', now())->count(),
            ];

            // Liste des mémos avec recherche
            $data = Memo::with('user')
                ->where(function($q) {
                    $q->where('object', 'like', '%'.$this->search.'%')
                      ->orWhere('reference', 'like', '%'.$this->search.'%');
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }
        // 3. CHARGEMENT STRUCTURES
        elseif ($this->activeTab === 'entities') {
            $data = Entity::query()
                ->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('ref', 'like', '%'.$this->search.'%')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } 
        else { // sous_directions
            $data = SousDirection::query()
                ->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('ref', 'like', '%'.$this->search.'%')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        return view('livewire.setting.settings', [
            'data' => $data,
            'stats' => $stats // On passe les stats à la vue
        ]);
    }
}