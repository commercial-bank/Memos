<?php

namespace App\Livewire\Memos;

use App\Models\BlocEnregistrements; 
use App\Models\Memo;
use App\Models\Entity;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BlockoutMemos extends Component
{
    // Propriétés d'état
    public $isOpen = false;
    public $selectedYear;
    public $search = ''; 

    // Propriétés du formulaire/modal
    public $memo_id;
    public $object = '';
    public $content = '';
    public $concern = '';
    public $date = '';
    public $user_entity_name = '';
    public $user_service = '';

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
    public function viewMemo($id) 
    {
        $memo = Memo::with(['user.entity'])->findOrFail($id);
        $this->fillMemoDataView($memo);
        $this->isOpen = true;
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
        // Chargement des relations nécessaires au template PDF
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($this->memo_id);
        
        // Groupement efficace des destinataires
        $recipientsByAction = $memo->destinataires->groupBy('action');

        // 1. Gestion du Logo en Base64
        $pathLogo = public_path('images/logo.jpg');
        $logoBase64 = null;
        if (file_exists($pathLogo)) {
            $logoBase64 = 'data:image/jpg;base64,' . base64_encode(file_get_contents($pathLogo));
        }

        // 2. Génération du QR Code (Optimisé en format PNG pour DomPDF)
        $qrCodeBase64 = null;
        if ($memo->qr_code) {
            $qrImage = QrCode::format('png')->size(100)->margin(1)->generate(route('memo.verify', $memo->qr_code));
            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrImage);
        }

        // 3. Préparation du PDF
        $pdf = Pdf::loadView('pdf.memo-layout', [
            'memo' => $memo,
            'recipientsByAction' => $recipientsByAction,
            'logo' => $logoBase64,
            'qrCode' => $qrCodeBase64,
            'date' => $memo->created_at->format('d/m/Y'),
        ])->setPaper('a4', 'portrait');

        // Retourne le fichier en téléchargement direct (plus rapide que le stockage temporaire)
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Memo_' . $memo->id . '.pdf');
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