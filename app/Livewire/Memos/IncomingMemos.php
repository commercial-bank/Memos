<?php

namespace App\Livewire\Memos;

use App\Models\User;
use Livewire\Component;
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
    public $reference_input = ''; // Pour la secrétaire

    // Variables pour stocker les infos du mémo sélectionné
    public $object = '';
    public $content = '';
    public $date = '';
    public $signature_sd = '';
    public $signature_dir = '';
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
        $this->signature_sd = $memo->signature_sd;
        $this->signature_dir = $memo->signature_dir;
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


    /*public function openSendModal($id)
    {
        $this->selectedMemoId = $id;
        $memo = WrittenMemo::findOrFail($id);

        // FILTRE INTELLIGENT DES UTILISATEURS
        $this->usersList = User::where('id', '!=', Auth::id()) // Pas moi-même
            ->where('id', '!=', $memo->user_id) // Pas l'auteur initial (on ne renvoie pas le dossier à l'auteur, sauf rejet)
            ->when($memo->previous_holder_id, function($q) use ($memo) {
                $q->where('id', '!=', $memo->previous_holder_id); // Pas celui qui vient de me l'envoyer
            })
            ->get();

        $this->reset(['next_user_id', 'comment', 'reference_input']);
        $this->isSendOpen = true;
    }*/

    public function closeSendModal()
    {
        $this->isSendOpen = false;
    }

    // 1. OUVRIR LE MODAL DE REJET
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

    // 2. CONFIRMER LE REJET (Action réelle)
    public function confirmRejection()
    {
        // Validation : Le motif est obligatoire
        $this->validate([
            'rejection_comment' => 'required|min:5|string'
        ], [
            'rejection_comment.required' => 'Le motif du rejet est obligatoire.',
            'rejection_comment.min' => 'Le motif doit être explicite (min 5 caractères).'
        ]);

        $memo = WrittenMemo::findOrFail($this->selectedMemoId);
        
        // Logique de rejet
        $memo->current_holder_id = $memo->user_id; // Retour à l'auteur initial
        $memo->status = 'rejected';
        $memo->workflow_comment = $this->rejection_comment; // On sauvegarde le motif
        $memo->save();

        $this->isRejectOpen = false;
        $this->dispatch('notify', message: 'Le mémo a été rejeté et renvoyé à son auteur!');
    }




    // --- 3. REJETER (RETOUR À L'ENVOYEUR OU CRÉATEUR) ---
    public function rejectMemo($id)
    {
        // Logique simple : on renvoie au créateur initial
        $memo = WrittenMemo::findOrFail($id);

        // === AJOUT : ENREGISTRER L'HISTORIQUE ===
        MemoHistory::create([
            'written_memo_id' => $memo->id,
            'actor_id' => Auth::id(),
            'action' => (strtolower(Auth::user()->poste) === 'secretaire') ? 'validation' : 'transfer',
            'comment' => $this->comment
        ]);
        
        $memo->current_holder_id = $memo->user_id; // Retour au créateur
        $memo->status = 'rejected';
        $memo->save();

        $this->dispatch('notify', message: 'Mémo rejeté !');
    }






 // --- OUVERTURE DU MODAL D'ENVOI (AVEC FILTRE) ---
    public function openSendModal($id)
    {
        $this->selectedMemoId = $id;
        $memo = WrittenMemo::findOrFail($id);

        // FILTRE INTELLIGENT DES UTILISATEURS
        $this->usersList = User::where('id', '!=', Auth::id()) // Pas moi-même
            ->where('id', '!=', $memo->user_id) // Pas l'auteur initial (on ne renvoie pas le dossier à l'auteur, sauf rejet)
            ->when($memo->previous_holder_id, function($q) use ($memo) {
                $q->where('id', '!=', $memo->previous_holder_id); // Pas celui qui vient de me l'envoyer
            })
            ->get();

        $this->reset(['next_user_id', 'comment', 'reference_input']);
        $this->isSendOpen = true;
    }

    // --- LOGIQUE D'ENVOI ET DE DIFFUSION ---
    public function sendMemo()
    {
        $this->validate([
            'next_user_id' => 'nullable|exists:users,id', // Nullable pour la secrétaire (fin de circuit)
        ]);

        $memo = WrittenMemo::findOrFail($this->selectedMemoId);
        $currentUser = Auth::user();

        // === AJOUT : ENREGISTRER L'HISTORIQUE ===
        MemoHistory::create([
            'written_memo_id' => $memo->id,
            'actor_id' => Auth::id(),
            'action' => (strtolower(Auth::user()->poste) === 'secretaire') ? 'validation' : 'transfer',
            'comment' => $this->comment
        ]);

       

        // 2. SAUVEGARDE DU "PRÉCÉDENT DÉTENTEUR" (Pour le filtre au prochain tour)
        $memo->previous_holder_id = Auth::id();
        $memo->workflow_comment = $this->comment;

        // 3. CAS SPÉCIAL : SECRÉTAIRE GÉNÉRALE (FIN DU CIRCUIT PRINCIPAL)
        if (strtolower($currentUser->poste) === 'Secretaire') {
            $this->validate(['reference_input' => 'required']);
            
            $memo->reference_number = $this->reference_input;
            $memo->status = 'distributed'; // Le circuit principal est fini
            $memo->current_holder_id = null; // Plus personne ne détient l'original pour action
            
            $memo->save(); // Sauvegarde avant la diffusion

            // --- DIFFUSION VERS LES SECRÉTAIRES DES ENTITÉS ---
            $this->distributeToEntities($memo);

            $this->isSendOpen = false;
            $this->dispatch('notify', message: 'Mémo enregistré et diffusé aux entités avec success !');
            return;
        }

        // 4. CAS STANDARD : TRANSMISSION NORMALE (User -> User)
        $memo->status = 'pending';
        $memo->current_holder_id = $this->next_user_id;
        $memo->save();

        $this->isSendOpen = false;
        $this->dispatch('notify', message: 'Mémo transmis avec succès avec success !');
    }


    public function signDocument($id)
    {
        $memo = WrittenMemo::findOrFail($id);
        // 1. Générer un token unique sécurisé
        $token = Str::random(64); 

        if(Auth::user()->poste === 'Sous-Directeur')
        {
          
            $memo->update([
                'signature_sd' => $token
            ]);
            
            $this->dispatch('notify', message: "signer avec succès !");

        }else{

            $memo->update([
                'signature_dir' => $token
            ]);
            
            $this->dispatch('notify', message: "signer avec succès !");

        }



    }


    // --- FONCTION PRIVÉE POUR ENVOYER AUX SECRÉTAIRES DESTINATAIRES ---
    private function distributeToEntities($memo)
    {
        // On récupère toutes les lignes de la table pivot (les destinataires)
        $attributions = Memo::where('written_memo_id', $memo->id)->get();

        foreach ($attributions as $attribution) {
            // On cherche la secrétaire de cette entité spécifique
            // Hypothèse : La table users a 'poste'='secretaire' et 'entity'='Nom Entité' ou un 'entity_id'
            $secretary = User::where('poste', 'Secretaire')
                             ->where('entity', $attribution->entity->name) // Ou via entity_id si tu as la relation
                             ->first();

            if ($secretary) {
                // On met à jour la ligne pivot : C'est maintenant la secrétaire locale qui a la main
                $attribution->local_holder_id = $secretary->id;
                $attribution->local_status = 'received'; // En attente d'enregistrement local
                $attribution->save();
            }
        }
    }

    public function render()
    {
        // LOGIQUE : 
        // 1. Le mémo est actuellement chez moi (current_holder_id = Moi)
        // 2. Ce n'est pas un brouillon (sinon c'est dans l'onglet Brouillons)
        // 3. On charge l'auteur (user) pour savoir qui me l'a envoyé
        
        // 1. C'EST DÉFINI ICI : On récupère l'ID de l'utilisateur connecté
        $userId = Auth::id(); 
        $countincong=0;

        $groupedMemos = WrittenMemo::where('current_holder_id', Auth::id())
            ->has('memos')
            ->with(['memos.entity', 'user'])
            ->latest()
            ->get();

        // B. Mémos reçus (Circuit de diffusion - Je suis secrétaire d'entité ou directeur destinataire)
        // On récupère les WrittenMemos via la table pivot où je suis le détenteur local
        $distributedMemos = WrittenMemo::whereHas('memos', function($q) use ($userId) {
                $q->where('local_holder_id', $userId);
            })
            ->with(['user', 'memos' => function($q) use ($userId) {
                // On charge uniquement mon attribution pour savoir mon statut local
                $q->where('local_holder_id', $userId);
            }, 'memos.entity'])
            ->get();

        // On fusionne les deux listes
        $memos = $groupedMemos->merge($distributedMemos)->sortByDesc('updated_at');
        $countincong = $groupedMemos->merge($distributedMemos)->count();
        return view('livewire.memos.incoming-memos', [
            'memos' => $memos,
            'notif' => $countincong
        ]);
    }
}