<?php

namespace App\Livewire\Favorites;

use App\Models\Memo;
use App\Models\Entity;
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

    // --- RECHERCHE ---
    public $search = '';

    // --- ÉTATS DES MODALS ---
    public $isOpen = false;
    public $isOpenHistory = false;

    // --- DONNÉES DU MÉMO SÉLECTIONNÉ ---
    public $memo_id = null;
    public $object = '';
    public $concern = '';
    public $content = '';
    public $date = '';
    public $user_entity_name = '';
    public $user_service = '';
    
    // --- DONNÉES HISTORIQUE ---
    public $memoHistory = [];

    /**
     * Réinitialise la pagination lors d'une nouvelle recherche
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Ouvre l'aperçu du mémo (Style Papier)
     * OPTIMISATION : Eager Loading de 'user.entity' pour éviter les requêtes N+1
     */
    public function viewMemo($id)
    {
        // On récupère tout en une seule requête SQL groupée
        $memo = Memo::with(['user.entity'])->findOrFail($id);
        
        $this->memo_id          = $memo->id;
        $this->object           = $memo->object;
        $this->concern          = $memo->concern;
        $this->content          = $memo->content;
        $this->date             = $memo->created_at->format('d/m/Y');
        $this->user_service     = $memo->user->service ?? 'Service';
        $this->user_entity_name = $memo->user->entity->name ?? 'Entité';

        $this->isOpen = true;
    }

    /**
     * Ferme le modal et nettoie les propriétés pour réduire la taille des échanges réseau
     */
    public function closeModal()
    {
        $this->isOpen = false;
        // On vide les variables lourdes pour alléger le prochain cycle Livewire
        $this->reset(['memo_id', 'object', 'concern', 'content', 'date', 'user_entity_name', 'user_service']);
    }

    /**
     * Récupère l'historique complet d'un mémo
     */
    public function viewHistory($id)
    {
        // Récupération optimisée avec conversion en array pour la rapidité d'affichage
        $this->memoHistory = Historiques::with('user')
            ->where('memo_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->isOpenHistory = true;
    }

    /**
     * Ferme le modal d'historique et libère la mémoire
     */
    public function closeHistoryModal()
    {
        $this->isOpenHistory = false;
        $this->memoHistory = [];
    }

    /**
     * Rendu de la vue avec filtrage optimisé
     */
    public function render()
    {
        $userId = Auth::id();

        // Construction de la requête optimisée
        $memos = Memo::query()
            ->with(['destinataires.entity', 'user.entity']) 
            // Jointure pour ne récupérer QUE les favoris de l'utilisateur (plus rapide qu'un WhereHas)
            ->join('favoris', 'memos.id', '=', 'favoris.memo_id')
            ->where('favoris.user_id', $userId)
            
            // Indique si le mémo est favori (toujours vrai ici, mais utile pour le trait ManageFavorites)
            ->withExists(['favoritedBy as is_favorited' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            
            // On sélectionne uniquement les colonnes du mémo pour éviter les collisions d'ID avec la table favoris
            ->select('memos.*') 
            
            // Recherche optimisée : ne s'exécute que si $search n'est pas vide
            ->when($this->search, function($query) {
                $searchTerm = '%' . $this->search . '%';
                $query->where(function($q) use ($searchTerm) {
                    $q->where('object', 'like', $searchTerm)
                      ->orWhere('concern', 'like', $searchTerm);
                });
            })
            
            // Tri par date d'ajout aux favoris (le plus récent en haut)
            ->orderBy('favoris.created_at', 'desc')
            ->paginate(10);

        return view('livewire.favorites.favorite-memos', [
            'memos' => $memos
        ]);
    }
}