<?php

namespace App\Livewire\Favorites;

use App\Models\Memo;
use App\Models\User;
use App\Models\Entity;
use Livewire\Component;
use App\Models\Historiques;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Traits\ManageFavorites;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

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
    public $isViewingPdf = false;
    public $isEditing = false;
    public $pdfBase64 = '';
    
    // --- DONNÉES HISTORIQUE ---
    public $memoHistory = [];

    /**
     * Réinitialise la pagination lors d'une nouvelle recherche
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    private function getPdfData($memo)
    {
        // On cherche le directeur de l'entité du créateur du mémo
        $director = User::where('dir_id', $memo->user->dir_id)
                        ->where('poste', 'Directeur')
                        ->first();

        return [
            'memo'               => $memo,
            'recipientsByAction' => $memo->destinataires->groupBy('action'),
            'date'               => $memo->created_at->format('d/m/Y'),
            'logo'               => $this->getLogoBase64(),
            'director'           => $director, // On passe l'objet director à la vue
        ];
    }

    /**
     * Ouvre l'aperçu du mémo
     */
    public function viewMemo($id)
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($id);
        $this->memo_id = $memo->id;

        // Utilisation de la méthode partagée
        $pdf = Pdf::loadView('pdf.memo-layout', $this->getPdfData($memo))
              ->setPaper('a4', 'portrait');

        $this->pdfBase64 = base64_encode($pdf->output());
        $this->isViewingPdf = true;
        $this->isEditing = false;
    }

    // Dans FavoriteMemos.php

    public function useAsModel($id)
    {
        // On émet l'événement vers le haut (vers Memos.php)
        // 'load-model-data' est le nom écouté par #[On(...)] dans le parent
        $this->dispatch('load-model-data', memoId: $id);
    }

    public function closePdfView()
    {
        $this->isViewingPdf = false;
        $this->pdfBase64 = '';
    }

    private function getLogoBase64() {
        $path = public_path('images/logo.jpg');
        return file_exists($path) ? 'data:image/jpg;base64,' . base64_encode(file_get_contents($path)) : null;
    }

    public function downloadMemoPDF()
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($this->memo_id);
        $pdf = Pdf::loadView('pdf.memo-layout', $this->getPdfData($memo));
        
        return response()->streamDownload(fn() => print($pdf->output()), "Memo_{$memo->id}.pdf");
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