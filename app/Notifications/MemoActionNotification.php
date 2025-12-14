<?php

namespace App\Notifications;

use App\Models\Memo;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MemoActionNotification extends Notification
{
    use Queueable;

    public $memo;
    public $type; // 'sent', 'rejected', 'registered', 'validated'
    public $actor; // L'utilisateur qui a fait l'action

    public function __construct(Memo $memo, string $type, User $actor)
    {
        $this->memo = $memo;
        $this->type = $type;
        $this->actor = $actor;
    }

    public function via(object $notifiable): array
    {
        return ['database']; // On active seulement la base de données pour l'instant
    }

    public function toArray(object $notifiable): array
    {
        // Configuration visuelle selon le type
        switch ($this->type) {
            case 'envoyer':
                $message = "Nouveau mémo reçu";
                $iconBg = 'bg-blue-100';
                $iconColor = 'text-blue-600';
                // Icone "Download/Inbox"
                $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>';
                break;

            case 'rejeter':
                $message = "Mémo rejeté";
                $iconBg = 'bg-red-100';
                $iconColor = 'text-red-600';
                // Icone "X"
                $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                break;

            case 'transmis':
                $message = "Mémo enregistré et transmit aux destinataires";
                $iconBg = 'bg-green-100';
                $iconColor = 'text-green-600';
                // Icone "Check"
                $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                break;
            
            case 'cotation':
                $message = "Nouvelle cotation / Instruction";
                $iconBg = 'bg-purple-100';
                $iconColor = 'text-purple-600';
                // Icone "Crayon/Édition" pour symboliser l'annotation
                $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>';
                break;
                
            default:
                $message = "Notification";
                $iconBg = 'bg-gray-100';
                $iconColor = 'text-gray-600';
                $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
        }

        return [
            'memo_id' => $this->memo->id,
            'object' => $this->memo->object,
            'message' => $message,
            'details' => "De ou Par: " . $this->actor->first_name . " " . $this->actor->last_name,
            'icon_bg' => $iconBg,
            'icon_color' => $iconColor,
            'icon_path' => $iconPath,
            'link' => '#', // Adapte cette route selon ton projet
        ];
    }
    
}