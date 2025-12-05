<?php

namespace App\Livewire\Setting;

use App\Models\User;
use App\Models\Entity;
use App\Models\SousDirection;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class Settings extends Component
{
    use WithPagination;

    // --- ÉTAT GLOBAL ---
    public $activeTab = 'users'; // 'users', 'entities', 'sous_directions'
    public $search = '';

    // --- VARIABLES STRUCTURES (Entités/SD) ---
    public $showModal = false;
    public $isEditing = false;
    public $itemId = null;
    public $ref;
    public $name;

    // --- NAVIGATION & RECHERCHE ---
    
    // Réinitialiser pagination et recherche quand on change d'onglet
    public function updatedActiveTab()
    {
        $this->resetPage();
        $this->search = '';
        $this->resetValidation();
    }

    // Réinitialiser pagination quand on cherche
    public function updatedSearch()
    {
        $this->resetPage();
    }

    // --- LOGIQUE UTILISATEURS (Votre code existant) ---

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

    // --- LOGIQUE STRUCTURES (Nouveau code) ---

    protected function rules()
    {
        // Détermine la table pour la règle unique
        $table = $this->activeTab === 'entities' ? 'entities' : 'sous_direction'; // Vérifiez le nom de votre table sous_directions dans la BDD
        
        return [
            'ref' => "required|string|max:50|unique:$table,ref," . ($this->itemId ?? 'NULL'),
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
        // Sélection du modèle selon l'onglet
        $model = $this->activeTab === 'entities' ? Entity::find($id) : SousDirection::find($id);
        
        $this->itemId = $model->id;
        $this->ref = $model->ref;
        $this->name = $model->name;
        $this->isEditing = true;
        $this->showModal = true;
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

        // Chargement conditionnel des données selon l'onglet
        if ($this->activeTab === 'users') {
            $data = User::query()
                ->where(function($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%') // Ajout recherche nom
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } 
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
            'data' => $data // On passe une variable générique $data
        ]);
    }
}