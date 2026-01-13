<?php

namespace App\Livewire\Nav;

use Livewire\Component;
use Livewire\Attributes\Url; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Sidebar extends Component
{
    #[Url(as: 'tab')] 
    public $activeTab = 'dashboard'; 

    public $isCollapsed = false; 
    public $darkMode = false;

    public function mount()
    {
        // Récupérer l'état du mode sombre depuis la session au chargement
       $this->darkMode = Auth::user()->dark_mode ?? false;
    }

    public function toggleDarkMode()
    {
        $this->darkMode = !$this->darkMode;
        
        // CORRECTION : On sauvegarde en BDD pour que ça reste après déconnexion
        $user = Auth::user();
        if ($user) {
            $user->update(['dark_mode' => $this->darkMode]);
        }
        
        // On notifie les autres composants (Dashboard)
        $this->dispatch('dark-mode-toggled', darkMode: $this->darkMode);
    }

    public function selectTab($tab)
    {
        if($tab == "logout") {
            Auth::guard('web')->logout();
            Session::invalidate();
            Session::regenerateToken();
            return redirect()->route('login');
        }

        $this->activeTab = $tab;
        $this->dispatch('tabSelected', tab: $tab); 
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