<?php

namespace App\Livewire\Memos;

use App\Models\Memo;
use App\Models\Entity;
use Livewire\Component;
use App\Models\References;

class BlockoutMemos extends Component
{

    public $isOpen = false;

    //Variables pour stocker les infos du mémo sélectionné
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
    public $user_entity_name_acronym='';
    public $qr_code;

    // Variable tableau pour stocker les destinataires triés par action
    public $recipientsByAction = [];


    public function viewReference($id)
    {
        // 1. Récupérer le document
        // J'ai retiré 'user.entity' du with() car cette relation n'existe pas chez toi
        
        $memo = Memo::with(['destinataires', 'user'])->findOrFail($id);


        // 2. Remplir les variables
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
        $this->user_entity_name = $memo->user->entity_name; // Valeur par défaut
        $this->user_entity_name_acronym = Entity::StatgetAcronymAttribute($this->user_entity_name);
        

        // 3. CORRECTION : Utiliser une fonction anonyme pour cibler 'pivot.action'
        $this->recipientsByAction = $memo->destinataires
            ->groupBy(function ($destinataire) {
                // On groupe selon la colonne 'action' de la table PIVOT
                return $destinataire->pivot->action;
            })
            ->toArray();

        // 4. Ouvrir le modal
        $this->isOpen = true; 
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['object','concern', 'content', 'recipientsByAction']);
    }

    
    public function render()
    {

        
              
       $references = References::query()
        ->where('nature', 'Memo Sortant') // Décommentez si vous devez filtrer par type
        ->orderBy('id', 'asc') 
        ->get();
        
        
        return view('livewire.memos.blockout-memos', [
        'references' => $references
        ]);
    }
}
