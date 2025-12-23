<?php

namespace App\Livewire\Memos;

use App\Models\Memo;
use App\Models\User;
use App\Models\Entity;
use App\Models\Historiques;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class SendMemos extends Component
{
    // --- États des Modals ---
    public $isOpen = false;
    public $isOpen2 = false;
    public $isSendOpen = false; 
    public $isRejectOpen = false; 
    public $isHistoryOpen = false;

    // --- Variables de formulaire / Envoi ---
    public $selectedMemoId;
    public $next_user_id; 
    public $comment = ''; 
    public $action = ''; 

    // --- Variables de stockage de données (Vue/Aperçu) ---
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
    public $user_entity_name_acronym = '';

    // --- Collections de données ---
    public $memoHistory = [];
    public $recipientsByAction = [];
    public $author_memo;
    public $usersList = []; 

    /**
     * Visualise un document avec ses destinataires groupés par action
     */
    public function viewDocument($id)
    {
        // Optimisation : Chargement des relations destinataires et user en une seule fois
        $memo = Memo::with(['destinataires', 'user'])->findOrFail($id);

        $this->object = $memo->object;
        $this->content = $memo->content;
        $this->concern = $memo->concern;
        $this->signature_sd = $memo->signature_sd;
        $this->signature_dir = $memo->signature_dir;
        $this->date = $memo->created_at->format('d/m/Y');
        
        $this->user_first_name = $memo->user->first_name;
        $this->user_last_name = $memo->user->last_name;
        $this->user_service = $memo->user->service ?? 'Service Non Défini';
        $this->user_entity_name = $memo->user->entity_name;
        
        // Appel de la méthode statique pour l'acronyme
        $this->user_entity_name_acronym = Entity::StatgetAcronymAttribute($this->user_entity_name);

        // Groupement des destinataires via la table pivot
        $this->recipientsByAction = $memo->destinataires
            ->groupBy(fn($destinataire) => $destinataire->pivot->action)
            ->toArray();

        $this->isOpen = true;
    }

    /**
     * Charge les données du mémo pour édition (Brouillon)
     */
    public function editMemo($id)
    {
        $memo = Memo::with('user')->findOrFail($id);
        
        $this->memo_id = $memo->id;
        $this->object = $memo->object;
        $this->concern = $memo->concern;
        $this->content = $memo->content;
        $this->date = $memo->created_at->format('d/m/Y');   
        $this->user_first_name = $memo->user->first_name;
        $this->user_last_name = $memo->user->last_name;
        $this->user_service = $memo->user->service;
        $this->user_entity_name = $memo->user->entity_name;
        
        $this->openModalDeux();
    }

    /**
     * Ferme l'aperçu et réinitialise les variables pour alléger le prochain rendu Livewire
     */
    public function closeModal()
    {
        $this->isOpen = false;
        // Optimisation : Vider les variables lourdes réduit le délai de réponse réseau
        $this->reset(['object', 'concern', 'content', 'recipientsByAction']);
    }

    public function openModalDeux() { $this->isOpen2 = true; }
    public function closeModalDeux() { $this->isOpen2 = false; }

    /**
     * Récupère l'historique des actions sur un mémo
     */
    public function openHistoryModal($id)
    {
        // Optimisation : Eager loading de 'user' au lieu de 'user_id' pour éviter les requêtes N+1
        $this->memoHistory = Historiques::where('memo_id', $id)
            ->with('user') 
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray(); // Conversion en array pour plus de rapidité d'affichage

        $this->isHistoryOpen = true;
    }

    public function closeHistoryModal()
    {
        $this->isHistoryOpen = false;
        $this->reset(['memoHistory']); // Nettoyage mémoire
    }

    /**
     * Prépare l'ouverture du modal d'envoi vers le manager ou remplaçant
     */
    public function openSendModal($id)
    {
        $this->selectedMemoId = $id;
        $memo = Memo::select('user_id')->findOrFail($id);
        $this->author_memo = $memo->user_id;

        $user = Auth::user();
        // Optimisation : Récupération ciblée des IDs valides
        $targetIds = array_filter([$user->manager_id, $user->manager_replace_id]);

        $this->usersList = User::whereIn('id', $targetIds)->get();
        $this->isSendOpen = true;
    }

    /**
     * Exécute l'envoi du mémo et enregistre l'historique
     */
    public function sendMemo()
    {
        $memo = Memo::findOrFail($this->selectedMemoId);
        $isAuthor = (Auth::id() == $memo->user_id);

        // Validation dynamique selon le rôle (auteur ou validateur)
        $rules = [
            'next_user_id' => 'required|exists:users,id',
        ];
        if (!$isAuthor) {
            $rules['action'] = 'required';
        }

        $this->validate($rules);

        // Création de l'historique (Unifié pour gagner en lisibilité/vitesse)
        Historiques::create([
            'workflow_comment' => $this->comment,
            'action' => $isAuthor ? 'createur' : $this->action,
            'memo_id' => $memo->id,
            'user_id' => Auth::id()
        ]);

        // Mise à jour du mémo
        // Note : On respecte tes types de données (int pour auteur, array pour les autres)
        $memo->update([
            'previous_holders' => $isAuthor ? Auth::id() : [Auth::id()],
            'current_holders'  => $isAuthor ? (int) $this->next_user_id : [$this->next_user_id],
            'workflow_comment' => $this->comment,
            'status'           => 'pending',
        ]);

        $this->isSendOpen = false;
        $this->reset(['comment', 'action', 'next_user_id']); // Nettoyage après envoi
        $this->dispatch('notify', message: 'Mémo envoyé avec succès !', type: 'success');
    }

    public function closeSendModal()
    {
        $this->isSendOpen = false;
        $this->reset(['usersList']);
    }

    /**
     * Sauvegarde du brouillon
     */
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
        $this->dispatch('notify', message: "Brouillon $action avec succès !");
    }

    /**
     * Rendu du composant
     */
    public function render()
    {
        // Optimisation : Sélection uniquement des champs nécessaires et eager loading
        $groupedMemos = Memo::where('user_id', Auth::id())
            ->whereNotIn('status', ['brouillon', 'document'])
            ->has('destinataires')
            ->with(['destinataires', 'user'])
            ->latest()
            ->get();

        return view('livewire.memos.send-memos', [
            'groupedMemos' => $groupedMemos
        ]);
    }
}