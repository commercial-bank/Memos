<?php

namespace App\Livewire\Nav;

use Livewire\Component;
use Livewire\Attributes\Url; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Sidebar extends Component
{
    // Important : garde la trace de l'onglet dans l'URL
    #[Url(as: 'tab')] 
    public $activeTab = 'dashboard'; 

    public $isCollapsed = false; 

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