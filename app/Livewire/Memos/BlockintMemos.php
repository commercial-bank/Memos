<?php

namespace App\Livewire\Memos;

use App\Models\Memo;
use App\Models\Entity;
use Livewire\Component;
use App\Models\References;
use App\Models\BlocEnregistrements;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BlockintMemos extends Component
{
    // Propriétés de contrôle d'état
    public $isOpen = false;
    public $selectedYear;
    public $search = ''; 

    // Propriétés pour le contenu du mémo (Modal)
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
     * Réinitialise la recherche quand l'année change pour éviter les résultats incohérents
     */
    public function updatedSelectedYear()
    {
        $this->reset('search');
    }

    /**
     * Visualisation d'un mémo
     * Optimisation : Chargement des relations imbriquées (user.entity) pour éviter les requêtes N+1
     */
    public function viewMemo($id) 
    {
        // On récupère le mémo avec l'utilisateur et son entité en une seule requête
        $memo = Memo::with(['user.entity'])->findOrFail($id);
        $this->fillMemoDataView($memo);
        $this->isOpen = true;
    }

    /**
     * Remplit les propriétés du composant pour l'affichage
     * Utilise les données déjà chargées pour gagner en rapidité
     */
    private function fillMemoDataView($memo) 
    {
        $this->memo_id = $memo->id;
        $this->object = $memo->object;
        $this->concern = $memo->concern;
        $this->content = $memo->content;
        $this->date = $memo->created_at->format('d/m/Y');
        
        // Accès direct via la relation déjà chargée (plus rapide que Entity::find)
        $this->user_entity_name = $memo->user->entity->name ?? 'Entité';
        $this->user_service = $memo->user->service;
    }

    /**
     * Ferme le modal et nettoie les propriétés pour réduire la charge utile Livewire
     */
    public function closeModal() 
    { 
        $this->isOpen = false; 
        $this->reset(['memo_id', 'object', 'concern', 'content', 'user_entity_name', 'user_service']);
    }

    /**
     * Génération et téléchargement du PDF
     * Optimisation : Gestion efficace des ressources (Images et QR Code)
     */
    public function downloadMemoPDF()
    {
        // Eager loading des relations nécessaires au PDF
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($this->memo_id);
        
        // Groupement des destinataires en mémoire
        $recipientsByAction = $memo->destinataires->groupBy('action');

        // 1. Préparation du Logo (Base64 pour éviter les problèmes d'accès fichiers dans le PDF)
        $pathLogo = public_path('images/logo.jpg');
        $logoBase64 = null;
        if (file_exists($pathLogo)) {
            $logoBase64 = 'data:image/jpg;base64,' . base64_encode(file_get_contents($pathLogo));
        }

        // 2. Génération du QR Code (Format PNG recommandé pour DomPDF)
        $qrCodeBase64 = null;
        if ($memo->qr_code) {
            $qrImage = QrCode::format('png')->size(100)->margin(1)->generate(route('memo.verify', $memo->qr_code));
            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrImage);
        }

        // 3. Initialisation du PDF
        $pdf = Pdf::loadView('pdf.memo-layout', [
            'memo' => $memo,
            'recipientsByAction' => $recipientsByAction,
            'logo' => $logoBase64,
            'qrCode' => $qrCodeBase64,
            'date' => $memo->created_at->format('d/m/Y'),
        ])->setPaper('a4', 'portrait');

        // 4. Stream du téléchargement pour libérer la mémoire serveur immédiatement
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Memo_' . $memo->id . '.pdf');
    }

    /**
     * Rendu de la vue avec filtrage optimisé
     */
    public function render()
    {
        // Construction de la requête avec eager loading du mémo
        $query = BlocEnregistrements::with('memo')
            ->where('nature_memo', 'Memo Entrant')
            ->where('user_id', Auth::id())
            ->whereYear('created_at', $this->selectedYear);

        // Application du filtre de recherche uniquement si une valeur est saisie
        // Utilisation de when() pour une structure de code plus fluide et performante
        $query->when($this->search, function($q) {
            $searchTerm = '%' . $this->search . '%';
            $q->where(function($sub) use ($searchTerm) {
                $sub->where('reference', 'like', $searchTerm)
                  ->orWhereHas('memo', function($memoQuery) use ($searchTerm) {
                      $memoQuery->where('object', 'like', $searchTerm)
                         ->orWhere('concern', 'like', $searchTerm)
                         ->orWhere('content', 'like', $searchTerm);
                  });
            });
        });

        // Récupération des données avec tri
        $references = $query->orderBy('created_at', 'desc')->get();
        
        return view('livewire.memos.blockint-memos', [
            'references' => $references
        ]);
    }
}