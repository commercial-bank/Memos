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

    public $darkMode = false; // État du mode sombre

    /**
     * INITIALISATION
     */
    public function mount()
    {
        $this->darkMode = session()->get('dark_mode', false);
        $this->user = Auth::user()->load(['replacements.substitute', 'replacing.user']);

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

        if ($this->dir_id) { $this->sous_directions = Entity::where('upper_id', $this->dir_id)->get(); }
        if ($this->sd_id) { $this->departements = Entity::where('upper_id', $this->sd_id)->get(); }
        if ($this->dep_id) { $this->services = Entity::where('upper_id', $this->dep_id)->get(); }
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
    public function updatedDirId($value)
    {
        $this->sous_directions = Entity::where('upper_id', $value)->get();
        $this->sd_id = $this->dep_id = $this->serv_id = null;
        $this->departements = $this->services = [];
    }

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