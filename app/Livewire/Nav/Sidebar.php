<?php

namespace App\Livewire\nav;
//use App\Models\User;

use Illuminate\Support\Facades\Auth; // Import indispensable
use Illuminate\Support\Facades\Session; // Import indispensable
use Livewire\Component;

class Sidebar extends Component
{
    //public User $user; // Déclare une propriété publique de type User
    public $activeTab = 'dashboard'; // Pour garder une trace de l'onglet actif
    public $isCollapsed = false; 

    /* Méthode de montage du composant
    public function mount(User $user)
    {
        $this->user = $user;
    }*/

   

    public function selectTab($tab)
    {
        if($tab == "logout")
        {

            Auth::guard('web')->logout();

            Session::invalidate();
            Session::regenerateToken();
            return redirect()->route('login');

        }
        else
        {
            $this->activeTab = $tab;
            // Nouvelle façon d'émettre un événement dans Livewire 3
            $this->dispatch('tabSelected', tab: $tab); // Notez le 'tab:' pour les arguments nommés
        }
        
    }


    public function toggleSidebar()
    {
        $this->isCollapsed = !$this->isCollapsed;
    }

    public function render()
    {
        return view('livewire.nav.sidebar');
    }
}