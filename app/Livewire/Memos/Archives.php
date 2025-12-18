<?php

namespace App\Livewire\Memos;

use App\Models\Memo;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Archives extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFilter = '';
    
    // Variables pour le Modal de visualisation
    public $isOpen = false;
    public $selectedMemo = null;

    public function viewMemo($id)
    {
        $this->selectedMemo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($id);
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->selectedMemo = null;
    }

    // Téléchargement PDF (Similaire aux autres)
    public function downloadPdf($id)
    {
        // ... (Reprendre votre logique de téléchargement PDF ici)
        // C'est important pour les archives d'avoir le bouton download
    }

    public function render()
    {
        $user = Auth::user();

        $archives = Memo::query()
            ->with(['user', 'destinataires'])
            // Critères d'archivage : Direction 'terminer' OU status 'archive'
            ->where(function($q) {
                $q->where('workflow_direction', 'terminer')
                  ->orWhere('status', 'archive');
            })
            // Filtre : L'utilisateur doit avoir été impliqué (Expéditeur ou Destinataire/Détenteur passé)
            ->where(function($q) use ($user) {
                $q->where('user_id', $user->id) // J'ai créé le mémo
                  ->orWhereJsonContains('previous_holders', $user->id) // Je l'ai eu entre les mains
                  ->orWhereJsonContains('current_holders', $user->id); // (Rare en archive mais possible)
            })
            // Recherche
            ->where(function($q) {
                $q->where('object', 'like', '%'.$this->search.'%')
                  ->orWhere('reference', 'like', '%'.$this->search.'%');
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('livewire.memos.archives', [
            'archives' => $archives
        ]);
    }
}