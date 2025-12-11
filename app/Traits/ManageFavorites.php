<?php

namespace App\Traits;

use App\Models\Memo;
use Illuminate\Support\Facades\Auth;

trait ManageFavorites
{
    public function toggleFavorite($memoId)
    {
        $user = Auth::user();
        
        // La méthode toggle() attache si inexistant, détache si existant
        // Elle retourne un tableau ['attached' => [], 'detached' => []]
        $result = $user->favorites()->toggle($memoId);

        $status = count($result['attached']) > 0 ? 'ajouté aux' : 'retiré des';
        
        // On notifie l'utilisateur (suppose que vous avez un listener pour 'notify')
        $this->dispatch('notify', message: "Mémo $status favoris.");
    }
}