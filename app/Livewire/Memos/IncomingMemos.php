<?php

namespace App\Livewire\Memos;

use Livewire\Component;
use App\Models\WrittenMemo;
use Illuminate\Support\Facades\Auth;

class IncomingMemos extends Component
{
    // On garde la logique d'ouverture de modal comme dans DocsMemos
    // Tu pourras copier-coller les méthodes viewDocument, openSendModal ici plus tard
    // pour pouvoir traiter les mémos entrants.
    public $isOpen = false;
    public $isSendOpen = false; // Modal Envoyer
    public $isRejectOpen = false; // Modal Rejeter

    // Variables pour stocker les infos du mémo sélectionné
    public $object = '';
    public $content = '';
    public $date = '';
    public $user_first_name = '';
    public $user_last_name = '';
    public $user_service = '';
    public $user_entity = '';

    // Variable tableau pour stocker les destinataires triés par action
    public $recipientsByAction = [];


    // Méthode appelée quand on clique sur le bouton bleu "Voir"
    public function viewDocument($id)
    {
        // 1. Récupérer le document
        // J'ai retiré 'user.entity' du with() car cette relation n'existe pas chez toi
        $memo = WrittenMemo::with(['memos.entity', 'user'])->findOrFail($id);

        // 2. Remplir les variables
        $this->object = $memo->object;
        $this->content = $memo->content;
        $this->date = $memo->created_at->format('d/m/Y');
        
        $this->user_first_name = $memo->user->first_name;
        $this->user_last_name = $memo->user->last_name;
        $this->user_service = $memo->user->service ?? 'Service Non Défini';
        $this->user_entity = $memo->user->entity; // Valeur par défaut

        // 3. CORRECTION ICI : On convertit en TABLEAU pour éviter l'erreur Livewire
        $this->recipientsByAction = $memo->memos->groupBy('action')->toArray();

        // 4. Ouvrir le modal
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['object', 'content', 'recipientsByAction']);
    }

    public function render()
    {
        // LOGIQUE : 
        // 1. Le mémo est actuellement chez moi (current_holder_id = Moi)
        // 2. Ce n'est pas un brouillon (sinon c'est dans l'onglet Brouillons)
        // 3. On charge l'auteur (user) pour savoir qui me l'a envoyé
        

        $groupedMemos = WrittenMemo::where('current_holder_id', Auth::id())
            ->has('memos')
            ->with(['memos.entity', 'user'])
            ->latest()
            ->get();

        return view('livewire.memos.incoming-memos', [
            'groupedMemos' => $groupedMemos
        ]);
    }
}