<?php

namespace App\Livewire\Memos;

use App\Models\Memo;
use App\Models\User;
use App\Models\Entity;
use Livewire\Component;
use App\Models\References;
use App\Models\Historiques;
use Illuminate\Support\Facades\Auth;

class Incoming2Memos extends Component
{

    // On garde la logique d'ouverture de modal comme dans DocsMemos
    // Tu pourras copier-coller les méthodes viewDocument, openSendModal ici plus tard
    // pour pouvoir traiter les mémos entrants.
    public $isOpen = false;
    public $isSendOpen = false;
    public $enregistrer= false;

    // Variables pour l'envoi
    public $selectedMemoId;
    public $next_user_id; // L'utilisateur à qui on envoie
    public $comment = ''; // Commentaire (optionnel)
    public $action = ''; // Pour la secrétaire

    // Variable tableau pour stocker les destinataires triés par action
    public $recipientsByAction = [];
    public $usersList = [];

    // Champs du formulaire Reference
    public $ref_nature;
    public $ref_date;
    public $ref_numero;
    public $ref_object;
    public $ref_concern;
    public $ref_entity_exp;

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
    public $qr_code;





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



    public function EnregistrerDocument($id)
    {
        $this->selectedMemoId = $id;
        $memo = Memo::with('destinataires')->findOrFail($id);


        // B. On pré-remplit le formulaire avec les infos du Mémo
        $this->ref_nature = 'Memo Entrant'; // Valeur par défaut
        $this->ref_date = now()->format('d/m/Y');
        $this->ref_numero = $memo->reference ?? 'À définir';
        $this->ref_object = $memo->object;
        $this->ref_concern = $memo->concern;
        $this->ref_entity_exp = $memo->user->entity_name;

        // C. On ouvre la modale
        $this->enregistrer = true;

    }

    // 2. L'ENREGISTREMENT ET L'ENVOI
    public function confirmRegistration()
    {
        // 1. Validation
        $this->validate([
            'ref_nature' => 'required|string',
            'ref_date' => 'required|string',
            'ref_numero' => 'required|string',
            'ref_object' => 'required|string',
            'ref_concern' => 'required|string', // Attention à bien avoir ce champ dans le formulaire
            'ref_entity_exp' => 'required|string',
        ]);

        $memo = Memo::findOrFail($this->selectedMemoId);
        $currentUser = Auth::user();

        // 2. Création de la Référence
        // Assure-toi d'avoir importé le modèle : use App\Models\Reference;
        References::create([
            'nature' => $this->ref_nature,
            'date' => $this->ref_date,
            'numero_ordre_path' => $this->ref_numero,
            'object' => $this->ref_object,
            'concerne' => $this->ref_concern ?? '', 
            'entity_exp' => $this->ref_entity_exp ?? '',
            'memo_id' => $memo->id,
        ]);

        // =========================================================
        // 3. GESTION DE L'HISTORIQUE (Previous Holders)
        // On ajoute l'utilisateur connecté (TOI) à la liste des anciens
        // =========================================================
        
        $rawHistory = $memo->previous_holders;

        // Normalisation en tableau
        if (is_array($rawHistory)) {
            $history = $rawHistory;
        } elseif (empty($rawHistory)) {
            $history = [];
        } else {
            $history = [$rawHistory];
        }

        // Ajout de l'ID courant s'il n'y est pas déjà
        if (!in_array($currentUser->id, $history)) {
            $history[] = $currentUser->id;
        }

        $memo->previous_holders = $history;


        // =========================================================
        // 4. GESTION DU NOUVEAU DÉTENTEUR (Current Holder)
        // On envoie au MANAGER de l'utilisateur connecté
        // =========================================================
        
        $managerId = $currentUser->manager_id;

        if ($managerId) {
            // On met l'ID dans un tableau car la colonne est typée JSON/Array
            $memo->current_holders = [$managerId];
            
            $memo->workflow_direction = 'entrant'; // ou 'hierarchique' selon ta logique
            $memo->status = 'transmis';    // Optionnel
            $memo->save();

            $this->dispatch('notify', message: 'Document enregistré et transmis à votre manager.', type: 'success');
        } else {
            // Sécurité : Si l'utilisateur n'a pas de manager défini
            $this->dispatch('notify', message: 'Erreur : Aucun manager n\'est défini sur votre profil.', type: 'error');
            return; // On arrête là
        }

        // 5. Fermeture
        $this->enregistrer = false;
        
        // Reset du formulaire (Optionnel)
        $this->reset(['ref_nature', 'ref_date', 'ref_numero', 'ref_object', 'ref_concern', 'ref_entity_exp']);
    }
    
    public function closeRegisterModal()
    {
        $this->enregistrer = false;
    }


    public function openSendModal($id)
    {
        $this->selectedMemoId = $id;
        // On s'assure que le mémo existe
        Memo::findOrFail($id); 
        
        $currentUser = Auth::user();
        $targetPoste = null;

        // 1. DÉFINITION DE LA CIBLE SELON LA HIÉRARCHIE
        if ($currentUser->poste == "Directeur") {
            $targetPoste = "Sous-Directeur";
        } 
        elseif ($currentUser->poste == "Sous-Directeur") {
            $targetPoste = "Chef-Departement";
        } 
        elseif ($currentUser->poste == "Chef-Departement") {
            $targetPoste = "Chef-Service";
        }

        // 2. CONSTRUCTION DE LA LISTE DES UTILISATEURS
        if ($targetPoste) {
            // CAS 1 : On cherche un poste spécifique + ses remplaçants
            
            // A. On trouve les titulaires du poste dans la même entité
            $titulaires = User::where('entity_name', $currentUser->entity_name)
                            ->where('poste', $targetPoste)
                            ->get();

            // B. On récupère leurs IDs
            $ids = $titulaires->pluck('id')->toArray();

            // C. On récupère aussi les IDs de leurs remplaçants (s'ils existent)
            $replacementIds = $titulaires->pluck('manager_replace_id')->filter()->toArray();

            // D. On fusionne les deux listes
            $finalIds = array_merge($ids, $replacementIds);

            // E. On charge la liste finale des objets Users
            $this->usersList = User::whereIn('id', $finalIds)->get();

        }else{
        // CAS 2 : On récupère les collègues de l'entité
        // SAUF la hiérarchie et la secrétaire
        
        $postesAExclure = [
            'Directeur',
            'Sous-Directeur',
            'Chef-Departement',
            'Chef-Service',
            'Secretaire' // Vérifie si c'est 'Secretaire' ou 'Secrétaire' dans ta BDD
        ];

        $this->usersList = User::where('entity_name', $currentUser->entity_name)
                            ->where('id', '!=', $currentUser->id) // Sauf moi
                            ->whereNotIn('poste', $postesAExclure) // Sauf ces postes
                            ->get();
        }

        // 3. OUVERTURE DE LA MODALE
        $this->isSendOpen = true;
    }


    public function sendMemo()
    {
        // 1. Validation
        $this->validate([
            'next_user_id' => 'required|exists:users,id',
            'action' => 'required',
        ]);

        $memo = Memo::findOrFail($this->selectedMemoId);
        $currentUserId = Auth::id();

        // 2. Enregistrer l'historique dans la table dédiée (C'est OK)
        Historiques::create([
            'workflow_comment' => $this->comment,
            'action' => $this->action,
            'memo_id' => $memo->id,
            'user_id' => $currentUserId
        ]);

        // 3. MISE À JOUR DU MÉMO (C'est ici qu'il y a les corrections)

        // A. Gestion de l'historique des détenteurs (Previous)
        // On récupère la liste existante, sinon ça plante si c'est null
        $history = $memo->previous_holders ?? []; 
        
        // On ajoute l'ID seulement s'il n'y est pas déjà
        if (!in_array($currentUserId, $history)) {
            $history[] = $currentUserId;
        }
        $memo->previous_holders = $history; // On sauvegarde le tableau complet

        // B. Gestion du nouveau détenteur (Current)
        // CORRECTION : Il faut mettre l'ID dans un tableau [] car ta colonne est un JSON array
        $memo->current_holders = [(int) $this->next_user_id]; 

        // C. Autres champs
        // B. Gestion du nouveau détenteur (Current)
            $memo->current_holders = [(int) $this->next_user_id]; 

            // C. Autres champs
            $memo->workflow_comment = $this->comment;

            // === LOGIQUE DE STATUT (Coter vs Attribuer) ===
            
            // 1. On récupère les infos du destinataire
            $nextUser = User::find($this->next_user_id);

            // 2. On définit la liste des postes "Hiérarchiques / Administratifs"
            $postesHierarchiques = [
                'Directeur',
                'Sous-Directeur',
                'Chef-Departement',
                'Chef-Service',
                'Secretaire'
            ];

            // 3. Vérification
            // Si le poste du destinataire N'EST PAS dans la liste (donc c'est un employé simple)
            if ($nextUser && !in_array($nextUser->poste, $postesHierarchiques)) {
                $memo->status = 'attribuer';
            } else {
                // Sinon (c'est un chef ou une secrétaire)
                $memo->status = 'coter'; 
            }
        
        // N'oublie pas la direction si tu veux que ton filtre 'sortant' marche
        $memo->workflow_direction = 'entrant'; 

        $memo->save();

        // 4. Fin
        $this->isSendOpen = false;
        
        // Reset pour éviter que les champs restent remplis à la prochaine ouverture
        $this->reset(['next_user_id', 'comment', 'action']);

        $this->dispatch('notify', message: 'Mémo envoyé avec succès !', type: 'success');
    }


    public function closeSendModal()
    {
        $this->isSendOpen = false;
    }



    public function render()
    {
       $userId = Auth::id();

        $memos = Memo::query()
            // On vérifie si le tableau JSON 'current_holders' contient l'ID du user
            ->whereJsonContains('current_holders', $userId) 
            
            ->where('workflow_direction', 'entrant')
            ->with('destinataires')
            ->get();

        return view('livewire.memos.incoming2-memos', [
            'memos' => $memos,
        ]);
    }
}
