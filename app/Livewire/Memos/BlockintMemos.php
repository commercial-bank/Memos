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
    public $isOpen = false;
    public $selectedYear;
    
    // 1. AJOUT DE LA VARIABLE DE RECHERCHE
    public $search = ''; 

    // ... (Vos autres variables pour le modal restent ici : object, content, etc.) ...
    public $memo_id;
    public $object = '';
    public $content = '';
    public $concern = '';
    public $date = '';
    public $user_entity_name = '';
    public $user_service = ''; // N'oubliez pas cette variable si elle manquait

    public function mount()
    {
        $this->selectedYear = date('Y');
    }

    // Réinitialiser la pagination ou la recherche quand l'année change (optionnel mais recommandé)
    public function updatedSelectedYear()
    {
        $this->reset('search');
    }

    public function viewMemo($id) {
        $memo = Memo::with('user')->findOrFail($id);
        $this->fillMemoDataView($memo);
        $this->isOpen = true;
    }

    private function fillMemoDataView($memo) {
        // Logique simplifiée pour l'aperçu lecture seule
        $this->memo_id = $memo->id;
        $this->object = $memo->object;
        $this->concern = $memo->concern;
        $this->content = $memo->content;
        $this->date = $memo->created_at->format('d/m/Y');
        $entity = Entity::find($memo->user->entity_id);
        $this->user_entity_name = $entity->name ?? 'Entité';
        $this->user_service = $memo->user->service;
    }

    public function closeModal() 
    { 
        $this->isOpen = false; 
    }

    // =================================================================
    // NOUVELLE FONCTION : GÉNÉRATION PDF
    // =================================================================
    public function downloadMemoPDF()
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($this->memo_id);
        
        // Groupement des destinataires
        $recipientsByAction = $memo->destinataires->groupBy('action');

        // 1. Image Logo en Base64
        $pathLogo = public_path('images/logo.jpg');
        $logoBase64 = file_exists($pathLogo) 
            ? 'data:image/jpg;base64,' . base64_encode(file_get_contents($pathLogo)) 
            : null;

        // 2. QR Code en Base64
        $qrCodeBase64 = null;
        if ($memo->qr_code) {
            $qrImage = QrCode::format('svg')->size(100)->generate(route('memo.verify', $memo->qr_code));
            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrImage);
        }

        // 3. Génération du PDF
        $pdf = Pdf::loadView('pdf.memo-layout', [
            'memo' => $memo,
            'recipientsByAction' => $recipientsByAction,
            'logo' => $logoBase64,
            'qrCode' => $qrCodeBase64,
            'date' => $memo->created_at->format('d/m/Y'),
        ]);

        // Configuration A4 Portrait
        $pdf->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Memo_' . $memo->id . '.pdf');
    }

    
    
    public function render()
    {
     
       // 2. MODIFICATION DE LA REQUÊTE
        $query = BlocEnregistrements::with('memo')
            ->where('nature_memo', 'Memo Entrant')
            ->where('user_id', Auth::id())
            ->whereYear('created_at', $this->selectedYear);

        // Application du filtre de recherche si $search n'est pas vide
        if (!empty($this->search)) {
            $query->where(function($q) {
                // Recherche dans la référence (table blocs_enregistrements)
                $q->where('reference', 'like', '%' . $this->search . '%')
                  // OU recherche dans la table liée memos (objet, concern, content)
                  ->orWhereHas('memo', function($q2) {
                      $q2->where('object', 'like', '%' . $this->search . '%')
                         ->orWhere('concern', 'like', '%' . $this->search . '%')
                         ->orWhere('content', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $references = $query->orderBy('created_at', 'desc')->get();
        
        return view('livewire.memos.blockint-memos', [
        'references' => $references
        ]);
    }
}
