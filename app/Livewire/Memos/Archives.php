<?php

namespace App\Livewire\Memos;

use Carbon\Carbon;
use App\Models\Memo;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Destinataires;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Archives extends Component
{
    use WithPagination;

    // =================================================================================================
    // 1. PROPRIÉTÉS D'ÉTAT DE L'INTERFACE (UI) & RECHERCHE
    // =================================================================================================

    public $search = '';
    public $isOpen = false;
    public $isViewingPdf = false;


    // =================================================================================================
    // 2. PROPRIÉTÉS DE DONNÉES DU MÉMO (AFFICHAGE)
    // =================================================================================================

    public $memo_id = null;

    // --- Données d'Affichage Utilisateur ---
    public $user_service;
    public $user_first_name;
    public $user_last_name;
    public $user_entity_name;

    // --- Contenu PDF ---
    public $pdfBase64 = '';

    // --- Modèles & Objets ---
    // Note : Stocker des modèles entiers dans des propriétés publiques 
    // peut ralentir Livewire (sérialisation).
    public $selectedMemo = null;
    public $myStatusInfo = null;


    // =================================================================================================
    // 3. NAVIGATION & ÉVÉNEMENTS
    // =================================================================================================

    /**
     * Réinitialise la pagination lors d'une recherche
     * Améliore l'expérience utilisateur et évite des requêtes inutiles sur des pages inexistantes
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }


    // =================================================================================================
    // 4. LOGIQUE D'AFFICHAGE & PDF
    // =================================================================================================

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
        $this->isEditing = false; // Sécurité pour s'assurer qu'on n'est pas en mode édition
    }

    public function closePdfView()
    {
        $this->isViewingPdf = false;
        $this->pdfBase64 = '';
    }

    /**
     * Génération et téléchargement du PDF
     */
    public function downloadMemoPDF()
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($this->memo_id);
        $pdf = Pdf::loadView('pdf.memo-layout', $this->getPdfData($memo));
        
        return response()->streamDownload(fn() => print($pdf->output()), "Memo_{$memo->id}.pdf");
    }

    /**
     * Prépare les données nécessaires à la vue PDF
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


    // =================================================================================================
    // 5. HELPER & GESTION DES MODALS
    // =================================================================================================

    private function getLogoBase64() {
        $path = public_path('images/logo.jpg');
        return file_exists($path) ? 'data:image/jpg;base64,' . base64_encode(file_get_contents($path)) : null;
    }

    /**
     * Ferme le modal et réinitialise les variables pour libérer de la mémoire
     */
    public function closeModal()
    {
        $this->isOpen = false;
        $this->selectedMemo = null;
        $this->myStatusInfo = null;
    }


    // =================================================================================================
    // 6. RENDU FINAL
    // =================================================================================================

    /**
     * Rendu de la vue avec filtrage optimisé pour les archives
     */
    public function render()
    {
        $user = Auth::user();

        $archives = Memo::query()
            // Chargement des relations pour l'affichage
            ->with(['user.entity', 'destinataires.entity']) 
            
            // CONDITION 1 : L'utilisateur a eu le mémo en main (est dans current_holders)
            ->whereJsonContains('current_holders', $user->id)
            
            // CONDITION 2 : Le circuit est totalement terminé
            ->where('workflow_direction', 'terminer')
            
            // Recherche
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $searchTerm = '%' . $this->search . '%';
                    $q->where('object', 'like', $searchTerm)
                      ->orWhere('reference', 'like', $searchTerm)
                      ->orWhere('concern', 'like', $searchTerm);
                });
            })
            
            // Tri par le plus récent
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('livewire.memos.archives', [
            'archives' => $archives
        ]);
    }
}