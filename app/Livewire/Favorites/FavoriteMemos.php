<?php

namespace App\Livewire\Favorites;

use App\Models\Memo;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\ManageFavorites; // On utilise le Trait créé
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;

#[Title('Mes Favoris')]
class FavoriteMemos extends Component
{
    use WithPagination;
    use ManageFavorites; // Importation de la logique toggleFavorite

    public $search = '';

    // Réinitialise la pagination quand on cherche
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        // On récupère les mémos via la relation favorites() définie dans le User
        $memos = $user->favorites()
            ->with(['user', 'destinataires.entity']) // Eager loading pour éviter les requêtes N+1
            ->where(function($query) {
                $query->where('object', 'like', '%'.$this->search.'%')
                      ->orWhere('concern', 'like', '%'.$this->search.'%')
                      ->orWhere('reference', 'like', '%'.$this->search.'%');
            })
            ->orderBy('favoris.created_at', 'desc') // On trie par date d'ajout en favoris
            ->paginate(10);

        return view('livewire.favorites.favorite-memos', [
            'memos' => $memos
        ]);
    }
    
    // --- Méthodes pour l'aperçu (similaires à Incoming2Memos) ---
    public $isOpen = false;
    public $memoView = null;
    
    public function viewMemo($id)
    {
        $this->memoView = Memo::with('user.entity')->find($id);
        $this->isOpen = true;
    }
    
    public function closeModal()
    {
        $this->isOpen = false;
        $this->memoView = null;
    }
}