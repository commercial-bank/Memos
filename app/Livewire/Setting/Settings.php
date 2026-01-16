<?php

namespace App\Livewire\Setting;

use App\Models\User;
use App\Models\Entity;
use Livewire\Component;
use App\Models\Historique; 
use App\Models\Historiques;
use App\Models\Memo;       
use App\Models\ReplacesUser;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Notifications\AdminNotification;

class Settings extends Component
{
    use WithPagination;

    // --- État Global ---
    public $activeTab = 'users'; 
    public $search = '';
    public $viewingMemoId = null;
    public $structureParents = []; 
    
    // --- Variables Users/Structures ---
    public $showDeactivationModal = false;
    public $blocking_reason = '';
    public $userToToggleId = null;
    public $showModal = false;
    public $isEditing = false;
    public $itemId = null;
    public $ref; 
    public $name; 
    public $type; 
    public $upper_id;   
    public $selectedMemo = null;
    
    // --- Remplacements ---
    public $replace_user_id; 
    public $replace_actions = []; 
    public $date_begin; 
    public $date_end; 
    public $userReplacements = [];
    
    // --- Infos Pros ---
    public $poste; 
    public $dir_id; 
    public $sd_id; 
    public $dep_id; 
    public $serv_id; 
    public $manager_id;
    public $directions_list = []; 
    public $sd_list = []; 
    public $dep_list = []; 
    public $serv_list = [];
    public $showDeleteModal = false; 
    public $structureToDeleteId = null; 
    public $structureToDeleteName = '';

    // --- VARIABLES WORKFLOW CONTROL ---
    public $showWorkflowModal = false;
    public $target_user_id; 
    public $transfer_reason;
    public $circuitUsers = []; // Pour la Timeline

    // --- REINITIALISATION ---
    public function updatingActiveTab() { 
        $this->resetPage(); 
        $this->search = ''; 
        $this->resetValidation();
        // On reset tout sauf les listes statiques
        $this->reset([
            'showModal', 'selectedMemo', 'viewingMemoId', 
            'showWorkflowModal', 'circuitUsers', 'target_user_id', 'transfer_reason',
            'itemId', 'ref', 'name', 'upper_id'
        ]); 
    }

    public function updatingSearch() { $this->resetPage(); }

    // --- LISTES DYNAMIQUES ---

    public function updatedDirId($value) 
    { 
        // Si on change la direction, on vide les enfants et on recharge la liste du dessous
        $this->sd_id = null; $this->dep_id = null; $this->serv_id = null;
        $this->sd_list = Entity::where('upper_id', $value)->get(); 
        $this->dep_list = []; $this->serv_list = [];
    }

    public function updatedSdId($value) 
    { 
        $this->dep_id = null; $this->serv_id = null;
        $this->dep_list = Entity::where('upper_id', $value)->get(); 
        $this->serv_list = [];
    }

    public function updatedDepId($value) 
    { 
        $this->serv_id = null;
        $this->serv_list = Entity::where('upper_id', $value)->get(); 
    }

    // =========================================================
    //  MÉTHODES EXISTANTES (RESUMÉES POUR LA CLARTÉ)
    // =========================================================
    // (Assurez-vous que ces méthodes contiennent votre logique originale)
    
    public function toggleAdmin($id) {
        $user = User::findOrFail($id);
        if ($user->id !== auth()->id()) {
            $user->update(['is_admin' => !$user->is_admin]);
            $this->dispatch('notify', message: "Droits mis à jour.");
        }
    }

    public function confirmToggleStatus($id) {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) return;
        if ($user->is_active) {
            $this->userToToggleId = $id;
            $this->showDeactivationModal = true;
        } else {
            $user->update(['is_active' => true]);
            $this->dispatch('notify', message: "Compte réactivé.");
        }
    }

    public function processDeactivation() {
        $this->validate(['blocking_reason' => 'required|min:5']);
        User::where('id', $this->userToToggleId)->update(['is_active' => false, 'blocking_reason' => $this->blocking_reason]);
        $this->showDeactivationModal = false;
        $this->dispatch('notify', message: "Compte suspendu.");
    }



    public function openCreateModal() 
    {
        // 1. Reset des variables
        $this->resetValidation();
        $this->reset(['ref', 'name', 'upper_id', 'itemId', 'structureParents']);
        
        // 2. Définition du type en fonction de l'onglet actuel
        $this->type = $this->activeTab; 
        $this->isEditing = false;
        $this->showModal = true;

        // 3. Chargement intelligent du parent requis
        // Si je crée un Service -> Je dois choisir un Département
        // Si je crée une Sous-Direction -> Je dois choisir une Direction
        
        $parentTypeMap = [
            'Sous-Direction' => 'Direction',
            'Departement'    => 'Sous-Direction',
            'Service'        => 'Departement',
        ];

        // Si le type actuel a un parent défini dans la map
        if (array_key_exists($this->activeTab, $parentTypeMap)) {
            $targetParentType = $parentTypeMap[$this->activeTab];
            
            $this->structureParents = Entity::where('type', $targetParentType)
                                            ->orderBy('name', 'asc')
                                            ->get();
        } else {
            // C'est une Direction (Racine), pas de parent
            $this->structureParents = []; 
        }
    }

    public function saveStructure() 
    {
        // 1. Validation Dynamique
        $rules = [
            'ref'  => 'required|string|max:50',
            'name' => 'required|string|max:255',
        ];

        // Si ce n'est pas une Direction, le parent (upper_id) est OBLIGATOIRE
        if ($this->activeTab !== 'Direction') {
            $rules['upper_id'] = 'required|exists:entities,id';
        }

        $messages = [
            'ref.required'      => 'Le code de référence est requis.',
            'upper_id.required' => 'Le rattachement à une structure parente est obligatoire.',
        ];

        $this->validate($rules, $messages);

        // 2. Préparation des données
        $data = [
            'ref'      => $this->ref,
            'name'     => $this->name,
            'type'     => $this->activeTab, // Utilise l'onglet actif (ex: 'Service')
            'upper_id' => $this->upper_id ?: null, // null si c'est une Direction
        ];

        // 3. Exécution (Création ou Mise à jour)
        if ($this->itemId) {
            // Mode ÉDITION
            $entity = Entity::findOrFail($this->itemId);
            $entity->update($data);
            $msg = "Structure mise à jour avec succès.";
        } else {
            // Mode CRÉATION
            Entity::create($data);
            $msg = "Nouvelle structure créée avec succès.";
        }

        // 4. Clôture
        $this->dispatch('notify', message: $msg);
        $this->showModal = false;
        
        // Reset pour éviter les conflits futurs
        $this->reset(['ref', 'name', 'upper_id', 'itemId']);
    }



    public function saveUserProInfo()
    {
        // 1. Validation
        $this->validate([
            'poste'      => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'dir_id'     => 'nullable|exists:entities,id',
            'sd_id'      => 'nullable|exists:entities,id', 
            'dep_id'     => 'nullable|exists:entities,id',
            'serv_id'    => 'nullable|exists:entities,id',
        ]);

        $user = User::findOrFail($this->itemId);

        // 2. Calcul de l'entité "Active" (La plus précise)
        // C'est utile si votre application utilise $user->entity pour savoir où il travaille "au quotidien"
        $mainEntityId = $this->serv_id ?: ($this->dep_id ?: ($this->sd_id ?: $this->dir_id));

        // 3. Mise à jour de toutes les colonnes spécifiques
        $user->update([
            'poste'      => $this->poste,
            'manager_id' => ($this->manager_id && $this->manager_id !== 'null') ? $this->manager_id : null,
            
            // Enregistrement dans les colonnes dédiées
            'dir_id'     => $this->dir_id ?: null,
            'sd_id'      => $this->sd_id ?: null,
            'dep_id'     => $this->dep_id ?: null,
            'serv_id'    => $this->serv_id ?: null,
            
            // On met aussi à jour la colonne générique si elle existe (recommandé pour la relation BelongsTo principale)
            'entity_id'  => $mainEntityId ?: null,
        ]);

        $this->dispatch('notify', message: "Affectation mise à jour avec succès.");
        $this->showModal = false;
    }

    public function openEditModal($id) 
    {
        // Reset complet
        $this->resetValidation();
        $this->reset(['ref', 'name', 'upper_id', 'poste', 'dir_id', 'sd_id', 'dep_id', 'serv_id', 'manager_id', 'directions_list', 'sd_list', 'dep_list', 'serv_list']);
        
        $this->itemId = $id;
        $this->isEditing = true;
        $this->showModal = true;

        if ($this->activeTab === 'users') {
            $user = User::findOrFail($id); // Pas besoin de with('entity') car on a les colonnes
            
            // A. Récupération des données simples
            $this->poste = ($user->poste instanceof \UnitEnum) ? $user->poste->value : $user->poste;
            $this->manager_id = $user->manager_id;

            // B. Récupération des IDs spécifiques depuis la table users
            $this->dir_id  = $user->dir_id;
            $this->sd_id   = $user->sd_id;
            $this->dep_id  = $user->dep_id;
            $this->serv_id = $user->serv_id;

            // C. Chargement des listes en cascade (Logique "Waterfall")
            
            // 1. Toujours charger les Directions
            $this->directions_list = Entity::where('type', 'Direction')->get();

            // 2. Si une Direction est définie, charger ses Sous-Directions
            if ($this->dir_id) {
                $this->sd_list = Entity::where('upper_id', $this->dir_id)->get();
            }

            // 3. Si une Sous-Direction est définie, charger ses Départements
            if ($this->sd_id) {
                $this->dep_list = Entity::where('upper_id', $this->sd_id)->get();
            }

            // 4. Si un Département est défini, charger ses Services
            if ($this->dep_id) {
                $this->serv_list = Entity::where('upper_id', $this->dep_id)->get();
            }

            // D. Chargement des remplacements (inchangé)
            $this->userReplacements = ReplacesUser::where('user_id', $id)
                ->where('date_end_replace', '>=', now())
                ->with('substitute')
                ->get();
        } 
        else {
            // Logique pour l'édition d'une Structure (Entité)
            $entity = Entity::findOrFail($id);
            $this->ref = $entity->ref;
            $this->name = $entity->name;
            $this->upper_id = $entity->upper_id;
            $this->type = $entity->type;
        }
    }

    /**
     * AJOUTER UN REMPLACEMENT
     */
    public function addReplacement()
    {
        // 1. Validation
        $this->validate([
            'replace_user_id' => 'required|exists:users,id',
            'date_begin'      => 'required|date',
            'date_end'        => 'required|date|after_or_equal:date_begin',
            'replace_actions' => 'nullable|array' 
        ], [
            'replace_user_id.required' => 'Le choix d\'un remplaçant est obligatoire.',
            'date_end.after_or_equal'  => 'La date de fin doit être postérieure au début.',
        ]);

        // Sécurité : Impossible de se remplacer soi-même
        if ($this->replace_user_id == $this->itemId) {
            $this->addError('replace_user_id', 'Un agent ne peut pas se remplacer lui-même.');
            return;
        }

        // 2. Création (Mapping avec VOS colonnes)
        ReplacesUser::create([
            'user_id'            => $this->itemId,            // L'agent titulaire
            'user_id_replace'    => $this->replace_user_id,   // Le remplaçant
            'action_replace'     => json_encode($this->replace_actions ?? []), // Conversion tableau -> string JSON
            'date_begin_replace' => $this->date_begin,
            'date_end_replace'   => $this->date_end,
        ]);

        $this->dispatch('notify', message: "Délégation ajoutée avec succès.");

        // 3. Reset du petit formulaire
        $this->reset(['replace_user_id', 'date_begin', 'date_end', 'replace_actions']);

        // 4. Recharger la liste
        $this->refreshReplacementsList();
    }

    /**
     * SUPPRIMER UN REMPLACEMENT
     */
    public function removeReplacement($replacementId)
    {
        $rep = ReplacesUser::find($replacementId);

        if ($rep) {
            $rep->delete();
            $this->dispatch('notify', message: "Délégation supprimée.");
            $this->refreshReplacementsList();
        }
    }

    /**
     * RECHARGER LA LISTE (PRIVÉE)
     */
    private function refreshReplacementsList()
    {
        // On récupère les remplacements dont la date de fin est >= aujourd'hui (ou tout l'historique selon votre choix)
        // Note: Comme vos dates sont en 'string' dans la DB, la comparaison >= now() peut être capricieuse si le format n'est pas YYYY-MM-DD.
        // On charge tout pour l'instant pour éviter les bugs de format string.
        
        $this->userReplacements = ReplacesUser::where('user_id', $this->itemId)
            ->with('substitute') // Utilise la relation définie à l'étape 1
            ->orderBy('date_begin_replace', 'desc')
            ->get();
    }

    

    
    public function confirmDeleteStructure($id) { $this->structureToDeleteId = $id; $this->showDeleteModal = true; }
    public function deleteStructure() { Entity::destroy($this->structureToDeleteId); $this->showDeleteModal = false; }
    
    public function openAuditDetails($id) { 
        $this->viewingMemoId = $id; 
        $this->selectedMemo = Memo::findOrFail($id); 
    }
    public function closeAuditDetails() { $this->reset(['viewingMemoId', 'selectedMemo']); }

    // =========================================================
    //  LOGIQUE WORKFLOW (CORRIGÉE)
    // =========================================================

    public function openWorkflowIntervention($memoId)
    {
        $this->selectedMemo = Memo::findOrFail($memoId);
        $this->transfer_reason = '';
        $this->target_user_id = ''; 

        // 1. Récupération de l'historique (Passé) et des détenteurs (Présent)
        // On s'assure que ce sont des tableaux (Laravel cast JSON automatiquement si configuré, sinon json_decode)
        $previous = $this->selectedMemo->previous_holders ?? [];
        $current = $this->selectedMemo->current_holders ?? [];

        // Si c'est du JSON brut (string), on décode, sinon on utilise tel quel
        if (is_string($previous)) $previous = json_decode($previous, true);
        if (is_string($current)) $current = json_decode($current, true);
        if (!is_array($previous)) $previous = [];
        if (!is_array($current)) $current = [];

        // 2. Fusion pour créer la Ligne de Vie (Ordre Chronologique)
        // On fusionne pour avoir : [User A, User B, User C (Actuel)]
        $timelineIds = array_merge($previous, $current);
        
        // On enlève les doublons consécutifs si nécessaire, ou on garde tout pour voir les allers-retours
        // Ici on garde les IDs uniques pour simplifier la règle visuelle
        $timelineIds = array_unique($timelineIds);

        // 3. Chargement des Users
        if (!empty($timelineIds)) {
            $users = User::with('entity')->whereIn('id', $timelineIds)->get()->keyBy('id');
            
            // On mappe dans l'ordre de la timeline
            $this->circuitUsers = collect($timelineIds)->map(function($id) use ($users) {
                return $users[$id] ?? null;
            })->filter();
        } else {
            $this->circuitUsers = collect([]);
        }

        $this->showWorkflowModal = true;
    }

    /**
     * INTERVENTION RÈGLE (Timeline)
     * Logique Adaptative : 
     * - Entrant : Mode Collaboratif (Ajout au groupe de traitement)
     * - Sortant : Mode Linéaire (Transfert de responsabilité)
     */

    public function jumpToStep($userId)
    {
        $targetUser = User::find($userId);
        if (!$targetUser) return;

        // 1. Décodage sécurisé
        $currents = $this->selectedMemo->current_holders ?? [];
        $treatments = $this->selectedMemo->treatment_holders ?? [];

        if(is_string($currents)) $currents = json_decode($currents, true) ?? [];
        if(is_string($treatments)) $treatments = json_decode($treatments, true) ?? [];

        // 2. Détection du contexte
        $isEntrant = strtolower($this->selectedMemo->workflow_direction) === 'entrant';
        $alreadyHasHand = in_array($userId, $treatments);
        $actionType = ''; // 'GRANT' ou 'REVOKE'

        // 3. Logique TOGGLE (Interrupteur)
        if ($alreadyHasHand) {
            // --- CAS RÉVOCATION (On retire la main) ---
            // On enlève l'ID du tableau treatments
            $treatments = array_diff($treatments, [$userId]);
            $actionType = 'REVOKE';
            
            // Note : On le laisse dans $currents (il garde la vue/copie du dossier), 
            // on lui retire juste le droit d'action.
        } 
        else {
            // --- CAS ATTRIBUTION (On donne la main) ---
            $actionType = 'GRANT';

            if ($isEntrant) {
                // Mode Collaboratif : On ajoute à la liste
                $treatments[] = $userId;
            } else {
                // Mode Exclusif (Sortant) : On remplace tout le monde par lui seul
                $treatments = [$userId];
            }

            // Si on donne la main, on doit s'assurer qu'il a le dossier (visibilité)
            if (!in_array($userId, $currents)) {
                $currents[] = $userId;
            }
        }

        // 4. Update en Base
        // array_values est important pour réindexer les clés après un array_diff
        $this->selectedMemo->update([
            'current_holders' => array_values(array_unique($currents)),
            'treatment_holders' => array_values(array_unique($treatments)),
        ]);

        // 5. Historique & Notification
        if ($actionType === 'REVOKE') {
            Historiques::create([
                'workflow_comment' => "RÉVOCATION DROITS TRAITEMENT",
                'visa' => 'ADMIN_REVOKE',
                'user_id' => auth()->id(),
                'memo_id' => $this->selectedMemo->id,
            ]);
            // Optionnel : Notifier de la révocation
        } else {
            Historiques::create([
                'workflow_comment' => "ATTRIBUTION DROITS TRAITEMENT",
                'visa' => 'ADMIN_GRANT',
                'user_id' => auth()->id(),
                'memo_id' => $this->selectedMemo->id,
            ]);

            try {
                $msg = "L'administrateur vous a attribué le traitement du dossier.";
                $targetUser->notify(new AdminNotification('workflow', auth()->user(), $targetUser, $msg));
            } catch (\Exception $e) {}
        }

        // Feedback UI
        $msgNotify = ($actionType === 'REVOKE') 
            ? "Droit de traitement retiré à " . $targetUser->last_name 
            : "Droit de traitement donné à " . $targetUser->last_name;

        $this->dispatch('notify', message: $msgNotify);
        
        // Rafraîchir la modale
        $this->openWorkflowIntervention($this->selectedMemo->id);
    }

    /**
     * INTERVENTION MANUELLE (Select)
     * Même logique adaptative
     */
    public function forceWorkflowTransfer()
    {
        $this->validate([
            'target_user_id' => 'required|exists:users,id',
            'transfer_reason' => 'required|string|min:5'
        ]);

        $userId = (int)$this->target_user_id;

        // 1. Décodage
        $currents = $this->selectedMemo->current_holders ?? [];
        $treatments = $this->selectedMemo->treatment_holders ?? [];
        if(is_string($currents)) $currents = json_decode($currents, true) ?? [];
        if(is_string($treatments)) $treatments = json_decode($treatments, true) ?? [];

        // 2. Mode Entrant ou Standard
        $isEntrant = strtolower($this->selectedMemo->workflow_direction) === 'entrant';

        if ($isEntrant) {
            // Ajout (Collaboratif)
            if (!in_array($userId, $treatments)) {
                $treatments[] = $userId;
            }
        } else {
            // Remplacement (Linéaire)
            $treatments = [$userId];
        }

        // 3. Ajout aux détenteurs
        if (!in_array($userId, $currents)) {
            $currents[] = $userId;
        }

        // 4. Update
        $this->selectedMemo->update([
            'current_holders' => array_values(array_unique($currents)),
            'treatment_holders' => array_values(array_unique($treatments)),
        ]);

        Historiques::create([
            'workflow_comment' => "INTERVENTION MANUELLE : " . $this->transfer_reason,
            'visa' => 'SUPER_ADMIN',
            'user_id' => auth()->id(),
            'memo_id' => $this->selectedMemo->id,
        ]);

        $newUser = User::find($userId);
        if($newUser) {
            try {
                $newUser->notify(new AdminNotification('workflow', auth()->user(), $newUser, "Intervention administrative sur le dossier."));
            } catch (\Exception $e) {}
        }

        $this->showWorkflowModal = false;
        $this->dispatch('notify', message: "Intervention effectuée avec succès.");
    }


    // --- VARIABLES POUR L'AJOUT RAPIDE ---
    public $userToAddId; // L'ID de l'user à ajouter
    public $isAddingToTimeline = false; // Pour afficher/masquer le petit formulaire

    /**
     * RETIRER UN USER DU CIRCUIT ACTUEL
     */
    public function removeUserFromLoop($userId)
    {
        // 1. Décodage
        $currents = $this->selectedMemo->current_holders ?? [];
        $treatments = $this->selectedMemo->treatment_holders ?? [];
        
        if(is_string($currents)) $currents = json_decode($currents, true) ?? [];
        if(is_string($treatments)) $treatments = json_decode($treatments, true) ?? [];

        // 2. Suppression des listes actives
        $currents = array_values(array_diff($currents, [$userId]));
        $treatments = array_values(array_diff($treatments, [$userId]));

        // 3. Update
        $this->selectedMemo->update([
            'current_holders' => $currents,
            'treatment_holders' => $treatments,
        ]);

        // 4. Historique
        Historiques::create([
            'workflow_comment' => "RETRAIT ADMINISTRATIF DU CIRCUIT",
            'visa' => 'ADMIN_REMOVE',
            'user_id' => auth()->id(),
            'memo_id' => $this->selectedMemo->id,
        ]);

        $this->dispatch('notify', message: "Utilisateur retiré du flux actif.");
        $this->openWorkflowIntervention($this->selectedMemo->id); // Rafraîchir
    }

    /**
     * AJOUTER UN USER AU CIRCUIT ACTUEL
     */
    public function addUserToLoop()
    {
        $this->validate(['userToAddId' => 'required|exists:users,id']);
        
        // 1. Décodage
        $currents = $this->selectedMemo->current_holders ?? [];
        if(is_string($currents)) $currents = json_decode($currents, true) ?? [];

        // 2. Ajout (Uniquement s'il n'est pas déjà là)
        if (!in_array($this->userToAddId, $currents)) {
            $currents[] = (int)$this->userToAddId;
            
            // Note : On l'ajoute comme "Détenteur" (Yeux bleus). 
            // Si on veut lui donner la main tout de suite, il faudra cliquer sur son nœud après l'ajout.
            
            $this->selectedMemo->update([
                'current_holders' => array_values($currents)
            ]);

            Historiques::create([
                'workflow_comment' => "INJECTION DANS LE CIRCUIT",
                'visa' => 'ADMIN_ADD',
                'user_id' => auth()->id(),
                'memo_id' => $this->selectedMemo->id,
            ]);

            // Notif
            $target = User::find($this->userToAddId);
            if($target) {
                try {
                    $target->notify(new AdminNotification('workflow', auth()->user(), $target, "Vous avez été ajouté au circuit du dossier."));
                } catch (\Exception $e) {}
            }

            $this->dispatch('notify', message: "Utilisateur ajouté au flux.");
        }

        // Reset
        $this->reset(['userToAddId', 'isAddingToTimeline']);
        $this->openWorkflowIntervention($this->selectedMemo->id); // Rafraîchir
    }

    
    
    public function render()
    {
        $searchTerm = '%' . $this->search . '%';
        $stats = [];
        $data = [];

        if ($this->activeTab === 'users') {
            $data = User::with('dir')
                ->where('last_name', 'like', $searchTerm)
                ->orWhere('email', 'like', $searchTerm)
                ->latest()->paginate(10);
        } elseif ($this->activeTab === 'audit') {
            $data = Memo::with('user')->where('object', 'like', $searchTerm)->latest()->paginate(10);
        } elseif ($this->activeTab === 'workflow') {
            // Correction de la requête pour inclure historique et users
            $data = Memo::with(['user', 'historiques'])
                ->where('status', '!=', 'brouillon')
                ->where(function($q) use ($searchTerm) {
                    $q->where('object', 'like', $searchTerm)
                      ->orWhere('reference', 'like', $searchTerm);
                })->latest()->paginate(10);
        } else {
            $data = Entity::where('type', $this->activeTab)
                ->where(function($q) use ($searchTerm) { 
                    $q->where('name', 'like', $searchTerm)->orWhere('ref', 'like', $searchTerm); 
                })->latest()->paginate(10);
        }

        return view('livewire.setting.settings', ['data' => $data, 'stats' => $stats]);
    }
}