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
use App\Models\BlocEnregistrements;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;   
use App\Notifications\MemoActionNotification;
use App\Traits\ManageFavorites; 



class IncomingMemos extends Component
{
    use ManageFavorites;

    // --- RECHERCHE & DATATABLE ---
    public $search = '';
    

     // --- VARIABLES MODAL ASSIGNATION ---
    public $memo_type = 'standard'; // 'standard' ou 'projet'

    // Structure des données pour l'affichage : ['original' => User, 'effective' => User, 'is_replaced' => bool]
    public $managerData = null; 

    // Liste des utilisateurs éligibles pour le mode projet (Excluant Auth et N+1)
    public $projectUsersList = []; 

    // IDs sélectionnés en mode projet
    public $selected_project_users = [];

    // --- VARIABLES MODAL TRANSMISSION (SECRÉTAIRE) ---
    public $isOpenTrans = false;
    public $transRecipients = []; // Liste des secrétaires destinataires
    public $generatedReference = ''; // Pour stocker la ref calculée avant enregistrement (optionnel)

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
        $memo = Memo::find($id); // On récupère le mémo pour l'analyse
        
        // Réinitialisation
        $this->reset(['workflow_comment', 'selected_visa', 'selected_project_users']);
        $this->memo_type = 'standard'; 

        $currentUser = Auth::user();
        $targetUser = null;

        // 1. ANALYSE : SUIS-JE REMPLAÇANT SUR CE MÉMO ?
        $replacementContext = $this->getReplacementRights($memo);

        // =================================================================
        // SCÉNARIO A : JE SUIS REMPLAÇANT (J'agis au nom du Titulaire)
        // =================================================================
        if ($replacementContext && $replacementContext['is_active']) {
            
            $titulaire = $replacementContext['original_user']; // Ex: Le Directeur que je remplace

            // Règle 1 : Si je remplace un DIRECTEUR -> Envoi à la Secrétaire
            if ($titulaire->poste === 'Directeur') {
                $targetUser = User::where('entity_id', $titulaire->entity_id)
                                ->where('poste', 'Secretaire')
                                ->first();
                
                if (!$targetUser) {
                    $this->addError('general', "Aucune secrétaire trouvée pour le Directeur remplacé.");
                }
            } 
            // Règle 2 : Si je remplace un autre poste -> Envoi au Manager du TITULAIRE
            else {
                if ($titulaire->manager_id) {
                    $targetUser = User::find($titulaire->manager_id);
                } else {
                    $this->addError('general', "Le titulaire remplacé n'a pas de manager défini.");
                }
            }

        } 
        // =================================================================
        // SCÉNARIO B : JE SUIS LE TITULAIRE (Comportement standard)
        // =================================================================
        else {
            
            // Règle 1 : Je suis DIRECTEUR -> Envoi à ma Secrétaire
            if ($currentUser->poste === 'Directeur') {
                $targetUser = User::where('entity_id', $currentUser->entity_id)
                                ->where('poste', 'Secretaire')
                                ->first();
                
                if (!$targetUser) {
                    $this->addError('general', "Aucune secrétaire trouvée dans votre entité.");
                }
            } 
            // Règle 2 : Je suis EMPLOYÉ/SD -> Envoi à MON Manager
            else {
                if ($currentUser->manager_id) {
                    $targetUser = User::find($currentUser->manager_id);
                }
            }
        }

        // =================================================================
        // RÉSOLUTION DE DISPONIBILITÉ (Le destinataire est-il lui-même remplacé ?)
        // =================================================================
        // Exemple : Je remplace le SD, j'envoie au Directeur. 
        // Mais si le Directeur est aussi absent ? Cette fonction va trouver SON remplaçant.
        
        if ($targetUser) {
            $this->managerData = $this->resolveUserAvailability($targetUser);
        } else {
            $this->managerData = null;
        }

        // Préparation liste projet (inchangé, sauf qu'on exclut le titulaire si remplacement)
        $excludeIds = [$currentUser->id];
        if ($replacementContext) {
            $excludeIds[] = $replacementContext['original_user']->id;
        }
        
        $this->projectUsersList = User::whereNotIn('id', $excludeIds)
                                    ->orderBy('last_name')
                                    ->get()
                                    ->map(function($user) {
                                        return $this->resolveUserAvailability($user);
                                    });

        $this->isOpen3 = true;
    }


    /**
     * Helper pour vérifier si un user est remplacé aujourd'hui
     * Retourne un tableau structuré
     */
    private function resolveUserAvailability($user)
    {
        // Sécurité : si aucun utilisateur n'est passé (ex: pas de manager), on renvoie null
        if (!$user) return null;

        // 1. On récupère la date du jour
        // IMPORTANT : Le format doit être identique à ce qu'il y a dans ta DB (Y-m-d)
        $today = Carbon::now()->format('Y-m-d'); 

        // 2. La requête vérifie l'intervalle [Debut ..... AUJOURD'HUI ..... Fin]
        $replacement = ReplacesUser::where('user_id', $user->id) // On cherche si cet user est remplacé
            
            // La date de début doit être passée ou être aujourd'hui
            // (Ex: Si début = 01/12 et on est le 06/12 => "2025-12-01" <= "2025-12-06") -> VRAI
            ->where('date_begin_replace', '<=', $today)
            
            // ET la date de fin doit être dans le futur ou aujourd'hui
            // (Ex: Si fin = 31/12 et on est le 06/12 => "2025-12-31" >= "2025-12-06") -> VRAI
            ->where('date_end_replace', '>=', $today)
            
            ->first();

        // 3. Traitement du résultat
        if ($replacement) {
            // --- CAS : ON EST DANS LA PÉRIODE DE REMPLACEMENT ---
            
            // On cherche le user remplaçant
            $replacingUser = User::find($replacement->user_id_replace);

            // Sécurité supplémentaire : Si le compte du remplaçant a été supprimé entre temps
            if ($replacingUser) {
                return [
                    'original' => $user,           // L'utilisateur de base (Absent)
                    'effective' => $replacingUser, // Le remplaçant (Présent)
                    'is_replaced' => true
                ];
            }
        }

        // --- CAS : PAS DE REMPLACEMENT OU HORS PÉRIODE ---
        return [
            'original' => $user,
            'effective' => $user, // L'utilisateur reste lui-même
            'is_replaced' => false
        ];
    }


    // =========================================================================
    // 3. ADAPTATION DE L'ACTION : VISER / ENVOYER
    // =========================================================================

    public function sendMemo()
    {
        // 1. Validation du formulaire
        $this->validate([
            'selected_visa' => 'required',
            'workflow_comment' => 'nullable|string|max:1000',
            // En mode projet, on doit avoir sélectionné des gens
            'selected_project_users' => 'required_if:memo_type,projet|array',
        ], [
            'selected_project_users.required_if' => 'En mode projet, veuillez sélectionner au moins un collaborateur.'
        ]);

        $memo = Memo::find($this->memo_id);
        $user = Auth::user();
        
        // 2. Gestion du commentaire "P/O" (Pour Ordre)
        $replacementContext = $this->getReplacementRights($memo);
        $finalComment = $this->workflow_comment;

        if ($replacementContext && in_array('viser', $replacementContext['actions_allowed'])) {
            $titulaire = $replacementContext['original_user'];
            // On ajoute la mention P/O au commentaire
            $finalComment = "[P/O " . $titulaire->poste . "] " . $this->workflow_comment;
        }

        // 3. CALCUL DES DESTINATAIRES (C'est ici que ça manquait)
        $nextHolders = [];

        // --- SCENARIO A : CIRCUIT STANDARD (Vers le Manager affiché dans le modal) ---
        if ($this->memo_type === 'standard') {
            
            // On vérifie si managerData est bien rempli (calculé lors du assignMemo)
            if ($this->managerData && isset($this->managerData['effective'])) {
                // On envoie à l'utilisateur effectif (le manager ou son remplaçant)
                $nextHolders[] = $this->managerData['effective']->id;
            } else {
                // Sécurité : Si les données ont été perdues, on tente de recalculer ou on bloque
                $this->addError('general', "Erreur : Le destinataire n'est pas défini. Veuillez fermer et rouvrir le modal.");
                return;
            }
        }

        // --- SCENARIO B : CIRCUIT PROJET (Vers les collaborateurs sélectionnés) ---
        if ($this->memo_type === 'projet') {
            foreach ($this->selected_project_users as $userId) {
                $targetUser = User::find($userId);
                if ($targetUser) {
                    // On vérifie la disponibilité de chaque destinataire projet
                    $availability = $this->resolveUserAvailability($targetUser);
                    if ($availability && $availability['effective']) {
                        $nextHolders[] = $availability['effective']->id;
                    }
                }
            }
        }

        // 4. Vérification finale
        if (empty($nextHolders)) {
            $this->addError('general', 'Aucun destinataire valide trouvé pour la transmission.');
            return;
        }

        // 5. Mise à jour du Mémo en Base de Données
        // Important : previous_holders stocke qui a tenu le mémo avant.
        // Si je suis remplaçant, techniquement c'est moi ($user->id) qui transmet.
        
        // On fusionne l'historique existant ou on écrase selon votre logique métier.
        // Ici, on écrase pour dire "Le mémo vient de MOI".
        $memo->previous_holders = [$user->id]; 
        
        $memo->current_holders = array_unique($nextHolders); // Les nouveaux détenteurs
        $memo->status = 'envoyer'; 
        $memo->workflow_direction = 'sortant'; // S'assure que ça reste sortant
        $memo->workflow_comment = $finalComment; 
        
        $memo->save();

        // 6. Enregistrement dans l'historique (Timeline)
        Historiques::create([
            'user_id' => $user->id,
            'memo_id' => $memo->id,
            'visa'    => $this->selected_visa,
            'workflow_comment' => $finalComment ?? 'R.A.S',
        ]);

        // 7. Envoi des Notifications Laravel
        $usersToNotify = User::whereIn('id', $nextHolders)->get();

        foreach ($usersToNotify as $recipient) {
            // $recipient : C'est la personne qui reçoit (ex: Secrétaire de direction)
            // 'envoyer' : L'action effectuée
            // $user : L'acteur (Moi)
            try {
                $recipient->notify(new MemoActionNotification($memo, 'envoyer', $user));
            } catch (\Exception $e) {
                // On évite de planter tout le processus si l'envoi de mail échoue
                // Log::error("Erreur notification : " . $e->getMessage());
            }
        }

        // 8. Fermeture et Feedback
        $this->closeModalTrois();
        $this->dispatch('notify', message: "Le mémo a été transmis avec succès.");
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
    // 1. VALIDATION (Indispensable)
    $this->validate([
        'reject_comment' => 'required|string|min:5|max:500',
    ], [
        'reject_comment.required' => 'Le motif du rejet est obligatoire.',
        'reject_comment.min' => 'Le motif doit être explicite (min 5 caractères).',
    ]);

    $memo = Memo::findOrFail($this->memo_id);
    $user = Auth::user();

    // 2. GESTION DES DROITS (Remplaçant ou Titulaire)
    $replacementContext = $this->getReplacementRights($memo);
    $finalReason = $this->reject_comment;

    if ($replacementContext) {
        if (in_array('rejeter', $replacementContext['actions_allowed'])) {
            $titulaire = $replacementContext['original_user'];
            $finalReason = "[REJET P/O " . $titulaire->poste . "] " . $this->reject_comment;
        } else {
             $this->addError('reject_comment', "Droit insuffisant : Vous ne pouvez pas rejeter ce document en tant que remplaçant.");
             return;
        }
    }

    // 3. LOGIQUE DE RENVOI (Retour à l'envoyeur)
    // On récupère qui nous a envoyé le mémo pour lui renvoyer
    $previousHolders = is_string($memo->previous_holders) ? json_decode($memo->previous_holders, true) : $memo->previous_holders;
    
    // Si historique vide, on renvoie au créateur initial
    $backToUserId = !empty($previousHolders) ? end($previousHolders) : $memo->user_id;

    // Mise à jour du mémo
    $memo->update([
        'status' => 'rejeter',               // Statut explicite
        'workflow_direction' => 'sortant',   // Pour le filtrage visuel
        'workflow_comment' => $finalReason,
        'current_holders' => [$backToUserId], // Le mémo quitte votre liste et retourne à l'autre
        'previous_holders' => [$user->id],    // Vous devenez le précédent détenteur
    ]);

    // 4. HISTORIQUE
    Historiques::create([
        'user_id' => $user->id,
        'memo_id' => $memo->id,
        'visa' => 'Rejeté',
        'workflow_comment' => $finalReason,
    ]);

    // 5. NOTIFICATION (Optionnel)
    $receiver = User::find($backToUserId);
    if ($receiver) {
        try {
            $receiver->notify(new MemoActionNotification($memo, 'rejeter', $user));
        } catch (\Exception $e) {}
    }

    // 6. FERMETURE ET MESSAGE (C'est ce qui manquait pour l'UX)
    $this->closeRejectModal();
    $this->dispatch('notify', message: "Le mémo a été rejeté et renvoyé à l'expéditeur.");
}

    public function closeModal() { $this->isOpen = false; }


    

    // =========================================================================
    // 2. ADAPTATION DE L'ACTION : SIGNER
    // =========================================================================

    public function sign($id)
    {
        $memo = Memo::findOrFail($id);
        $user = Auth::user();
        
        // On récupère le contexte de remplacement
        $replacementContext = $this->getReplacementRights($memo);
        
        // Token de base
        $randomString = Str::upper(Str::random(10));
        $tokenBase = now()->timestamp . "-{$randomString}";
        
        $message = "";
        $historyData = "";
        $posteSignataire = $user->poste; // Par défaut, mon poste

        // --- CAS 1 : JE SUIS REMPLAÇANT ---
        if ($replacementContext && in_array('signer', $replacementContext['actions_allowed'])) {
            
            $titulaire = $replacementContext['original_user'];
            $posteSignataire = $titulaire->poste; // On signe "en tant que" (virtuellement)

            // Logique selon le poste du TITULAIRE
            if ($titulaire->poste === 'Sous-Directeur') {
                if ($memo->signature_sd) { $this->dispatch('notify', message: "Déjà signé."); return; }
                $memo->signature_sd = 'SD-INT-' . $tokenBase;
                $message = "Signature Sous-Directeur (P/O) apposée.";
            } 
            elseif ($titulaire->poste === 'Directeur') {
                if ($memo->signature_dir) { $this->dispatch('notify', message: "Déjà signé."); return; }
                $memo->signature_dir = 'DIR-INT-' . $tokenBase;
                $memo->qr_code = (string) Str::uuid();
                $memo->status = 'valider';
                $message = "Signature Directeur (P/O) apposée.";
            }

            // Commentaire spécial P/O
            $historyData = "Signature P/O (Pour Ordre) de M./Mme {$titulaire->last_name} ({$titulaire->poste}) par {$user->first_name} {$user->last_name}";

        } 
        // --- CAS 2 : JE SUIS LE TITULAIRE (Standard) ---
        else {
             // ... Ta logique standard existante ...
             if ($user->poste === 'Sous-Directeur') {
                // ...
                $memo->signature_sd = 'SD-' . $tokenBase;
                $message = "Signature Sous-Directeur apposée.";
                $historyData = "Signature Sous-Directeur : " . $memo->signature_sd;
             } elseif ($user->poste === 'Directeur') {
                // ...
                $memo->signature_dir = 'DIR-' . $tokenBase;
                $memo->qr_code = (string) Str::uuid();
                $memo->status = 'valider';
                $message = "Signature Directeur apposée.";
                $historyData = "Signature Directeur Finale : " . $memo->signature_dir;
             } else {
                 $this->dispatch('notify', message: "Vous n'avez pas les droits de signature pour ce document.");
                 return;
             }
        }

        $memo->save();

        Historiques::create([
            'user_id' => $user->id,
            'memo_id' => $memo->id,
            'visa'    => 'Signé',
            'workflow_comment' => $historyData, // C'est ici que le texte P/O est sauvegardé pour le QR Code
        ]);

        $this->dispatch('notify', message: $message);
    }





    // 1. OUVRIR LE MODAL ET CALCULER LES DESTINATAIRES
    // 2. MODIFICATION DE L'OUVERTURE DU MODAL
    public function transMemo($id)
    {
        $this->memo_id = $id;
        $memo = Memo::with('destinataires')->findOrFail($id);
    
        // ... (Logique de recherche des destinataires inchangée) ...
        $targetEntityIds = $memo->destinataires->pluck('entity_id')->toArray();
        $rawSecretaries = User::whereIn('entity_id', $targetEntityIds)
                            ->where('poste', 'Secretaire') 
                            ->get();

        $this->transRecipients = $rawSecretaries->map(function($user) {
            return $this->resolveUserAvailability($user);
        });

        if ($this->transRecipients->isEmpty()) {
            $this->dispatch('notify', message: "Erreur : Aucune secrétaire trouvée...");
            return; 
        }

        // --- NOUVEAU : On pré-génère la référence ICI et on la stocke dans la variable publique ---
        $this->generatedReference = $this->generateSmartReference($memo);

        $this->isOpenTrans = true;
    }

    public function closeTransModal()
    {
        $this->isOpenTrans = false;
        $this->transRecipients = [];
    }

    // 2. EXÉCUTION DE LA TRANSMISSION
    public function confirmTrans()
{
    // 1. Validation
    $this->validate([
        'generatedReference' => 'required|string|max:50'
    ]);

    $memo = Memo::findOrFail($this->memo_id);
    $currentUser = Auth::user();

    // --- CORRECTION ICI ---
    // On calcule les IDs AVANT la transaction pour que la variable existe partout
    $nextHoldersIds = $this->transRecipients->pluck('effective.id')->unique()->toArray();
    // ----------------------

    // 2. Transaction Base de Données
    // On passe $nextHoldersIds à la fonction via 'use'
    DB::transaction(function () use ($memo, $currentUser, $nextHoldersIds) {
        
        $finalReference = $this->generatedReference;
        
        // A. Enregistrement dans le chrono
        BlocEnregistrements::create([
            'nature_memo' => 'Memo Sortant',
            'date_enreg' => now()->format('d/m/Y'),
            'reference' => $finalReference,
            'memo_id' => $memo->id,
            'user_id' => $currentUser->id,
        ]);

        // B. Mise à jour du mémo
        $memo->update([
            'workflow_direction' => 'entrant', 
            'workflow_comment' => "Enregistré sous n° " . $finalReference,
            'current_holders' => $nextHoldersIds, // On utilise la variable calculée plus haut
            'previous_holders' => [$currentUser->id], 
            'status' => 'transmit',
            'reference' => $finalReference,
        ]);

        // C. Historique
        Historiques::create([
            'user_id' => $currentUser->id,
            'memo_id' => $memo->id,
            'visa' => 'Enregistré & Transmis',
            'workflow_comment' => "Réf: " . $finalReference,
        ]);
    });

    // ====================================================================
    // 3. GESTION DES NOTIFICATIONS
    // ====================================================================

    // --- GROUPE A : LES DESTINATAIRES ---
    // La variable $nextHoldersIds est maintenant reconnue ici !
    $destinataires = User::whereIn('id', $nextHoldersIds)->get();

    foreach ($destinataires as $dest) {
        try {
            $dest->notify(new MemoActionNotification($memo, 'envoyer', $currentUser));
        } catch (\Exception $e) {}
    }

    // --- GROUPE B : LE N+1 (Information) ---
    if ($currentUser->manager_id) {
        $manager = User::find($currentUser->manager_id);
        
        // Vérification disponibilité manager
        $managerDispo = $this->resolveUserAvailability($manager);

        if ($managerDispo && $managerDispo['effective']) {
            $effectiveManager = $managerDispo['effective'];
            
            try {
                $effectiveManager->notify(new MemoActionNotification($memo, 'transmis', $currentUser));
            } catch (\Exception $e) {}
        }
    }

    // ====================================================================

    $this->closeTransModal();
    $this->dispatch('notify', message: "Mémo enregistré (Réf: {$this->generatedReference}) et transmis !");
}
            


    // 3. LOGIQUE INTELLIGENTE DE GÉNÉRATION DE RÉFÉRENCE
    private function generateSmartReference($memo)
    {
        // A. Récupérer l'année en cours
        $currentYear = now()->year;
        
        // B. Récupérer l'entité de la secrétaire connectée (Celle qui enregistre)
        $currentUser = Auth::user();
        $entityId = $currentUser->entity_id;

        // C. Compter les enregistrements DE CETTE ENTITÉ pour CETTE ANNÉE
        // On regarde dans la table blocs_enregistrements, en filtrant par les utilisateurs de la même entité
        $count = BlocEnregistrements::whereYear('created_at', $currentYear)
            ->whereHas('user', function($query) use ($entityId) {
                $query->where('entity_id', $entityId);
            })
            ->count() + 1; // On ajoute 1 pour le prochain numéro
        
        // 2. Données de base du créateur du mémo (Pour les sigles)
        $creator = User::with(['entity', 'sousDirection'])->find($memo->user_id);
        
        // Préparation des segments
        $refEntity = $creator->entity->ref ?? 'ENT';
        $refSD = $creator->sousDirection->ref ?? 'SD'; 
        $refDept = $this->abbreviate($creator->departement);
        $refService = $this->abbreviate($creator->service);
        $userInitials = Str::upper(substr($creator->first_name, 0, 1) . substr($creator->last_name, 0, 1));

        // Récupération des validations pour la logique hiérarchique
        $validations = Historiques::where('memo_id', $memo->id)
                                ->where('visa', 'Vu & Accord')
                                ->pluck('user_id')
                                ->toArray();

        // Construction de la référence (Format N°/Entity...)
        // Note: J'utilise sprintf avec %04d pour avoir "0001", "0012", etc.
        
        $baseRef = "";

        // SCÉNARIO 1 : Le créateur a validé lui-même
        if (in_array($creator->id, $validations)) {
            $baseRef = sprintf("%04dM/%s/%s/%s/%s/%s", $count , $refEntity, $refSD, $refDept, $refService, $userInitials);
        }
        else {
            // ... (Votre logique de remontée hiérarchique existante) ...
            $n1 = $creator->manager_id ? User::find($creator->manager_id) : null;
            $n2 = $n1 && $n1->manager_id ? User::find($n1->manager_id) : null;

            if ($n1 && in_array($n1->id, $validations) && Str::contains($n1->poste, 'Service')) {
                $baseRef = sprintf("%04d/%s/%s/%s/%s", $count, $refEntity, $refSD, $refDept, $refService);
            }
            elseif ($n2 && in_array($n2->id, $validations) && Str::contains($n2->poste, 'Département')) {
                $baseRef = sprintf("%04d/%s/%s/%s", $count, $refEntity, $refSD, $refDept);
            }
            else {
                // Par défaut
                $baseRef = sprintf("%04d/%s/%s", $count, $refEntity, $refSD);
            }
        }

        return $baseRef;
    }


   

    private function abbreviate($string)
    {
        if (empty($string)) return '';

        // 1. Nettoyage préalable
        // Convertir en ASCII pour supprimer les accents (ex: "Département" -> "Departement")
        $cleanString = Str::ascii($string);
        
        // Supprimer les points (ex: "D.R.H" devient "DRH") pour considérer ça comme un seul mot
        $cleanString = str_replace('.', '', $cleanString);

        // Remplacer les apostrophes et tirets par des espaces
        $cleanString = str_replace(["'", "’", "-"], ' ', $cleanString);
        
        // Tout mettre en minuscule
        $cleanString = Str::lower($cleanString);

        // 2. Découpage en mots
        $words = preg_split('/\s+/', $cleanString, -1, PREG_SPLIT_NO_EMPTY);
        
        // --- NOUVELLE LOGIQUE : SI UN SEUL MOT ---
        if (count($words) === 1) {
            $uniqueWord = $words[0];

            // Cas spécifique : Si le mot unique est "departement", on renvoie DP
            if ($uniqueWord === 'departement') {
                return 'DP';
            }

            // Sinon, on considère que c'est déjà un sigle ou un nom propre (ex: "ECONOMAT", "DPTDSI")
            // On retourne le mot complet en majuscule
            return Str::upper($uniqueWord);
        }

        // 3. LOGIQUE CLASSIQUE (SI PLUSIEURS MOTS)
        $ignoredWords = [
            'le', 'la', 'les', 'l', 'un', 'une', 'des', 'du', 'de', 'd',
            'et', 'ou', 'mais', 'donc', 'or', 'ni', 'car',
            'a', 'au', 'aux', 'en', 'par', 'pour', 'sur', 'dans', 'vers', 'avec', 'sans', 'sous', 'chez'
        ];

        $acronym = '';

        foreach ($words as $word) {
            // Ignorer les mots vides
            if (in_array($word, $ignoredWords)) {
                continue;
            }

            // RÈGLE SPÉCIFIQUE : Departement devient DP
            if ($word === 'departement') {
                $acronym .= 'DP';
            }
            // RÈGLE GÉNÉRALE : Première lettre
            else {
                $acronym .= substr($word, 0, 1);
            }
        }

        return Str::upper($acronym);
    }

    // =========================================================================
    // 1. CŒUR DU SYSTÈME : VÉRIFICATION DES DROITS DE REMPLACEMENT
    // =========================================================================

    /**
     * Cette fonction détermine si l'utilisateur courant agit en tant que remplaçant pour ce mémo précis.
     * Elle retourne un tableau avec les infos et les droits, ou null.
     */
    
    public function getReplacementRights($memo)
    {
        $user = Auth::user();
        $today = Carbon::now()->format('Y-m-d');

        // 1. Récupérer le "Précédent Détenteur"
        $previousHolders = is_array($memo->previous_holders) ? $memo->previous_holders : json_decode($memo->previous_holders, true);
        
        if (empty($previousHolders)) {
            return null; 
        }

        $lastSenderId = end($previousHolders);
        $lastSender = User::find($lastSenderId);

        if (!$lastSender) return null;

        // 2. Chercher les remplacements actifs
        $replacements = ReplacesUser::where('user_id_replace', $user->id)
            ->where('date_begin_replace', '<=', $today)
            ->where('date_end_replace', '>=', $today)
            ->get();

        foreach ($replacements as $replacement) {
            $replacedUser = User::find($replacement->user_id);

            if (!$replacedUser) continue;

            $isTarget = false;

            // Vérification : Le manager de l'expéditeur est celui que je remplace
            if ($lastSender->manager_id == $replacedUser->id) {
                $isTarget = true;
            }

            if ($isTarget) {
                
                // --- CORRECTION DE L'ERREUR ICI ---
                $rawActions = $replacement->action_replace;
                $actions = [];

                if (is_array($rawActions)) {
                    // Cas 1 : C'est déjà un tableau (grâce au cast Model)
                    // On s'assure juste que tout est en minuscule
                    $actions = array_map('strtolower', $rawActions);
                } elseif (is_string($rawActions)) {
                    // Cas 2 : C'est une string "viser,signer"
                    $actions = explode(',', str_replace(' ', '', strtolower($rawActions)));
                }
                // ----------------------------------

                return [
                    'is_active' => true,
                    'original_user' => $replacedUser,
                    'actions_allowed' => $actions,
                ];
            }
        }

        return null;
    }

    public function toggleFavorite($memoId)
    {
        $userId = Auth::id();
        
        // On vérifie si le favori existe déjà
        $existingFavorite = \App\Models\Favoris::where('user_id', $userId)
                                            ->where('memo_id', $memoId)
                                            ->first();

        if ($existingFavorite) {
            // S'il existe, on le supprime (retrait des favoris)
            $existingFavorite->delete();
            $this->dispatch('notify', message: "Retiré des favoris.");
        } else {
            // S'il n'existe pas, on le crée
            \App\Models\Favoris::create([
                'user_id' => $userId,
                'memo_id' => $memoId
            ]);
            $this->dispatch('notify', message: "Ajouté aux favoris !");
        }
    }
    
    
     public function render()
    {
        $userId = Auth::id(); 

        $memos = Memo::with(['user', 'destinataires.entity'])
            ->where('workflow_direction', 'sortant')
            ->whereNotIn('status', ['transmit', 'rejet', 'rejeter']) 
            ->whereJsonContains('current_holders', $userId)
            
            // 3. OPTIMISATION : On ajoute un attribut booléen 'is_favorited'
            // Cela vérifie si l'ID de l'utilisateur est dans la table favoris pour ce mémo
            ->withExists(['favoritedBy as is_favorited' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            
            ->where(function($query) {
                $query->where('object', 'like', '%'.$this->search.'%')
                    ->orWhere('concern', 'like', '%'.$this->search.'%');
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(9);

        return view('livewire.memos.incoming-memos', [
            'memos' => $memos,
        ]); 
    }
}