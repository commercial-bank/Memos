<?php

namespace App\Livewire\Favorites;

use App\Models\Memo;
use App\Models\Entity; // N'oubliez pas d'importer Entity
use App\Models\Historiques;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\ManageFavorites;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;

#[Title('Mes Favoris')]
class FavoriteMemos extends Component
{
    use WithPagination;
    use ManageFavorites;

    public $search = '';

    // --- VARIABLES POUR LE MODAL APERÇU (STYLE PAPIER) ---
    public $isOpen = false;
    public $memo_id = null; // Important pour le @php dans la vue
    public $object = '';
    public $concern = '';
    public $content = '';
    public $date = '';
    public $user_entity_name = '';
    public $user_service = '';
    
    // --- VARIABLES HISTORIQUE ---
    public $isOpenHistory = false;
    public $memoHistory = [];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    // 1. MÉTHODE VIEW MEMO MISE À JOUR
    public function viewMemo($id)
    {
        $memo = Memo::with('user')->findOrFail($id);
        
        // On remplit les variables pour le modal "Papier"
        $this->memo_id = $memo->id;
        $this->object = $memo->object;
        $this->concern = $memo->concern;
        $this->content = $memo->content;
        $this->date = $memo->created_at->format('d/m/Y');
        $this->user_service = $memo->user->service ?? 'Service';

        // Récupération du nom de l'entité
        $entity = Entity::find($memo->user->entity_id);
        $this->user_entity_name = $entity->name ?? 'Entité';

        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['memo_id', 'object', 'concern', 'content', 'date', 'user_entity_name', 'user_service']);
    }

    // 2. MÉTHODES HISTORIQUE
    public function viewHistory($id)
    {
        $this->memoHistory = Historiques::with('user')
            ->where('memo_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->isOpenHistory = true;
    }

    public function closeHistoryModal()
    {
        $this->isOpenHistory = false;
        $this->memoHistory = [];
    }

    public function render()
    {
        $userId = Auth::id();

        $memos = Memo::with(['destinataires.entity']) // On retire la query favoris ici car on utilise le trait différemment ou la relation user
             // Optimisation : on passe par la relation User->favorites() si définie, sinon la logique manuelle
            ->join('favoris', 'memos.id', '=', 'favoris.memo_id')
            ->where('favoris.user_id', $userId)
            
            // On ajoute l'attribut pour l'étoile jaune (toujours true ici car on est dans les favoris)
            ->withExists(['favoritedBy as is_favorited' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            
            ->select('memos.*') // Important pour éviter les conflits d'ID avec la table pivot
            
            ->where(function($query) {
                $query->where('object', 'like', '%'.$this->search.'%')
                      ->orWhere('concern', 'like', '%'.$this->search.'%');
            })
            ->orderBy('favoris.created_at', 'desc')
            ->paginate(10);

        return view('livewire.favorites.favorite-memos', [
            'memos' => $memos
        ]);
    }
}