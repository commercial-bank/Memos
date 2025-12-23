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
    // Instance de l'utilisateur connecté
    public $user;

    // Listes pour les menus déroulants (chargées une seule fois)
    public $entites;
    public $sd;
    public $user_all;

    // Variables pour l'affichage initial
    public $user_entity;
    public $user_sd;
    public $user_manager;

    // --- CHAMPS DU FORMULAIRE (wire:model) ---
    public $poste;
    public $departement;
    public $service;
    public $entity_id; 
    public $sous_direction_id;
    public $manager_id;

    /**
     * Initialisation du composant
     */
    public function mount()
    {
        // OPTIMISATION : Eager loading des relations pour éviter le problème N+1
        // On ne sélectionne que l'essentiel pour l'utilisateur
        $this->user = Auth::user()->load(['replacements.substitute', 'replacing.user']);

        // OPTIMISATION : On récupère uniquement les colonnes nécessaires pour alléger la RAM
        $this->entites = Entity::select('id', 'name', 'ref')->orderBy('name')->get();
        $this->sd = SousDirection::select('id', 'name')->orderBy('name')->get();
        
        // OPTIMISATION : On récupère uniquement ID et Noms pour la liste des managers
        $this->user_all = User::select('id', 'first_name', 'last_name')
            ->where('id', '!=', $this->user->id)
            ->orderBy('last_name')
            ->get();
        
        // Initialisation des objets de visualisation (Eager loading manuel pour la rapidité)
        $this->user_entity = $this->entites->firstWhere('id', $this->user->entity_id);
        $this->user_sd = $this->sd->firstWhere('id', $this->user->sous_direction_id);
        $this->user_manager = $this->user_all->firstWhere('id', $this->user->manager_id);

        // Hydratation des champs du formulaire
        $this->poste = $this->user->poste;
        $this->departement = $this->user->departement;
        $this->service = $this->user->service;
        $this->entity_id = $this->user->entity_id;
        $this->sous_direction_id = $this->user->sous_direction_id;
        $this->manager_id = $this->user->manager_id;
    }

    /**
     * Enregistrement des modifications
     */
    public function save()
    {
        // 1. Validation des données avec messages personnalisés
        $this->validate([
            'poste'             => ['required', 'string', 'max:255'],
            'departement'       => ['nullable', 'string', 'max:255', new ProperDepartmentCase()],
            'service'           => ['nullable', 'string', 'max:255', new ProperDepartmentCase()],
            'entity_id'         => ['required', 'exists:entities,id'],
            'sous_direction_id' => ['nullable', 'exists:sous_direction,id'],
            'manager_id'        => ['nullable', 'exists:users,id'],
        ], [
            'poste.required'     => 'Le champ Poste est obligatoire.',
            'entity_id.required' => 'Veuillez sélectionner une Entité.',
        ]);

        // 2. Récupération optimisée des métadonnées de l'entité
        $entityName = null;
        $entitySigle = null;
        
        if ($this->entity_id) {
            // Utilisation de la collection déjà chargée en mémoire au lieu d'une requête SQL
            $selectedEntity = $this->entites->firstWhere('id', $this->entity_id);
            if ($selectedEntity) {
                $entitySigle = $selectedEntity->sigle;
                $entityName  = $selectedEntity->name;
            }
        }

        // 3. Mise à jour de la base de données
        // L'utilisation de update() sur l'instance permet de ne modifier que ce qui a changé
        $this->user->update([
            'poste'             => $this->poste,
            'departement'       => $this->departement,
            'service'           => $this->service,
            'entity_id'         => $this->entity_id,
            'sous_direction_id' => $this->sous_direction_id,
            'manager_id'        => $this->manager_id ?? null,
            
            // Note: Décommentez si vous utilisez des colonnes de déduplication de texte
            // 'entity'         => $entityName, 
            // 'entity_sigle'   => $entitySigle,
        ]);

        // Mise à jour des variables d'affichage pour refléter les changements sans recharger la page
        $this->user_entity = $this->entites->firstWhere('id', $this->entity_id);
        $this->user_manager = $this->user_all->firstWhere('id', $this->manager_id);

        // 4. Notification flash via browser event
        $this->dispatch('notify', message: "Profil mis à jour avec succès !");
    }

    /**
     * Rendu de la vue
     */
    public function render()
    {
        return view('livewire.setting.profil');
    }
}