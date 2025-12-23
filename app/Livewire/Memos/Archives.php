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
    public $isOpen = false;

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
        // On récupère le mémo avec ses relations en une seule requête SQL
        $this->selectedMemo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($id);
        
        // Filtrage en mémoire (Collection) au lieu d'une nouvelle requête SQL
        $this->myStatusInfo = $this->selectedMemo->destinataires
            ->where('entity_id', Auth::user()->entity_id)
            ->first();

        $this->isOpen = true;
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
    public function downloadPdf($id)
    {
        // Chargement optimisé pour le PDF
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($id);
        
        // Groupement des destinataires par action pour la vue PDF
        $recipientsByAction = $memo->destinataires->groupBy('action');

        // Préparation du logo (mise en cache statique possible si le logo est lourd)
        $pathLogo = public_path('images/logo.jpg');
        $logoBase64 = null;
        if (file_exists($pathLogo)) {
            $logoBase64 = 'data:image/jpg;base64,' . base64_encode(file_get_contents($pathLogo));
        }

        // Génération rapide du QR Code
        $qrCodeBase64 = null;
        if ($memo->qr_code) {
            $qrImage = QrCode::format('png')
                ->size(150)
                ->margin(1)
                ->generate(route('memo.verify', $memo->qr_code));
            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrImage);
        }

        // Création du flux PDF
        $pdf = Pdf::loadView('pdf.memo-layout', [
            'memo'               => $memo, 
            'recipientsByAction' => $recipientsByAction,
            'logo'               => $logoBase64, 
            'qrCode'             => $qrCodeBase64, 
            'date'               => $memo->created_at->format('d/m/Y'),
        ])->setPaper('a4', 'portrait');

        // Utilisation de streamDownload pour réduire la consommation de mémoire serveur
        return response()->streamDownload(
            fn() => print($pdf->output()), 
            "Archive_Memo_{$memo->reference}.pdf"
        );
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