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



class IncomingMemos extends Component
{
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
        
        // Réinitialisation des champs du formulaire
        $this->reset(['workflow_comment', 'selected_visa', 'selected_project_users']);
        $this->memo_type = 'standard'; // On force le mode standard pour l'affichage de la carte unique

        $currentUser = Auth::user();
        $targetUser = null;

        // --- LOGIQUE DE DÉTERMINATION DU DESTINATAIRE ---

        if ($currentUser->poste === 'Directeur') {
            // CAS 1 : C'est un DIRECTEUR
            // La règle : Envoyer à la "Secretaire" de la même entité
            
            $targetUser = User::where('entity_id', $currentUser->entity_id)
                            ->where('poste', 'Secretaire') // Assure-toi que c'est bien écrit comme ça en base
                            ->first();
                            
            // Petit gestion d'erreur si pas de secrétaire trouvée
            if (!$targetUser) {
                $this->addError('general', "Aucune secrétaire n'a été trouvée dans votre entité.");
            }

        } else {
            // CAS 2 : C'est un EMPLOYÉ LAMBDA ou SOUS-DIRECTEUR
            // La règle : Envoyer au Manager (N+1) défini dans la table users
            
            if ($currentUser->manager_id) {
                $targetUser = User::find($currentUser->manager_id);
            }
        }

        // --- GESTION DU REMPLACEMENT (Commun à tous) ---
        // On utilise ta super fonction qui vérifie les dates et la table replaces_users
        if ($targetUser) {
            $this->managerData = $this->resolveUserAvailability($targetUser);
        } else {
            $this->managerData = null;
        }

        // 2. PRÉPARATION LISTE PROJET (Optionnel, inchangé)
        $excludeIds = [$currentUser->id];
        if ($this->managerData) {
            $excludeIds[] = $this->managerData['original']->id;
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


    public function sendMemo()
    {
        $this->validate([
            'selected_visa' => 'required',
            'workflow_comment' => 'nullable|string|max:1000',
            // Si projet, il faut au moins un destinataire
            'selected_project_users' => 'required_if:memo_type,projet|array',
        ], [
            'selected_project_users.required_if' => 'En mode projet, veuillez sélectionner au moins un collaborateur.'
        ]);

        $memo = Memo::find($this->memo_id);
        $currentUser = Auth::user();
        $nextHolders = [];

        // --- SCENARIO A : STANDARD (Envoi au N+1 ou son remplaçant) ---
        if ($this->memo_type === 'standard') {
            if ($this->managerData) {
                // On envoie à l'utilisateur effectif (le remplaçant s'il existe, sinon le manager)
                $nextHolders[] = $this->managerData['effective']->id;
            } else {
                $this->addError('general', "Vous n'avez pas de supérieur hiérarchique défini.");
                return;
            }
        }

        // --- SCENARIO B : PROJET (Envoi aux collaborateur sélectionnés ou leurs remplaçants) ---
        if ($this->memo_type === 'projet') {
            foreach ($this->selected_project_users as $userId) {
                // On revérifie la disponibilité au moment de l'envoi (sécurité)
                $targetUser = User::find($userId);
                $availability = $this->resolveUserAvailability($targetUser);
                
                if ($availability['effective']) {
                    $nextHolders[] = $availability['effective']->id;
                }
            }
        }

        if (empty($nextHolders)) {
            $this->addError('general', 'Aucun destinataire valide trouvé.');
            return;
        }

        // Sauvegarde DB
        $memo->previous_holders = [$currentUser->id];
        $memo->current_holders = array_unique($nextHolders); // Éviter doublons
        $memo->status = 'envoyer'; 
        $memo->workflow_comment = $this->workflow_comment; 
        // Optionnel : Sauvegarder le type de mémo si vous avez ajouté la colonne en base
        // $memo->type = $this->memo_type; 
        
        $memo->save();

        Historiques::create([
            'user_id' => $currentUser->id,
            'memo_id' => $memo->id,
            'visa'    => $this->selected_visa,
            'workflow_comment' => $this->workflow_comment ?? 'R.A.S',
        ]);

        $this->closeModalTrois();
        $this->dispatch('notify', message: "Le mémo ($this->memo_type) a été envoyé avec succès.");
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

         // ====================================================================
        // GESTION DES NOTIFICATIONS (Basée sur current_holders / nextHolders)
        // ====================================================================
        
        $creator = $memo->user; // Assure-toi que la relation 'user' existe dans ton modèle Memo
        // Sinon: $creator = User::find($memo->user_id);

        if ($creator) {
            // 2. On envoie la notification
            // Paramètres : Le mémo, le type d'action, et l'acteur (Moi)
            $creator->notify(new MemoActionNotification($memo, 'rejected', $currentUser));
        }

    
        
        // ====================================================================

        $this->closeRejectModal();
        $this->dispatch('notify', message: "Le mémo a été rejeté et renvoyé à son créateur.");
    }

    public function closeModal() { $this->isOpen = false; }


    

    public function sign($id)
    {
        // 1. Récupération
        $memo = Memo::findOrFail($id);
        $user = Auth::user();

        // 2. Token de base
        $randomString = Str::upper(Str::random(10));
        $timestamp = now()->timestamp;
        $baseToken = "{$timestamp}-{$randomString}";

        // 3. Logique par poste
        if ($user->poste === 'Sous-Directeur') {
            
            if ($memo->signature_sd) {
                $this->dispatch('notify', message: "Ce document est déjà signé.");
                return;
            }

            $token = 'SD-' . $baseToken;
            $memo->signature_sd = $token;
            $message = "Signature Sous-Directeur apposée.";

        } elseif ($user->poste === 'Directeur') {
            
            if ($memo->signature_dir) {
                $this->dispatch('notify', message: "Ce document est déjà signé.");
                return;
            }

            // Signature visuelle
            $token = 'DIR-' . $baseToken;
            $memo->signature_dir = $token;

            // --- AJOUT : GÉNÉRATION DU QR CODE ---
            // On crée un UUID unique qui servira pour l'URL de vérification (route('memo.verify', $uuid))
            $memo->qr_code = (string) Str::uuid();

            // Optionnel : On verrouille le statut final du mémo
            $memo->status = 'valider'; 

            $message = "Signature Directeur apposée et QR Code généré.";
        
        } else {
            $this->dispatch('notify', message: "Action non autorisée pour votre poste.");
            return;
        }

        // 4. Sauvegarde
        $memo->save();

        // 5. Historique
        Historiques::create([
            'user_id' => $user->id,
            'memo_id' => $memo->id,
            'visa'    => 'Signé',
            'workflow_comment' => "Signature numérique " . ($user->poste == 'Directeur' ? "(Finale)" : "(Partielle)") . " : " . $token,
        ]);

         // ====================================================================
        // GESTION DES NOTIFICATIONS (Basée sur current_holders / nextHolders)
        // ====================================================================
        
     
            $user->notify(new MemoActionNotification($memo, 'signer', $user->id));
         
        // ====================================================================

        // 6. Feedback
        $this->dispatch('notify', message: $message);
    }





    // 1. OUVRIR LE MODAL ET CALCULER LES DESTINATAIRES
    public function transMemo($id)
    {
        $this->memo_id = $id;
        $memo = Memo::with('destinataires')->findOrFail($id);
       
        
        // A. Récupérer les ID des entités destinataires
        $targetEntityIds = $memo->destinataires->pluck('entity_id')->toArray();
         

        // B. Trouver les secrétaires de ces entités
        $rawSecretaries = User::whereIn('entity_id', $targetEntityIds)
                              ->where('poste', 'Secretaire') 
                              ->get();

        
        // C. Gérer les remplacements (User -> Remplaçant)
        $this->transRecipients = $rawSecretaries->map(function($user) {
            return $this->resolveUserAvailability($user);
        });

        if ($this->transRecipients->isEmpty()) {
            $this->addError('general', "Aucune secrétaire trouvée dans les entités destinataires.");
            return; // On n'ouvre pas le modal si personne à qui envoyer
        }

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
        $memo = Memo::findOrFail($this->memo_id);
        $currentUser = Auth::user();

        // On utilise une transaction DB pour s'assurer que tout s'enregistre ou rien
        DB::transaction(function () use ($memo, $currentUser) {
            
            // A. GÉNÉRATION DE LA RÉFÉRENCE
            $referenceString = $this->generateSmartReference($memo);
            
            // B. CRÉATION DANS BLOCS_ENREGISTREMENTS
            BlocEnregistrements::create([
                'nature_memo' => 'Memo Sortant', // Ou dynamique selon besoin
                'date_enreg' => now()->format('d/m/Y'),
                'reference' => $referenceString,
                'memo_id' => $memo->id,
                'user_id' => $currentUser->id,
            ]);

            // C. MISE À JOUR DU MÉMO
            // Récupérer les ID des "Users Effectifs" (les secrétaires ou leurs remplaçants)
            $nextHoldersIds = $this->transRecipients->pluck('effective.id')->unique()->toArray();

            $memo->update([
                'workflow_direction' => 'entrant',
                'workflow_comment' => "Enregistré et transmis aux destinataires",
                'current_holders' => $nextHoldersIds,
                'previous_holders' => [$currentUser->id],
                'status' => 'transmit',
                'reference' => $referenceString, // On met à jour la ref visible sur le mémo aussi ?
            ]);

            // D. HISTORIQUE
            Historiques::create([
                'user_id' => $currentUser->id,
                'memo_id' => $memo->id,
                'visa' => 'Enregistré',
                'workflow_comment' => "Enregistrement Ref: " . $referenceString,
            ]);
        });

         // ====================================================================
        // GESTION DES NOTIFICATIONS (Basée sur current_holders / nextHolders)
        // ====================================================================
        
        // On récupère les modèles User correspondant aux IDs trouvés juste au-dessus
        $usersToNotify = User::whereIn('id', $currentUser)->get();

        foreach ($usersToNotify as $user) {
            // $user : C'est l'utilisateur physique (ex: le Directeur ou son remplaçant)
            // 'sent' : Le type d'action pour afficher le bon message/icône
            $user->notify(new MemoActionNotification($memo, 'transmis', $currentUser));
        }
        
        // ====================================================================

        $this->closeTransModal();
        $this->dispatch('notify', message: "Mémo enregistré et transmis avec succès !");
    }


    // 3. LOGIQUE INTELLIGENTE DE GÉNÉRATION DE RÉFÉRENCE
    private function generateSmartReference($memo)
    {
        // 1. Compteur + 1
        $count = BlocEnregistrements::count() + 1 ;
        
        // 2. Données de base du créateur du mémo
        $creator = User::with(['entity', 'sousDirection'])->find($memo->user_id);
        
        // Préparation des segments
        $refEntity = $creator->entity->ref ?? 'ENT';
        $refSD = $creator->sousDirection->ref ?? 'SD'; // Assure-toi d'avoir la relation dans User
        $refDept = $this->abbreviate($creator->departement);
        $refService = $this->abbreviate($creator->service);
        $userInitials = Str::upper(substr($creator->first_name, 0, 1) . substr($creator->last_name, 0, 1));

        // 3. Vérification des Visas dans l'historique
        // On récupère tous les visas "Vu & Accord" pour ce mémo
        $validations = Historiques::where('memo_id', $memo->id)
                                  ->where('visa', 'Vu & Accord')
                                  ->pluck('user_id')
                                  ->toArray();

        // SCÉNARIO 1 : Le créateur a validé lui-même (Vu & Accord)
        if (in_array($creator->id, $validations)) {
            // Format : N°/Entity/SD/Dept/Service/Initiales
            return sprintf("%04dM/%s/%s/%s/%s/%s", $count , $refEntity, $refSD, $refDept, $refService, $userInitials);
        }

        // Pour les étapes suivantes, on remonte la hiérarchie du créateur
        // Note: Cela suppose que la hiérarchie est bien définie via manager_id
        
        $n1 = $creator->manager_id ? User::find($creator->manager_id) : null;
        
        // SCÉNARIO 2 : Le N+1 (Chef Service ?) a validé
        // On vérifie si N1 existe, s'il a validé, et si son poste contient "Service"
        if ($n1 && in_array($n1->id, $validations) && Str::contains($n1->poste, 'Service')) {
             // Format : N°/Entity/SD/Dept/Service
             return sprintf("%04d/%s/%s/%s/%s", $count, $refEntity, $refSD, $refDept, $refService);
        }

        // On cherche le N+2 (Chef Département ?)
        $n2 = $n1 && $n1->manager_id ? User::find($n1->manager_id) : null;

        // SCÉNARIO 3 : Le N+2 (Chef Dept) a validé
        if ($n2 && in_array($n2->id, $validations) && Str::contains($n2->poste, 'Département')) {
             // Format : N°/Entity/SD/Dept
             return sprintf("%04d/%s/%s/%s", $count, $refEntity, $refSD, $refDept);
        }

        // SCÉNARIO 4 (Par défaut ou Sous-Directeur) : 
        // Si personne "en bas" n'a validé complètement, on garde la ref haute
        // Format : N°/Entity/SD
        return sprintf("%04d/%s/%s", $count, $refEntity, $refSD);
    }

    // Helper pour abréger (Ex: "Ressources Humaines" -> "RH")
    private function abbreviate($string)
    {
        if (empty($string)) return '';
        
        // Prend les premières lettres de chaque mot majuscule
        // Ex simple: juste les 3 premières lettres en majuscule
        // Tu peux faire une logique plus complexe avec des Regex
        return Str::upper(substr($string, 0, 3)); 
    }
    
    
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