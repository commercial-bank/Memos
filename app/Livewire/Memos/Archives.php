<?php

namespace App\Livewire\Memos;

use App\Models\Memo;
use App\Models\Destinataires;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

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

    /**
     * Ouvre le modal de visualisation et récupère les infos de statut spécifiques
     * Optimisation : Chargement des relations (Eager Loading) pour éviter le problème N+1
     */
    public function viewMemo($id)
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($id);
        $this->memo_id = $memo->id;

        $pdf = Pdf::loadView('pdf.memo-layout', [
            'memo'               => $memo,
            'recipientsByAction' => $memo->destinataires->groupBy('action'),
            'date'               => $memo->created_at->format('d/m/Y'),
            'logo'               => $this->getLogoBase64(),
        ])->setPaper('a4', 'portrait');

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
        $memo = Memo::findOrFail($this->memo_id);
        $pdf = Pdf::loadView('pdf.memo-layout', [
            'memo' => $memo,
            'recipientsByAction' => $memo->destinataires->groupBy('action'),
            'date' => $memo->created_at->format('d/m/Y'),
            'logo' => $this->getLogoBase64(),
        ]);
        return response()->streamDownload(fn() => print($pdf->output()), "Memo_{$memo->id}.pdf");
    }

    /**
     * Rendu de la vue avec filtrage optimisé
     */
    public function render()
    {
        $user = Auth::user();

        // Construction de la requête optimisée
        $archives = Memo::query()
            ->with(['user.entity', 'destinataires.entity']) // Eager loading constant
            
            // 1. Filtrage sur les détenteurs (Utilisation de l'index JSON si disponible en DB)
            ->whereJsonContains('current_holders', $user->id)
            
            // 2. Filtrage sur le statut du destinataire lié à l'entité de l'utilisateur
            ->whereHas('destinataires', function($q) use ($user) {
                $q->where('entity_id', $user->entity_id)
                  ->whereIn('processing_status', ['traiter', 'decision_prise', 'repondu']);
            })
            
            // 3. Logique de recherche optimisée : n'exécute le LIKE que si nécessaire
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $searchTerm = '%' . $this->search . '%';
                    $q->where('object', 'like', $searchTerm)
                      ->orWhere('reference', 'like', $searchTerm)
                      ->orWhere('concern', 'like', $searchTerm);
                });
            })
            
            // Tri par date de mise à jour (le plus récent en premier)
            ->orderBy('updated_at', 'desc')
            
            // Pagination (10 éléments par page pour garder un DOM léger)
            ->paginate(10);

        return view('livewire.memos.archives', [
            'archives' => $archives
        ]);
    }
}