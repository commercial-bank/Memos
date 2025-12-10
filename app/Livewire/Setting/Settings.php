<?php

namespace App\Livewire\Setting;

use App\Models\User;
use App\Models\Entity;
use Livewire\Component;
use App\Models\ReplacesUser;
use Livewire\WithPagination;
use App\Models\SousDirection;
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

    // --- VARIABLES REMPLACEMENTS ---
    public $replace_user_id;        // ID du remplaçant choisi
    public $replace_actions = [];   // Array pour les checkoxes ['VISER', 'SIGNER']
    public $date_begin;
    public $date_end;
    public $userReplacements = [];  // Liste des remplacements actuels de l'user édité

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
        
        if ($this->activeTab === 'users') {
            $model = User::with('replacements.substitute')->find($id);
            // Charger les remplacements existants pour l'affichage
            $this->userReplacements = $model->replacements; 
            
            // Initialiser les champs de l'utilisateur (votre code existant)
            // ... $this->first_name = $model->first_name ...
        } else {
             // ... logique entity/sd existante
             $model = $this->activeTab === 'entities' ? Entity::find($id) : SousDirection::find($id);
        }

        $this->itemId = $model->id;
        // ... reste de votre logique d'assignation
        
        $this->isEditing = true;
        $this->showModal = true;
    }

     // --- LOGIQUE DE GESTION DES REMPLACANTS ---

    public function addReplacement()
    {
        // Validation spécifique pour l'ajout d'un remplaçant
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

        // Création
        ReplacesUser::create([
            'user_id'            => $this->itemId, // L'utilisateur en cours d'édition
            'user_id_replace'    => $this->replace_user_id,
            'action_replace'     => $this->replace_actions, // Casté automatiquement en JSON par le modèle
            'date_begin_replace' => $this->date_begin,
            'date_end_replace'   => $this->date_end,
        ]);

        // Rechargement de la liste et reset du petit formulaire
        $this->userReplacements = ReplacesUser::where('user_id', $this->itemId)->with('substitute')->get();
        $this->reset(['replace_user_id', 'date_begin', 'date_end', 'replace_actions']);
        
        $this->dispatch('notify', message: 'Remplaçant ajouté avec succès.');
    }

    public function removeReplacement($replacementId)
    {
        $rep = ReplacesUser::find($replacementId);
        if ($rep && $rep->user_id == $this->itemId) {
            $rep->delete();
            // Rechargement de la liste
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