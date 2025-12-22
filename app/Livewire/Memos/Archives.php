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

    public $search = '';
    
    // Variables pour le Modal de visualisation
    public $isOpen = false;
    public $selectedMemo = null;
    public $myStatusInfo = null;

    /**
     * Ouvre le modal de visualisation et récupère les infos de statut spécifiques
     */
    public function viewMemo($id)
    {
        // On charge les relations nécessaires pour éviter les requêtes N+1
        $this->selectedMemo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($id);
        
        // On récupère la ligne de destinataire correspondant à l'entité de l'utilisateur connecté
        $this->myStatusInfo = $this->selectedMemo->destinataires
            ->where('entity_id', Auth::user()->entity_id)
            ->first();

        $this->isOpen = true;
    }

    /**
     * Ferme le modal et réinitialise les variables
     */
    public function closeModal()
    {
        $this->isOpen = false;
        $this->selectedMemo = null;
        $this->myStatusInfo = null;
    }

    /**
     * Génération et téléchargement du PDF
     */
    public function downloadPdf($id)
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($id);
        $recipientsByAction = $memo->destinataires->groupBy('action');

        // Préparation du logo
        $pathLogo = public_path('images/logo.jpg');
        $logoBase64 = file_exists($pathLogo) 
            ? 'data:image/jpg;base64,' . base64_encode(file_get_contents($pathLogo)) 
            : null;

        // Préparation du QR Code
        $qrCodeBase64 = null;
        if ($memo->qr_code) {
            $qrImage = QrCode::format('png')->size(150)->generate(route('memo.verify', $memo->qr_code));
            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrImage);
        }

        $pdf = Pdf::loadView('pdf.memo-layout', [
            'memo' => $memo, 
            'recipientsByAction' => $recipientsByAction,
            'logo' => $logoBase64, 
            'qrCode' => $qrCodeBase64, 
            'date' => $memo->created_at->format('d/m/Y'),
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn() => print($pdf->output()), 
            "Archive_Memo_{$memo->reference}.pdf"
        );
    }

    public function render()
    {
        $user = Auth::user();

        $archives = Memo::query()
            ->with(['user.entity', 'destinataires.entity'])
            // 1. L'utilisateur est actuellement détenteur du mémo (il est dans la liste JSON)
            ->whereJsonContains('current_holders', $user->id)
            // 2. MAIS le destinataire lié à son entité a fini son traitement
            ->whereHas('destinataires', function($q) use ($user) {
                $q->where('entity_id', $user->entity_id)
                  ->whereIn('processing_status', ['traiter', 'decision_prise', 'repondu']);
            })
            // 3. Logique de recherche (recherche sur objet, référence ou concerne)
            ->where(function($q) {
                $q->where('object', 'like', '%'.$this->search.'%')
                  ->orWhere('reference', 'like', '%'.$this->search.'%')
                  ->orWhere('concern', 'like', '%'.$this->search.'%');
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('livewire.memos.archives', [
            'archives' => $archives
        ]);
    }
}