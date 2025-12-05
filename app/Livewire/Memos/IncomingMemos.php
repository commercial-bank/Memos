<?php

namespace App\Livewire\Memos;

use Carbon\Carbon;
use App\Models\Memo;
use App\Models\User;
use App\Models\Entity;
use Livewire\Component;
use App\Models\References;
use App\Models\Historiques;
use App\Models\MemoHistory;
use Illuminate\Support\Str;
use App\Models\ReplacesUser;
use Illuminate\Support\Facades\Auth;


class IncomingMemos extends Component
{
    // --- RECHERCHE & DATATABLE ---
    public $search = '';

     // --- MODALS STATES ---
    public $isOpen = false;
    public $isOpen2 = false;
    public $isOpen3 = false; 
    public $isOpen4 = false;


    #[Rule('required|string|max:255')]
    public string $object = '';

    #[Rule('required|string|max:255')]
    public string $concern = '';

    #[Rule('required|string')]
    public string $content = '';

    // --- CHAMPS DU MÉMO (SCHEMA DB) ---
    public $memo_id = null;

    // --- VARIABLES POUR L'ENVOI (WORKFLOW) ---
    public $workflow_comment = '';
    public $selected_visa = ''; 
    public $target_users_ids = []; // Pour ajouter d'autres destinataires en plus du N+1
    
    // Pour l'affichage dans le modal
    public $nPlusOneUser = null;
    public $effectiveReceiver = null; // Celui qui reçoit vraiment (N+1 ou Remplaçant)
    public $isReplaced = false;
    public $usersList = []; // Liste de tous les users pour choix multiple

    // --- VARIABLES REJET ---
    public $isOpenReject = false;
    public $reject_comment = '';

    // --- DATA VIEW (Aperçu) ---
    public $date;
    public $user_service;
    public $user_first_name;
    public $user_last_name;
    public $user_entity_name;
    public $selections = []; // Pour modal assignation simple

    // Options de visa
    public $visaOptions = [
        'Vu' => 'Vu (Simple transmission)',
        'Vu & Accord' => 'Vu & D\'accord',
        'Vu & Pas d\'accord' => 'Vu & Pas d\'accord',
    ];

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


    public function assignMemo($id)
    {
        $this->memo_id = $id;
        
        // Reset des champs du formulaire
        $this->workflow_comment = '';
        $this->selected_visa = 'Vu'; // Valeur par défaut
        $this->target_users_ids = [];
        $this->isReplaced = false;

        // 1. Récupérer le Manager (N+1) de l'utilisateur connecté
        $currentUser = Auth::user();
        $managerId = $currentUser->manager_id; 

        if ($managerId) {
            $this->nPlusOneUser = User::find($managerId);
            
            if ($this->nPlusOneUser) {
                // 2. Vérifier si ce manager est actuellement remplacé
                // Note : On suppose que les dates sont stockées au format 'Y-m-d' dans la DB
                $today = Carbon::now()->format('Y-m-d'); 

                $replacement = ReplacesUser::where('user_id', $managerId)
                    ->where('date_begin_replace', '<=', $today)
                    ->where('date_end_replace', '>=', $today)
                    ->first();

                if ($replacement) {
                    // CAS : Le manager est absent et remplacé
                    $this->isReplaced = true;
                    $this->effectiveReceiver = User::find($replacement->user_id_replace);
                } else {
                    // CAS : Le manager est présent
                    $this->effectiveReceiver = $this->nPlusOneUser;
                }
            }
        } else {
            // Pas de manager défini dans la table users
            $this->nPlusOneUser = null;
            $this->effectiveReceiver = null;
        }

        // 3. Charger la liste des autres utilisateurs (sauf soi-même)
        // Pour permettre d'envoyer en copie ou à d'autres personnes
        $this->usersList = User::where('id', '!=', Auth::id())
                               ->orderBy('last_name')
                               ->orderBy('first_name')
                               ->get();

        $this->isOpen3 = true;
    }

    public function sendMemo()
    {
        $this->validate([
            'selected_visa' => 'required', // Le visa est obligatoire
            'workflow_comment' => 'nullable|string|max:1000',
        ]);

        $memo = Memo::find($this->memo_id);
        $currentUser = Auth::user();

        // --- 1. Calcul des Destinataires (Current Holders) ---
        $nextHolders = [];

         // A. Ajouter le destinataire hiérarchique
        if ($this->effectiveReceiver) {
            // Plus besoin de (string), on garde l'ID tel quel (entier ou string selon la DB)
            $nextHolders[] = $this->effectiveReceiver->id;
        }

        /// B. Ajouter les destinataires manuels
        if (!empty($this->target_users_ids)) {
            foreach ($this->target_users_ids as $uid) {
                // On s'assure que $uid est un entier pour la cohérence (optionnel, mais propre)
                $uidClean = is_numeric($uid) ? (int)$uid : $uid;

                if (!in_array($uidClean, $nextHolders)) {
                    $nextHolders[] = $uidClean;
                }
            }
        }

        // Sécurité : Si personne n'est ciblé
        if (empty($nextHolders)) {
            $this->addError('effectiveReceiver', 'Veuillez sélectionner au moins un destinataire.');
            return;
        }

        // --- 2. Mise à jour du Mémo ---
        
        $memo->previous_holders = [$currentUser->id];
        $memo->current_holders = $nextHolders;

        $memo->status = 'envoyer'; 
        $memo->workflow_comment = $this->workflow_comment; 
        
        $memo->save();

        Historiques::firstOrCreate(
            [
                // Critères de recherche (Ce qui rend l'action unique)
                'user_id' => $currentUser->id,
                'memo_id' => $memo->id,
                'visa'    => $this->selected_visa,
                'workflow_comment' => $this->workflow_comment ?? 'R.A.S',
            ]
        );
        // --- 4. Fin ---
        $this->closeModalTrois();
        $this->dispatch('notify', message: "Le mémo a été envoyer avec succès.");
    }

    public function closeModalTrois() { $this->isOpen3 = false; }

    // 1. Ouvre le modal de rejet
    public function askReject($id)
    {
        $this->memo_id = $id;
        $this->reject_comment = ''; // Réinitialiser le commentaire
        $this->resetValidation();   // Effacer les erreurs précédentes
        $this->isOpenReject = true;
    }

    // 2. Ferme le modal
    public function closeRejectModal()
    {
        $this->isOpenReject = false;
        $this->reject_comment = '';
    }

    public function processReject()
    {
        // Validation : Le commentaire est obligatoire pour un rejet
        $this->validate([
            'reject_comment' => 'required|string|min:5|max:1000',
        ], [
            'reject_comment.required' => 'Le motif du rejet est obligatoire.',
            'reject_comment.min' => 'Veuillez expliquer le motif plus en détail.',
        ]);

        $memo = Memo::find($this->memo_id);
        $currentUser = Auth::user();

        // LOGIQUE DE RETOUR À L'INITIATEUR (Createur du mémo)
        // Selon votre demande : current_holders devient l'ID du créateur (user_id)
        
        // 1. Mise à jour des porteurs (Le créateur récupère la main)
        $memo->current_holders = [0];
        
        // 2. L'historique précédent devient MOI (celui qui rejette)
        $memo->previous_holders = [0];

        // 3. Mise à jour statut et commentaire
        $memo->status = 'rejeter'; // Status demandé
        $memo->workflow_comment = $this->reject_comment;

        $memo->save();

        // 4. Enregistrement dans l'historique
        Historiques::firstOrCreate([
            'user_id' => $currentUser->id,
            'memo_id' => $memo->id,
            'visa' => 'rejeter', // Action spécifique
            'workflow_comment' => 'MOTIF REJET : ' . $this->reject_comment,
        ]);

        $this->closeRejectModal();
        $this->dispatch('notify', message: "Le mémo a été rejeté et renvoyé à son créateur.");
    }

    public function closeModal() { $this->isOpen = false; }
    
    
    public function render()
    {
        // On récupère l'ID tel quel (c'est un entier par défaut dans Laravel)
        $userId = Auth::id(); 

        $memos = Memo::with(['user', 'destinataires.entity'])
            ->where('workflow_direction', 'sortant')
            
            // Laravel va chercher l'entier dans le tableau JSON
            ->whereJsonContains('current_holders', $userId)
            
            // Recherche
            ->where(function($query) {
                $query->where('object', 'like', '%'.$this->search.'%')
                    ->orWhere('concern', 'like', '%'.$this->search.'%');
            })
            
            // Tri
            ->orderBy('updated_at', 'desc')
            ->paginate(9);

        return view('livewire.memos.incoming-memos', [
            'memos' => $memos,
        ]); 
    }
}