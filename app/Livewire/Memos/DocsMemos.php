<?php

namespace App\Livewire\Memos;

use App\Models\User;
use Livewire\Component;
use App\Models\WrittenMemo;
use Illuminate\Support\Facades\Auth;

class DocsMemos extends Component
{
    public $isOpen = false;
    public $isSendOpen = false; // Modal Envoyer
    public $isRejectOpen = false; // Modal Rejeter

    // Variables pour l'envoi
    public $selectedMemoId;
    public $next_user_id; // L'utilisateur à qui on envoie
    public $comment = ''; // Commentaire (optionnel)
    public $reference_input = ''; // Pour la secrétaire

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

    public $usersList = []; // Liste des destinataires possibles

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

     public function openSendModal($id)
    {
        $this->selectedMemoId = $id;
        $this->usersList = User::where('id', '!=', Auth::id())->get(); // Tous sauf moi
        $this->reset(['next_user_id', 'comment', 'reference_input']);
        $this->isSendOpen = true;
    }

    public function closeSendModal()
    {
        $this->isSendOpen = false;
    }

    // --- 2. LOGIQUE MÉTIER D'ENVOI (LE COEUR DU SYSTÈME) ---
    public function sendMemo()
    {
        $this->validate([
            'next_user_id' => 'required|exists:users,id',
        ]);

        $memo = WrittenMemo::findOrFail($this->selectedMemoId);
        $currentUser = Auth::user();

        // LOGIQUE DES SIGNATURES AUTOMATIQUES SELON LE POSTE
        
        // Cas 1 : Sous-Directeur
        if (strtolower($currentUser->poste) === 'sous-directeur') {
            $memo->signature_sd = $currentUser->first_name . ' ' . $currentUser->last_name;
        }

        // Cas 2 : Directeur
        if (strtolower($currentUser->poste) === 'directeur') {
            $memo->signature_dir = $currentUser->first_name . ' ' . $currentUser->last_name;
        }

        // Cas 3 : Secrétaire (Finalisation)
        if (strtolower($currentUser->poste) === 'secretaire') {
            $this->validate(['reference_input' => 'required']); // Référence obligatoire
            $memo->reference_number = $this->reference_input;
            $memo->status = 'distributed'; // Statut final
            // Ici, le next_user_id serait la secrétaire destinatrice, ou alors on arrête le circuit ici.
        } else {
            $memo->status = 'pending'; // En cours de circuit
        }

        // Mise à jour du détenteur
        $memo->current_holder_id = $this->next_user_id;
        $memo->workflow_comment = $this->comment;
        $memo->save();

        $this->isSendOpen = false;
        $this->dispatch('dispatch-notify', message: 'Mémo transmis avec succès !', type: 'success');
    }

    // --- 3. REJETER (RETOUR À L'ENVOYEUR OU CRÉATEUR) ---
    public function rejectMemo($id)
    {
        // Logique simple : on renvoie au créateur initial
        $memo = WrittenMemo::findOrFail($id);
        $memo->current_holder_id = $memo->user_id; // Retour au créateur
        $memo->status = 'rejected';
        $memo->save();

        $this->dispatch('dispatch-notify', message: 'Mémo rejeté.', type: 'error');
    }

    public function render()
    {
        $groupedMemos = WrittenMemo::where('user_id', Auth::id())
            ->has('memos')
            ->with(['memos.entity', 'user'])
            ->latest()
            ->get();

        return view('livewire.memos.docs-memos', [
            'groupedMemos' => $groupedMemos
        ]);
    }
}