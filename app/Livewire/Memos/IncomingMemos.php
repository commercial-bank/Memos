<?php

namespace App\Livewire\Memos;

use App\Models\Memo;
use App\Models\User;
use App\Models\Entity;
use Livewire\Component;
use App\Models\Historiques;
use App\Models\MemoHistory;
use App\Models\WrittenMemo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class IncomingMemos extends Component
{
    
    // On garde la logique d'ouverture de modal comme dans DocsMemos
    // Tu pourras copier-coller les méthodes viewDocument, openSendModal ici plus tard
    // pour pouvoir traiter les mémos entrants.
    public $isOpen = false;
    public $isSendOpen = false; // Modal Envoyer
    public $isRejectOpen = false; // Modal Rejeter
    public $rejection_comment = ''; // Le motif du rejet
    public ?WrittenMemo $sign_memo = null;



      // Variables pour l'envoi
    public $selectedMemoId;
    public $next_user_id; // L'utilisateur à qui on envoie
    public $comment = ''; // Commentaire (optionnel)
    public $action = ''; // Pour la secrétaire

    // Variables pour stocker les infos du mémo sélectionné
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

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['object','concern', 'content', 'recipientsByAction']);
    }

    public function openSendModal($id)
    {
        $this->selectedMemoId = $id;
        $memo = Memo::findOrFail($id);

        // FILTRE INTELLIGENT DES UTILISATEURS
        $this->usersList = User::where('id', '!=', Auth::id()) // Pas moi-même
            ->where('id', '!=', $memo->user_id) // Pas l'auteur initial (on ne renvoie pas le dossier à l'auteur, sauf rejet)
            ->when($memo->previous_holder_id, function($q) use ($memo) {
                $q->where('id', '!=', $memo->previous_holder_id); // Pas celui qui vient de me l'envoyer
            })
            ->get();

        $this->reset(['next_user_id', 'comment', 'action']);
        $this->isSendOpen = true;
    }

    public function signDocument($id,$post)
    {
        $memo = Memo::findOrFail($id);
        // 2. Générer une signature unique (Ex: "SD-8X9P2M")
        // On combine le poste, un timestamp court et une chaine aléatoire
        $uniqueSignature = Str::upper(Str::random(10)); 
        $date = now()->format('d/m/Y H:i');
        
        // 3. Modifier le champ selon le poste
        if ($post === 'Sous-Directeur') {
            $memo->signature_sd = $uniqueSignature;
        } 
        elseif ($post === 'Directeur') {
            // Au cas où tu voudrais gérer les deux dans la même fonction
            $memo->signature_dir = $uniqueSignature;
        }

        $memo->save();

        $this->dispatch('notify', message: 'Signature apposée avec succès !');
        
    }

    public function qrDocument($id)
    {
         $memo = Memo::findOrFail($id);
         
         $token = Str::random(40); // Ex: a1b2c3d4...
         $memo->qr_code = $token;

         $memo->save();

         $this->dispatch('notify', message: 'Document signé et QR Code généré avec succès !', type: 'success');
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

    
    public function openRejectModal($id)
    {
        $this->selectedMemoId = $id;
        $this->rejection_comment = ''; // On vide le champ
        $this->isRejectOpen = true;
    }

    public function closeRejectModal()
    {
        $this->isRejectOpen = false;
    }

    public function confirmRejection()
    {
        // Validation : Le motif est obligatoire
        $this->validate([
            'rejection_comment' => 'required|min:5|string'
        ], [
            'rejection_comment.required' => 'Le motif du rejet est obligatoire.',
            'rejection_comment.min' => 'Le motif doit être explicite (min 5 caractères).'
        ]);

        $memo = Memo::findOrFail($this->selectedMemoId);
        
        // Logique de rejet
        $memo->current_holder_id = $memo->user_id; // Retour à l'auteur initial
        $memo->status = 'rejected';
        $memo->workflow_comment = $this->rejection_comment; // On sauvegarde le motif
        $memo->save();

        $this->isRejectOpen = false;
        $this->dispatch('notify', message: 'Le mémo a été rejeté et renvoyé à son auteur!');
    }



    public function render()
    {
         // Récupérer l'ID de l'utilisateur connecté
            $userId = Auth::id();

        // 1. On cherche les mémos où 'current_holder_id' est l'utilisateur connecté
        // 2. On charge la relation 'destinataires' (Eager Loading) pour éviter les requêtes N+1
        $memos = Memo::where('current_holder_id', $userId)
                    ->with('destinataires') 
                    ->get();

                 

        return view('livewire.memos.incoming-memos', [
            'memos' => $memos,
        ]);
    }
}