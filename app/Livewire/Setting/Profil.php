<?php

namespace App\Livewire\Setting;

use App\Enums\Poste;
use App\Models\User;
use App\Models\Entity;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class Profil extends Component
{
    /** @var User */
    public $user;

    public $entites; 
    public $sous_directions = [];
    public $departements = [];
    public $services = [];
    public $user_all;

    public $dir_id;
    public $sd_id;
    public $dep_id;
    public $serv_id;
    public $poste;
    public $manager_id;
    public $isLocked = false;


    public $searchManager = ''; 
    public $searchDirection = '';
    public $searchSousDirection = '';
    public $searchDepartement = '';
    public $searchService = '';

    public $darkMode = false; // État du mode sombre

    /**
     * INITIALISATION
     */
    public function mount()
    {
        $this->darkMode = session()->get('dark_mode', false);
        $this->user = Auth::user()->load(['replacements.substitute', 'replacing.user']);

        $this->entites = \App\Models\Entity::where('type', 'Direction')->get();

         // Pré-remplissage au chargement initial
        $this->dir_id = $this->user->dir_id;
        if ($this->user->direction) {
            // Affiche "REF - NOM" pour que l'utilisateur sache ce qu'il voit
            $this->searchDirection = $this->user->direction->ref . ' - ' . $this->user->direction->name;
        }

        // LOGIQUE DE VERROUILLAGE : 
        // Si le profil est marqué comme verrouillé ET que l'utilisateur n'est PAS admin
        if ($this->user->profile_locked && !$this->user->is_admin) {
            $this->isLocked = true;
        }

        $this->entites = Entity::whereNull('upper_id')->orWhere('type', 'direction')->get();
        $this->user_all = User::select('id', 'first_name', 'last_name')
            ->where('id', '!=', $this->user->id)
            ->get();

        $this->poste = $this->user->poste;
        $this->dir_id = $this->user->dir_id;
        $this->sd_id = $this->user->sd_id;
        $this->dep_id = $this->user->dep_id;
        $this->serv_id = $this->user->serv_id;
        $this->manager_id = $this->user->manager_id;

        // Initialisation du champ de recherche Manager
        if ($this->user->manager_id) {
            $manager = User::find($this->user->manager_id);
            // On vérifie si le manager existe toujours
            if ($manager) {
                $this->searchManager = $manager->first_name . ' ' . $manager->last_name;
            }
        }

       // 1. Direction
        $this->entites = Entity::where('type', 'Direction')->get();
        if ($this->user->dir_id) {
            $dir = $this->entites->firstWhere('id', $this->user->dir_id);
            if ($dir) $this->searchDirection = $dir->name;
            
            // Charge les Sous-Directions
            $this->sous_directions = Entity::where('upper_id', $this->user->dir_id)->get();
        }

        // 2. Sous-Direction
        if ($this->user->sd_id) {
            $sd = $this->sous_directions->firstWhere('id', $this->user->sd_id);
            if ($sd) $this->searchSousDirection = $sd->name;

            // Charge les Départements
            $this->departements = Entity::where('upper_id', $this->user->sd_id)->get();
        }

        // 3. Département
        if ($this->user->dep_id) {
            $dep = $this->departements->firstWhere('id', $this->user->dep_id);
            if ($dep) $this->searchDepartement = $dep->name;

            // Charge les Services
            $this->services = Entity::where('upper_id', $this->user->dep_id)->get();
        }

        // 4. Service
        if ($this->user->serv_id) {
            $serv = $this->services->firstWhere('id', $this->user->serv_id);
            if ($serv) $this->searchService = $serv->name;
        }
    }

    // 1. REINITIALISATION EN CASCADE
    public function updatedDirId($value)
    {
        $this->sous_directions = Entity::where('upper_id', $value)->get();
        
        // Reset des entités enfants
        $this->sd_id = $this->dep_id = $this->serv_id = null;
        $this->searchSousDirection = $this->searchDepartement = $this->searchService = '';
        $this->departements = $this->services = [];

        // --- IMPORTANT : Reset du Manager quand la direction change ---
        // Car les managers de l'ancienne direction ne sont plus valides
        $this->manager_id = null;
        $this->searchManager = '';
    }

    public function getFilteredDirectionsProperty()
    {
        // Si la recherche est vide, on peut soit ne rien retourner, 
        // soit retourner toutes les directions (ici je retourne tout pour le confort)
        if (empty($this->searchDirection)) {
            return $this->entites;
        }

        $searchTerm = strtolower($this->searchDirection);
        
        // On filtre la collection $this->entites (déjà chargée dans mount)
        return $this->entites->filter(function($entite) use ($searchTerm) {
            return str_contains(strtolower($entite->name), $searchTerm) || 
                str_contains(strtolower($entite->ref ?? ''), $searchTerm);
        });
    }

   public function getFilteredSousDirectionsProperty()
    {
        if (empty($this->searchSousDirection)) return $this->sous_directions;
        
        $search = strtolower($this->searchSousDirection);
        
        return $this->sous_directions->filter(function($i) use ($search) {
            return str_contains(strtolower($i->name), $search) || 
                str_contains(strtolower($i->ref ?? ''), $search);
        });
    }

    public function getFilteredDepartementsProperty()
    {
        if (empty($this->searchDepartement)) return $this->departements;
        
        $search = strtolower($this->searchDepartement);

        return $this->departements->filter(function($i) use ($search) {
            return str_contains(strtolower($i->name), $search) || 
                str_contains(strtolower($i->ref ?? ''), $search);
        });
    }

    public function getFilteredServicesProperty()
    {
        if (empty($this->searchService)) return $this->services;
        
        $search = strtolower($this->searchService);

        return $this->services->filter(function($i) use ($search) {
            return str_contains(strtolower($i->name), $search) || 
                str_contains(strtolower($i->ref ?? ''), $search);
        });
    }

    // 2. FILTRE STRICT PAR DIRECTION
    public function getFilteredManagersProperty()
    {
        // Si aucune direction n'est sélectionnée, on ne propose personne
        if (empty($this->dir_id)) {
            return collect();
        }

        $searchTerm = strtolower($this->searchManager);

        // On cherche les utilisateurs :
        // - Qui sont dans la même direction (dir_id)
        // - Qui ne sont pas moi-même (id != user->id)
        // - Qui correspondent à la recherche (Nom ou Prénom)
        return User::where('dir_id', $this->dir_id)
            ->where('id', '!=', $this->user->id) 
            ->where(function($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                ->orWhere('last_name', 'like', "%{$searchTerm}%");
            })
            ->get();
    }

    // Méthode appelée quand on clique sur une direction dans la liste
    public function selectDirection($id, $name)
    {
        $this->dir_id = $id;
        $this->searchDirection = $name; // Met le nom dans le champ texte
        
        // On déclenche manuellement la logique de cascade (chargement sous-directions)
        // car updatedDirId ne se déclenche parfois pas si on set la valeur directement
        $this->updatedDirId($id); 
    }

     public function selectSousDirection($id, $name)
    {
        $this->sd_id = $id;
        $this->searchSousDirection = $name;
        $this->updatedSdId($id); // Déclenche le chargement des départements
    }

    public function selectDepartement($id, $name)
    {
        $this->dep_id = $id;
        $this->searchDepartement = $name;
        $this->updatedDepId($id); // Déclenche le chargement des services
    }

    public function selectService($id, $name)
    {
        $this->serv_id = $id;
        $this->searchService = $name;
    }

    // 3. SÉLECTION AVEC VÉRIFICATION (Double sécurité)
    public function selectManager($id, $name)
    {
        $targetManager = User::find($id);

        // Sécurité : Si l'utilisateur tente de forcer un ID via l'inspecteur
        if ($targetManager && $targetManager->dir_id != $this->dir_id) {
            $this->addError('manager_id', "Ce manager ne fait pas partie de votre direction.");
            return;
        }

        $this->manager_id = $id;
        $this->searchManager = $name;
    }

    

    

    /**
     * ÉCOUTEUR DARK MODE
     */
    #[On('dark-mode-toggled')]
    public function updateDarkMode($darkMode)
    {
        $this->darkMode = $darkMode;
    }

    /**
     * LOGIQUE DE MISE À JOUR DYNAMIQUE
     */
    

    public function updatedSdId($value)
    {
        $this->departements = Entity::where('upper_id', $value)->get();
        $this->dep_id = $this->serv_id = null;
        $this->services = [];
    }

    public function updatedDepId($value)
    {
        $this->services = Entity::where('upper_id', $value)->get();
        $this->serv_id = null;
    }

    /**
     * ENREGISTREMENT
     */
    public function save()
    {
        // Sécurité supplémentaire côté serveur
        if ($this->isLocked) {
            $this->dispatch('notify', message: "Action non autorisée. Votre profil est verrouillé.", type: 'error');
            return;
        }

        $this->validate([
            'dir_id'  => 'required|exists:entities,id',
            'poste'   => 'required',
        ]);

        $this->user->update([
            'dir_id'     => $this->dir_id,
            'sd_id'      => $this->sd_id,
            'dep_id'     => $this->dep_id,
            'serv_id'    => $this->serv_id,
            'poste'      => $this->poste,
            'manager_id' => $this->manager_id,
            'profile_locked' => true, 
        ]);

        // Une fois enregistré, on fige immédiatement pour l'interface
        if (!$this->user->is_admin) {
            $this->isLocked = true;
        }

        $this->dispatch('notify', message: "Profil mis à jour !");
    }

    public function render()
    {
        return view('livewire.setting.profil');
    }
}