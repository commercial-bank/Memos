<?php

namespace App\Livewire\Nav;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url; 
use Illuminate\Support\Facades\Auth;

class TopBar extends Component
{
    // Important : garde le contenu affiché dans l'URL
    #[Url(as: 'view')] 
    public $currentContent = 'dashboard-content'; 
    
    public $navbarTitle = 'Dashboard';

    public function mount()
    {
        $this->updateTitleBasedOnContent();
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
            case 'courriers': $this->currentContent = 'courriers-content'; break;
            case 'projects': $this->currentContent = 'projects-content'; break;
            case 'groups': $this->currentContent = 'groups-content'; break;
            case 'reports': $this->currentContent = 'reports-content'; break;
            case 'profile': $this->currentContent = 'profile-content'; break;
            case 'settings': $this->currentContent = 'settings-content'; break;
            case 'notifications': $this->currentContent = 'notifications-content'; break;
            case 'tasks': $this->currentContent = 'settings-tasks'; break;
            case 'calendar': $this->currentContent = 'settings-calendar'; break;
            case 'documents': $this->currentContent = 'settings-documents'; break;
            case 'reports': $this->currentContent = 'settings-reports'; break;
            default: $this->currentContent = 'dashboard-content';
        }

        $this->updateTitleBasedOnContent();
    }

    private function updateTitleBasedOnContent()
    {
        $titles = [
            'dashboard-content' => 'Dashboard',
            'memos-content' => 'Mémos',
            'courriers-content' => 'Courriers',
            'projects-content' => 'Projets',
            'groups-content' => 'Groupes',
            'reports-content' => 'Rapports',
            'profile-content' => 'Mon Profil',
            'settings-content' => 'Paramètres',
            'notifications-content' => 'Mes Notifications',
        ];

        $this->navbarTitle = $titles[$this->currentContent] ?? 'Dashboard';
    }
    
    // ... Garde tes méthodes de notifications (markAsRead) ici ...
    public function markAllNotificationsAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
    }
    
    public function markNotificationAsRead($notificationId)
    {
        $notification = Auth::user()->notifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
            if (!empty($notification->data['link']) && $notification->data['link'] !== '#') {
                return redirect($notification->data['link']);
            }
        }
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