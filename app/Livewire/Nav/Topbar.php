<?php

namespace App\Livewire\Nav;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url; 
use Illuminate\Support\Facades\Auth;

class TopBar extends Component
{
    #[Url(as: 'view')] 
    public $currentContent = 'dashboard-content'; 
    
    public $navbarTitle = 'Dashboard';
    public $darkMode = false; // État du mode sombre

    public function mount()
    {
        $this->updateTitleBasedOnContent();
        // Récupérer l'état initial depuis la session
        $this->darkMode = session()->get('dark_mode', false);
    }

    // Cette fonction écoute le signal envoyé par la Sidebar
    #[On('dark-mode-toggled')]
    public function updateDarkMode($darkMode)
    {
        $this->darkMode = $darkMode;
    }

    public function selectTab($tab)
    {
        $this->tabSelected($tab);
    }

    #[On('tabSelected')]
    public function tabSelected($tab)
    {
        switch ($tab) {
            case 'dashboard': $this->currentContent = 'dashboard-content'; break;
            case 'memos': $this->currentContent = 'memos-content'; break;
            case 'profile': $this->currentContent = 'profile-content'; break;
            case 'settings': $this->currentContent = 'settings-content'; break;
            case 'notifications': $this->currentContent = 'notifications-content'; break;
            case 'documents': $this->currentContent = 'settings-documents'; break;
            default: $this->currentContent = 'dashboard-content';
        }

        $this->updateTitleBasedOnContent();
    }

    private function updateTitleBasedOnContent()
    {
        $titles = [
            'dashboard-content' => 'Dashboard',
            'memos-content' => 'Mémos',
            'settings-documents' => 'Mes documents',
            'profile-content' => 'Mon Profil',
            'settings-content' => 'Paramètres',
            'notifications-content' => 'Mes Notifications',
        ];

        $this->navbarTitle = $titles[$this->currentContent] ?? 'Dashboard';
    }

    public function forceGoToProfile()
    {
        $this->currentContent = 'profile-content';
        $this->navbarTitle = 'Mon Profil';
        $this->dispatch('tabSelected', tab: 'profile'); 
    }

    public function render()
    {
        return view('livewire.nav.topbar');
    }
}