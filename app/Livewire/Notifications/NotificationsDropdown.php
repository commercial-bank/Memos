<?php

namespace App\Livewire\Notifications;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class NotificationsDropdown extends Component
{
    use WithPagination;

    // Filtre actuel : 'all' (tous), 'unread' (non lus), 'read' (lus)
    public $filter = 'all';

    /**
     * Change le filtre de visualisation
     * Optimisation : Réinitialise la pagination pour éviter les résultats vides
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    /**
     * Marque une notification spécifique comme lue
     * Optimisation : On ne déclenche l'update que si nécessaire
     */
    public function markAsRead($notificationId)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($notificationId);
        
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }
    }

    /**
     * Supprime une notification
     */
    public function delete($notificationId)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($notificationId);
        $notification->delete();
        
        $this->dispatch('notify', message: "Notification supprimée.");
    }

    /**
     * Marque toutes les notifications non lues comme lues d'un coup
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->dispatch('notify', message: "Tout est marqué comme lu.");
    }
    
    /**
     * Nettoie l'historique en supprimant uniquement les notifications lues
     */
    public function deleteAllRead()
    {
        Auth::user()->readNotifications()->delete();
        $this->dispatch('notify', message: "Historique nettoyé.");
    }

    /**
     * Rendu du composant avec logique de filtrage et comptage optimisée
     */
    public function render()
    {
        $user = Auth::user();
        
        // Initialisation de la requête sur la relation de l'utilisateur
        $query = $user->notifications();

        // Application conditionnelle du filtre (Plus rapide que des clauses Where multiples)
        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        // Pagination : On limite à 10 pour garder un chargement DOM ultra-rapide
        $notifications = $query->latest()->paginate(10);

        /**
         * Optimisation des compteurs : 
         * On récupère les comptes en une seule phase de rendu pour les badges
         */
        $counts = [
            'all'    => $user->notifications()->count(),
            'unread' => $user->unreadNotifications()->count(),
            'read'   => $user->readNotifications()->count(),
        ];

        return view('livewire.notifications.notifications-dropdown', [
            'notifications' => $notifications,
            'counts'        => $counts
        ]);
    }
}