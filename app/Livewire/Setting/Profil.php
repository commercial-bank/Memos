<?php

namespace App\Livewire\Setting;

use App\Models\User;
use App\Models\Entity;
use Livewire\Component;
use App\Models\SousDirection;
use Illuminate\Validation\Rule;
use App\Rules\ProperDepartmentCase;
use Illuminate\Support\Facades\Auth;

class Profil extends Component
{
    public $user;

    // Listes pour les menus déroulants (Select)
    public $entites;
    public $sd;
    public $user_all;

    // Variables pour l'affichage initial (optionnel si on utilise directement les IDs)
    public $user_entity;
    public $user_sd;
    public $user_manager;

    // --- CHAMPS DU FORMULAIRE (Liés via wire:model) ---
    public $poste;
    public $departement;
    public $service;
    
    // Ces variables doivent correspondre aux wire:model de votre vue
    public $entity_id; 
    public $sous_direction_id;
    public $manager_id;

    // Dans App\Livewire\Setting\Profil.php

    public function mount()
    {
        // On charge l'utilisateur avec ses relations de remplacement
        // 'replacements.substitute' = récupérer les infos des gens qui me remplacent
        // 'replacing.user' = récupérer les infos des gens que je remplace
        $this->user = Auth::user()->load(['replacements.substitute', 'replacing.user']);

        // ... Le reste de votre code mount existant (entites, sd, etc.) ...
        $this->entites = Entity::all();
        $this->sd = SousDirection::all();
        $this->user_all = User::where('id', '!=', $this->user->id)->get();
        
        // ... initialisation des variables ...
        $this->user_entity = Entity::find($this->user->entity_id);
        $this->user_sd = SousDirection::find($this->user->sous_direction_id);
        $this->user_manager = User::find($this->user->manager_id);

        $this->poste = $this->user->poste;
        $this->departement = $this->user->departement;
        $this->service = $this->user->service;
        
        $this->entity_id = $this->user->entity_id;
        $this->sous_direction_id = $this->user->sous_direction_id;
        $this->manager_id = $this->user->manager_id;
    }

    public function save()
    {
        // 1. Validation des données
        $validatedData = $this->validate([
            'poste' => ['required','string', 'max:255'],
            'departement' => ['nullable', 'string', 'max:255', new ProperDepartmentCase()],
            'service' =>  ['nullable', 'string', 'max:255', new ProperDepartmentCase()],
            // Validation des clés étrangères
            'entity_id' => ['required','exists:entities,id'], // Assurez-vous que la table s'appelle 'entities'
            'sous_direction_id' => ['nullable', 'exists:sous_direction,id'],
            'manager_id' => ['nullable', 'exists:users,id'],
        ],[
        // Messages d'erreur personnalisés pour une meilleure expérience utilisateur
        'poste.required'             => 'Le champ Poste est obligatoire.',
        'entity_id.required'         => 'Veuillez sélectionner une Entité.',
    ]);

        // 2. Logique pour récupérer le sigle de l'entité (si nécessaire dans votre DB)
        // Si vous stockez le nom ou le sigle en dur dans la table users :
        $entitySigle = null;
        $entityName = null;
        
        if ($this->entity_id) {
            $selectedEntity = Entity::find($this->entity_id);
            if ($selectedEntity) {
                $entitySigle = $selectedEntity->sigle ?? null; // Si la colonne sigle existe
                $entityName = $selectedEntity->name ?? null;
            }
        }

        // 3. Mise à jour dans la base de données
        // J'utilise $this->manager_id car wire:model="manager_id" dans la vue
        $this->user->update([
            'poste' => $this->poste,
            'departement' => $this->departement,
            'service' => $this->service,
            
            // Mise à jour des clés étrangères (IDs)
            'entity_id' => $this->entity_id,
            'sous_direction_id' => $this->sous_direction_id,
            'manager_id' => $this->manager_id ?? null,
            
            // Si votre base de données utilise d'autres noms de colonnes (comme dans votre ancien code)
            // décommentez et adaptez les lignes ci-dessous :
            // 'entity' => $entityName,       // Si vous stockez le nom texte
            // 'entity_sigle' => $entitySigle,// Si vous stockez le sigle
            // 'n1' => $this->manager_id,     // Si la colonne s'appelle 'n1' au lieu de manager_id
        ]);

        // 4. Message de succès
        $this->dispatch('notify', message: "Profil mis à jour avec succès !");
        
        // Optionnel : Rafraîchir l'utilisateur pour voir les changements immédiatement s'ils sont affichés ailleurs
        // $this->user->refresh(); 
    }

    public function render()
    {
        return view('livewire.setting.profil');
    }
}