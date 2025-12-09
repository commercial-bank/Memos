<?php

namespace App\Livewire\Notifications;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class NotificationsDropdown extends Component
{
    use WithPagination;

    public $filter = 'all'; // 'all', 'unread', 'read'

    // Permet de changer de filtre sans recharger la page
    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->resetPage(); // Revenir à la page 1 quand on change de filtre
    }

    public function markAsRead($notificationId)
    {
        $notification = Auth::user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();
        // Pas de dispatch ici, Livewire mettra à jour l'UI automatiquement
    }

    public function delete($notificationId)
    {
        $notification = Auth::user()->notifications()->findOrFail($notificationId);
        $notification->delete();
        $this->dispatch('notify', message: "Notification supprimée.");
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->dispatch('notify', message: "Tout est marqué comme lu.");
    }
    
    public function deleteAllRead()
    {
        // Supprime seulement les notifications déjà lues
        Auth::user()->readNotifications()->delete();
        $this->dispatch('notify', message: "Historique nettoyé.");
    }

    public function render()
    {
        $query = Auth::user()->notifications();

        // Application du filtre
        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        // Pagination (10 par page pour ne pas surcharger)
        $notifications = $query->paginate(10);

        // Compteurs pour les badges des onglets
        $counts = [
            'all' => Auth::user()->notifications()->count(),
            'unread' => Auth::user()->unreadNotifications()->count(),
            'read' => Auth::user()->readNotifications()->count(),
        ];

        return view('livewire.notifications.notifications-dropdown', [
            'notifications' => $notifications,
            'counts' => $counts
        ]);
    }
}