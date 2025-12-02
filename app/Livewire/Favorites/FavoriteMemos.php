<?php

namespace App\Livewire\Favorites;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

class FavoriteMemos extends Component
{
    use WithPagination;

    #[Title('Mes Favoris')] 
    public function render()
    {
        // On récupère les mémos favoris de l'utilisateur connecté
        $favorites = Auth::user()->favorites()
            ->with(['destinataires', 'user']) // Eager loading pour éviter les requêtes N+1
            ->latest('favorites.created_at') // On trie par date d'ajout aux favoris
            ->paginate(9); // Pagination par 9 (grille 3x3)

        return view('livewire.favorites.favorite-memos', [
            'favorites' => $favorites
        ]);
    }

    // On garde la fonction pour pouvoir retirer un favori directement depuis cette liste
    public function toggleFavorite($documentId)
    {
        Auth::user()->favorites()->toggle($documentId);
        
        // Pas besoin de recharger, Livewire va rafraîchir la liste 
        // et l'élément disparaîtra car il ne fait plus partie de la requête.
    }

   
}
