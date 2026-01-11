<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BrouillonExpireNotification extends Notification
{
    use Queueable;

    public $memoObject; // On stocke juste le titre car le mémo sera supprimé

    public function __construct($object)
    {
        $this->memoObject = $object;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        // On reprend exactement la structure de votre MemoActionNotification
        // pour ne pas casser l'affichage dans le dashboard
        return [
            'memo_id'    => null, // Null car l'ID n'existe plus en base
            'object'     => $this->memoObject,
            'message'    => "Brouillon expiré",
            'details'    => "Suppression automatique (Délai dépassé)",
            
            // Style Rouge (Danger/Suppression)
            'icon_bg'    => 'bg-red-100',
            'icon_color' => 'text-red-600',
            
            // Icône Poubelle (Trash)
            'icon_path'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>',
            
            'link'       => '#', // Pas de lien possible
        ];
    }
}