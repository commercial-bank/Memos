<?php

namespace App\Livewire\nav;

use Livewire\Component;
use Livewire\Attributes\On; // N'oubliez pas d'importer l'attribut On
//use App\Models\User;

class TopBar extends Component
{

    //public User $user; // Déclare une propriété publique de type User
    public $currentContent = 'dashboard-content'; // Contenu initial
    public $navbarTitle = 'Dashboard'; // Titre de la navbar

    
    /*Méthode de montage du composant
    public function mount(User $user)
    {
        $this->user = $user;
    }*/

    // Nouvelle façon d'écouter un événement dans Livewire 3
    #[On('tabSelected')]
    public function tabSelected($tab) // Le nom de la méthode peut être différent, mais ici c'est clair
    {
        // Met à jour le contenu et le titre en fonction de l'onglet sélectionné
        switch ($tab) {
            case 'dashboard':
                $this->currentContent = 'dashboard-content';
                $this->navbarTitle = 'Dashboard';
                break;
            case 'memos':
                $this->currentContent = 'memos-content';
                $this->navbarTitle = 'Memos';
                break;
            case 'analytic':
                $this->currentContent = 'analytic-content';
                $this->navbarTitle = 'courriers';
                break;
            case 'projects':
                $this->currentContent = 'projects-content';
                $this->navbarTitle = 'Projects';
                break;
            case 'groups':
                $this->currentContent = 'groups-content';
                $this->navbarTitle = 'Groups';
                break;
            case 'reports':
                $this->currentContent = 'reports-content';
                $this->navbarTitle = 'Reports';
                break;
            case 'profile':
                $this->currentContent = 'profile-content';
                $this->navbarTitle = 'Profile';
                break;
            case 'settings':
                $this->currentContent = 'settings-content';
                $this->navbarTitle = 'Settings';
                break;
            default:
                $this->currentContent = 'dashboard-content';
                $this->navbarTitle = 'Dashboard';
        }
    }

    public function render()
    {
        return view('livewire.nav.topbar');
    }
}