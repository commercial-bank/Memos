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

    // Propriétés de recherche et état du modal
    public $search = '';
    public $isViewingPdf = false;
    public $isOpen = false;

     // --- Données d'Affichage ---
    public $user_service;
    public $user_first_name;
    public $user_last_name;
    public $user_entity_name;
    public $pdfBase64 = '';
    public $memo_id = null;

    // Note : Stocker des modèles entiers dans des propriétés publiques 
    // peut ralentir Livewire (sérialisation). 
    // Pour l'optimisation, on garde ces variables mais on s'assure qu'elles sont légères.
    public $selectedMemo = null;
    public $myStatusInfo = null;

    /**
     * Réinitialise la pagination lors d'une recherche
     * Améliore l'expérience utilisateur et évite des requêtes inutiles sur des pages inexistantes
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
     * Ferme le modal et réinitialise les variables pour libérer de la mémoire
     */
    public function closeModal()
    {
        $this->isOpen = false;
        $this->selectedMemo = null;
        $this->myStatusInfo = null;
    }

    /**
     * Génération et téléchargement du PDF
     * Optimisation : Traitement du logo et du QR Code
     */
    public function downloadMemoPDF()
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($this->memo_id);
        $pdf = Pdf::loadView('pdf.memo-layout', $this->getPdfData($memo));
        
        return response()->streamDownload(fn() => print($pdf->output()), "Memo_{$memo->id}.pdf");
    }

    /**
     * Rendu de la vue avec filtrage optimisé
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
            
            // Recherche (inchangée)
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