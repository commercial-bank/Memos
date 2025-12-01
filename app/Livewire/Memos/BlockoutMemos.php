<?php

namespace App\Livewire\Memos;

use App\Models\Memo;
use App\Models\Entity;
use Livewire\Component;
use App\Models\References; // Assurez-vous que le modèle est bon (Reference ou References ?)
use Illuminate\Support\Facades\Auth;

class BlockoutMemos extends Component
{
    public $isOpen = false;
    public $selectedYear; // Variable pour stocker l'année choisie

    // Variables pour le modal (détails)
    public $object = '';
    public $content = '';
    public $concern = '';
    public $date = '';
    public $signature_sd = '';
    public $signature_dir = '';
    public $user_first_name = '';
    public $user_last_name = '';
    public $user_service = '';
    public $user_entity_name = '';
    public $user_entity_name_acronym = '';
    public $qr_code;
    public $recipientsByAction = [];

    // 1. Initialisation : On définit l'année par défaut au chargement de la page
    public function mount()
    {
        $this->selectedYear = date('Y'); // Par défaut : 2025 (ou l'année actuelle)
    }

    public function viewReference($id)
    {
        $memo = Memo::with(['destinataires', 'user'])->findOrFail($id);

        $this->object = $memo->object;
        $this->content = $memo->content;
        $this->concern = $memo->concern;
        $this->signature_sd = $memo->signature_sd;
        $this->signature_dir = $memo->signature_dir;
        $this->qr_code = $memo->qr_code;
        $this->date = $memo->created_at->format('d/m/Y');
        
        $this->user_first_name = $memo->user->first_name;
        $this->user_last_name = $memo->user->last_name;
        $this->user_service = $memo->user->service ?? 'Service Non Défini';
        $this->user_entity_name = $memo->user->entity_name;
        $this->user_entity_name_acronym = Entity::StatgetAcronymAttribute($this->user_entity_name);

        $this->recipientsByAction = $memo->destinataires
            ->groupBy(function ($destinataire) {
                return $destinataire->pivot->action;
            })
            ->toArray();

        $this->isOpen = true; 
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['object','concern', 'content', 'recipientsByAction']);
    }

    public function render()
    {
       // 2. Requête filtrée par l'année sélectionnée
       $references = References::query()
        ->where('nature', 'Memo Sortant')
        ->where('user_id', Auth::id())
        ->whereYear('created_at', $this->selectedYear) // <--- C'est ici que la magie opère
        ->orderBy('id', 'asc') 
        ->get();
        
        return view('livewire.memos.blockout-memos', [
            'references' => $references
        ]);
    }
}