<?php

namespace App\Traits;

use App\Models\Memo;
use Illuminate\Support\Facades\Auth;

trait ManageFavorites
{
    public function toggleFavorite($memoId)
    {
        $user = Auth::user();
        
        // La méthode toggle() ajoute si ça n'existe pas, et supprime si ça existe.
        $changes = $user->favorites()->toggle($memoId);

        $status = count($changes['attached']) > 0 ? 'ajouté aux' : 'retiré des';
        
        // Notification (adaptez selon votre système de notification)
        $this->dispatch('notify', message: "Mémo $status favoris.");
    }
}