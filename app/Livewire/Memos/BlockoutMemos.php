<?php

namespace App\Livewire\Memos;

use App\Models\Memo;
use App\Models\User;
use App\Models\Entity;
use Livewire\Component;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\BlocEnregistrements; 
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BlockoutMemos extends Component
{
    // Propriétés d'état
    public $isOpen = false;
    public $selectedYear;
    public $search = ''; 
    public $isViewingPdf = false;

    // Propriétés du formulaire/modal
    public $memo_id;
    public $object = '';
    public $content = '';
    public $concern = '';
    public $date = '';
    public $user_entity_name = '';
    public $user_service = '';

    // --- Données d'Affichage ---

    public $user_first_name;
    public $user_last_name;

    public $pdfBase64 = '';

    /**
     * Initialisation du composant
     */
    public function mount()
    {
        $this->selectedYear = date('Y');
    }

    /**
     * Réinitialise la recherche si l'année change
     */
    public function updatedSelectedYear()
    {
        $this->reset('search');
    }

    /**
     * Visualisation du mémo
     * OPTIMISATION : Eager loading de 'user.entity' pour éviter des requêtes SQL en boucle
     */
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

    public function closePdfView()
    {
        $this->isViewingPdf = false;
        $this->pdfBase64 = '';
    }

    private function getLogoBase64() {
        $path = public_path('images/logo.jpg');
        return file_exists($path) ? 'data:image/jpg;base64,' . base64_encode(file_get_contents($path)) : null;
    }

    /**
     * Remplit les données pour le modal
     * OPTIMISATION : Utilisation des relations déjà chargées au lieu de refaire un Entity::find()
     */
    private function fillMemoDataView($memo) 
    {
        $this->memo_id = $memo->id;
        $this->object = $memo->object;
        $this->concern = $memo->concern;
        $this->content = $memo->content;
        $this->date = $memo->created_at->format('d/m/Y');
        
        // Performance : On récupère le nom directement depuis la relation chargée
        $this->user_entity_name = $memo->user->entity->name ?? 'Entité';
        $this->user_service = $memo->user->service;
    }

    /**
     * Ferme le modal et nettoie la mémoire
     * OPTIMISATION : reset() permet d'alléger le poids de la page Livewire
     */
    public function closeModal() 
    { 
        $this->isOpen = false; 
        $this->reset(['memo_id', 'object', 'concern', 'content', 'user_entity_name', 'user_service']);
    }

    /**
     * Génération du PDF
     * OPTIMISATION : Stream de la réponse pour réduire la consommation de RAM serveur
     */
     public function downloadMemoPDF()
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($this->memo_id);
        $pdf = Pdf::loadView('pdf.memo-layout', $this->getPdfData($memo));
        
        return response()->streamDownload(fn() => print($pdf->output()), "Memo_{$memo->id}.pdf");
    }

    /**
     * Rendu de la liste avec filtrage
     */
    public function render()
    {
        // Initialisation de la requête avec eager loading du mémo lié
        $query = BlocEnregistrements::with('memo')
            ->where('nature_memo', 'Memo Sortant')
            ->where('user_id', Auth::id())
            ->whereYear('created_at', $this->selectedYear);

        // OPTIMISATION : Utilisation de when() pour éviter des blocs conditionnels lourds
        $query->when($this->search, function($q) {
            $term = '%' . $this->search . '%';
            $q->where(function($sub) use ($term) {
                $sub->where('reference', 'like', $term)
                  ->orWhereHas('memo', function($memoQuery) use ($term) {
                      $memoQuery->where('object', 'like', $term)
                         ->orWhere('concern', 'like', $term)
                         ->orWhere('content', 'like', $term);
                  });
            });
        });

        // Exécution de la requête avec tri par date décroissante
        $references = $query->orderBy('created_at', 'desc')->get();
        
        return view('livewire.memos.blockout-memos', [
            'references' => $references
        ]);
    }
}