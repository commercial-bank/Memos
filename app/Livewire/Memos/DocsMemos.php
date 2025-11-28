<?php

namespace App\Livewire\Memos;

use App\Models\Memo;
use App\Models\User;
use App\Models\Entity;
use Livewire\Component;
use App\Models\Historiques;
use App\Models\MemoHistory;
use App\Models\WrittenMemo;
use Illuminate\Support\Facades\Auth;

class DocsMemos extends Component
{
    public $isOpen = false;
    public $isOpen2 = false;
    public $isSendOpen = false; // Modal Envoyer
    public $isRejectOpen = false; // Modal Rejeter

    // Variables pour l'envoi
    public $selectedMemoId;
    public $next_user_id; // L'utilisateur à qui on envoie
    public $comment = ''; // Commentaire (optionnel)
    public $action = ''; 

    // Variables pour stocker les infos du mémo sélectionné
    public $memo_id;
    public $object = '';
    public $content = '';
    public $concern = '';
    public $signature_sd = '';
    public $signature_dir = '';
    public $date = '';
    public $user_first_name = '';
    public $user_last_name = '';
    public $user_service = '';
    public $user_entity_name = '';
    public $user_entity_name_acronym='';

    // NOUVEAU : Variables pour l'historique
    public $isHistoryOpen = false;
    public $memoHistory = [];

    // Variable tableau pour stocker les destinataires triés par action
    public $recipientsByAction = [];

    public $usersList = []; // Liste des destinataires possibles


    public function viewDocument($id)
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

    public function editMemo($id)
    {
        $memo = Memo::findOrFail($id);
        $this->object = $memo->object;
        $this->concern = $memo->concern;
        $this->content = $memo->content;
        $this->memo_id = $memo->id;
        $this->date = $memo->created_at->format('d/m/Y');   
        $this->user_first_name = $memo->user->first_name;
        $this->user_last_name = $memo->user->last_name;
        $this->user_service = $memo->user->service;
        $this->user_entity_name = $memo->user->entity_name;
        $this->openModalDeux();
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['object','concern', 'content', 'recipientsByAction']);
    }

    // Ouvrir le modal
    public function openModalDeux()
    {
        $this->isOpen2 = true;
    }

    public function closeModalDeux()
    {
        $this->isOpen2 = false;
    }


    public function openHistoryModal($id)
    {
        // On récupère l'historique trié par date (du plus récent au plus vieux ou inversement)
        $this->memoHistory = Historiques::where('memo_id', $id)
            ->with('user_id')
            ->orderBy('created_at', 'desc') // Le plus récent en haut
            ->get();

        $this->isHistoryOpen = true;
    }

    public function closeHistoryModal()
    {
        $this->isHistoryOpen = false;
    }

    public function openSendModal($id)
    {
        $this->selectedMemoId = $id;
        $this->usersList = User::where('id', '!=', Auth::id())->get(); // Tous sauf moi
        //$this->reset(['next_user_id', 'comment', 'reference_input']);
        $this->isSendOpen = true;
    }

    public function sendMemo()
    {
        
        $this->validate([
            'next_user_id' => 'required|exists:users,id',
            'action' => 'required',
        ]);

        $memo = Memo::findOrFail($this->selectedMemoId);
       
        // === AJOUT : ENREGISTRER L'HISTORIQUE ===
        Historiques::create([
            'workflow_comment' => $this->comment,
            'action' => $this->action,
            'memo_id' =>$memo->id,
            'user_id' => Auth::id()
        ]);

        $currentUser = Auth::user();

        // LOGIQUE DES SIGNATURES AUTOMATIQUES SELON LE POSTE
        
        

        // Mise à jour du détenteur
        $memo->previous_holder_id = Auth::id();
        $memo->current_holder_id = $this->next_user_id;
        $memo->workflow_comment = $this->comment;
        $memo->status = 'pending';
        $memo->save();

        $this->isSendOpen = false;
        $this->dispatch('notify', message: 'Mémo envoyer avec succès !', type: 'success');
    }

    public function closeSendModal()
    {
        $this->isSendOpen = false;
    }

    public function save()
    {
        

        Memo::updateOrCreate(
            ['id' => $this->memo_id],
            [
                'object' => $this->object,
                'concern' => $this->concern,
                'content' => $this->content,
                'user_id' => Auth::id()
            ]
        );

        $action = $this->memo_id ? 'modifié' : 'créé';
        
        $this->closeModalDeux();
        
        // Envoi de l'événement pour le Toast
        $this->dispatch('notify', message: "Brouillon $action avec succès !");
    }

    


    public function render()
    {
        
        $groupedMemos = Memo::where('user_id', Auth::id())
            ->has('destinataires')
            ->with(['destinataires', 'user'])
            ->latest()
            ->get();

        return view('livewire.memos.docs-memos', [
            'groupedMemos' => $groupedMemos
        ]);
    }
}