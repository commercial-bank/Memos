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
use App\Models\Destinataires;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB; 
use App\Models\BlocEnregistrements; 
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Notifications\MemoActionNotification;

class Incoming2Memos extends Component
{

    // --- RECHERCHE & DATATABLE ---
    public $search = '';

    public $isCreatingReply = false; // Pour basculer l'affichage
    public $parent_id = null;        // L'ID du mémo original

    // Propriétés pour le formulaire de création
    public $new_object = '';
    public $new_concern = '';
    public $new_content = '';
    public $recipients = [];         // Liste des destinataires
    public $attachments = [];        // Pour les fichiers (nécessite WithFileUploads)

    // Variables pour le sélecteur de destinataires
    public $newRecipientEntity = '';
    public $newRecipientAction = '';
    public $actionsList = ['Faire le nécessaire', 'Prendre connaissance', 'Prendre position', 'Décider'];

    // --- VARIABLES MODAL ASSIGNATION ---
    public $memo_type = 'standard'; // 'standard' ou 'projet'

    // Structure des données pour l'affichage : ['original' => User, 'effective' => User, 'is_replaced' => bool]
    public $managerData = null; 

    // --- VARIABLES MODAL CLÔTURE (TERMINER) ---
    public $isCloseModalOpen = false;
    public $memoIdToClose = null;
    public $closingComment = ''; // Pour laisser une note finale

    // --- AJOUTER CES PROPRIÉTÉS ---
    public $isTransModalOpen = false; // État du modal
    public $targetRecipients = [];    // La liste des utilisateurs trouvés (Objets User)
    public $selectedRecipients = [];  // Les IDs cochés par l'utilisateur
    public $targetRoleName = '';      // Le nom du rôle (ex: "Sous-Directeurs")
    public $memoIdToTrans = null;     // L'ID du mémo en cours de traitement

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

     // --- DATA VIEW (Aperçu) ---
    public $date;
    public $user_service;
    public $user_first_name;
    public $user_last_name;
    public $user_entity_name;
    public $selections = []; // Pour modal assignation simple

     // AJOUTER CETTE VARIABLE
    public $comment = ''; // Pour stocker le commentaire saisi dans le modal


    #[Rule('required|string|max:255')]
    public string $object = '';

    #[Rule('required|string|max:255')]
    public string $concern = '';

    #[Rule('required|string')]
    public string $content = '';

 
 




    // --- VARIABLES ENREGISTREMENT (MODIFIÉES) ---
    public $isRegistrationModalOpen = false;
    
    #[Rule('required|string')]
    public $reg_reference = ''; 

    #[Rule('required|string')]
    public $reg_nature = '';    

    #[Rule('required|string')]
    public $reg_objet = '';     // NOUVEAU

    #[Rule('required|string')]
    public $reg_expediteur = ''; // NOUVEAU

    #[Rule('required|string')]
    public $reg_date = '';


    // --- CHAMPS DU MÉMO (SCHEMA DB) ---
    public $memo_id = null;

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

    public function closeModal() { $this->isOpen = false; }

    /**
     * POINT D'ENTRÉE : Clic sur le bouton "Avion"
     */
    public function transMemo($id)
    {
        $this->resetValidation();
        $this->memoIdToTrans = $id;
        $this->comment = '';
        $this->selectedRecipients = [];
        
        // On charge le mémo avec l'utilisateur et son entité pour récupérer le nom
        $memo = Memo::with('user.entity')->find($id);

        if (!$memo) return;

        $user = Auth::user();
        $poste = Str::lower(trim($user->poste));

        // CAS SPÉCIFIQUE : SECRÉTAIRE
        if (Str::contains($poste, 'secretaire')) {
            
            // --- DEBUT DE LA VÉRIFICATION ---
            
            // Vérifie si un enregistrement existe déjà pour ce mémo dans BlocEnregistrements
            $dejaEnregistre = BlocEnregistrements::where('memo_id', $id)
                                                ->where('user_id', $user->id) // <--- AJOUT CRUCIAL
                                                ->exists();

            if ($dejaEnregistre) {
                // Si déjà enregistré, on notifie et on passe directement à la transmission
                $this->dispatch('notify', message: "Ce mémo est déjà enregistré. Passage direct à la transmission.");
                
                // On lance la préparation de la transmission (Step 2)
                $this->prepareTransmission($id);
                return; // On arrête l'exécution ici pour ne pas ouvrir le modal d'enregistrement
            }

            // --- FIN DE LA VÉRIFICATION ---

            // 1. Pré-remplissage (Si pas encore enregistré)
            $this->reg_reference = $memo->reference ?? '';
            $this->reg_date = Carbon::now()->format('d/m/Y');
            
            // 2. Pré-remplissage séparé
            $this->reg_objet = $memo->object; // L'objet du mémo
            $this->reg_nature = 'Memo Entrant'; // Valeur par défaut
            
            // 3. Récupération automatique de l'entité de l'expéditeur
            $this->reg_expediteur = $memo->user->entity->name ?? 'Entité Inconnue';

            $this->isRegistrationModalOpen = true; 
        } 
        else {
            // Pour les non-secrétaires (Directeurs, etc.), on passe direct à la transmission
            $this->prepareTransmission($id);
        }
    }

      public function saveRegistrationAndContinue()
        {
            $this->validate([
                'reg_reference' => 'required|string',
                'reg_nature' => 'required|string',
                'reg_objet' => 'required|string',
                'reg_expediteur' => 'required|string',
                'reg_date' => 'required|string',
            ]);

            $user = Auth::user();

            // Enregistrement avec les champs distincts
            BlocEnregistrements::create([
                'reference'          => $this->reg_reference,
                'date_enreg'         => $this->reg_date,
                'nature_memo'        => $this->reg_nature,        // Champ distinct
                'objet'              => $this->reg_objet,         // Champ distinct
                'memo_id'            => $this->memoIdToTrans,
                'user_id'            => $user->id,
            ]);

            // Mise à jour de la réf dans le mémo original si nécessaire
            $memo = Memo::find($this->memoIdToTrans);
            if ($memo && empty($memo->reference)) {
                $memo->update(['reference' => $this->reg_reference]);
            }

            $this->isRegistrationModalOpen = false;
            $this->dispatch('notify', message: "Enregistrement effectué. Passage à la transmission.");

            // On enchaîne sur la transmission
            $this->prepareTransmission($this->memoIdToTrans);
        }

     /**
     * ÉTAPE 2 : Préparation de la liste des destinataires (Logique existante extraite)
     */
    private function prepareTransmission($id)
    {
        $memo = Memo::find($id);
        $user = Auth::user();
        $poste = Str::lower(trim($user->poste)); 
        
        // On commence par filtrer sur l'entité globale (pour éviter de voir les gens d'une autre direction générale)
        // et on s'exclut soi-même.
        $query = User::query()
            ->where('entity_id', $user->entity_id)
            ->where('id', '!=', $user->id);

        // --- 1. CAS SECRÉTAIRE (Inchangé : on cible le Directeur) ---
        if (Str::contains($poste, 'secretaire')) {
            $this->targetRoleName = 'Directeur';
            $query->where('poste', 'like', '%Directeur%')
                ->where('poste', 'not like', '%Sous-Directeur%'); // S'assurer de ne pas prendre les sous-directeurs
        }

        // --- 2. CAS DIRECTEUR (Inchangé : on cible les Sous-Directeurs) ---
        elseif (Str::contains($poste, 'directeur') && !Str::contains($poste, 'sous')) {
            $this->targetRoleName = 'Sous-Directeur';
            $query->where('poste', 'like', '%Sous-Directeur%');
        }

        // --- 3. CAS SOUS-DIRECTEUR (Modifié) ---
        // Objectif : Voir Chefs de Département + Autres collaborateurs de sa sous-direction
        // Exclusions : Directeur, Secrétaires, Autres Sous-Directeurs
        elseif (Str::contains($poste, 'sous-directeur')) {
            $this->targetRoleName = 'Collaborateurs (Chefs de Dép. & autres)';
            
            // Si le sous-directeur est lié à une ID de sous-direction précise, on filtre dessus
            if ($user->sous_direction_id) {
                $query->where('sous_direction_id', $user->sous_direction_id);
            }

            // EXCLUSIONS :
            $query->where(function($q) {
                $q->where('poste', 'not like', '%Directeur%')      // Exclut le Directeur Général
                ->where('poste', 'not like', '%Sous-Directeur%') // Exclut les collègues Sous-Directeurs
                ->where('poste', 'not like', '%Secretaire%');    // Exclut les secrétaires
            });
        }

        // --- 4. CAS CHEF DE DÉPARTEMENT (Modifié) ---
        // Objectif : Voir Chefs de Service + Autres collaborateurs de son département
        // Exclusions : Supérieurs (Dir, Sous-Dir) et Pairs (Autres Chefs de Dept)
        elseif (Str::contains($poste, 'chef-departement')) {
            $this->targetRoleName = 'Collaborateurs (Chefs de Svc. & autres)';

            // On restreint au département de l'utilisateur
            $query->where('departement', $user->departement);

            // EXCLUSIONS :
            $query->where(function($q) {
                $q->where('poste', 'not like', '%Directeur%')       // Exclut Dir et Sous-Dir
                ->where('poste', 'not like', '%Chef-Departement%') // Exclut les autres Chefs de Dept (pairs)
                ->where('poste', 'not like', '%Secretaire%');
            });
        }

        // --- 5. CAS CHEF DE SERVICE (Modifié) ---
        // Objectif : Voir tous les collaborateurs du service
        // Exclusions : Supérieurs et Pairs (Autres Chefs de Service)
        elseif (Str::contains($poste, 'chef-service')) {
            $this->targetRoleName = 'Collaborateurs du Service';

            // On restreint au service de l'utilisateur
            $query->where('service', $user->service);

            // EXCLUSIONS :
            $query->where(function($q) {
                $q->where('poste', 'not like', '%Directeur%')
                ->where('poste', 'not like', '%Chef-Departement%')
                ->where('poste', 'not like', '%Chef-Service%') // Exclut les autres chefs de service si doublon
                ->where('poste', 'not like', '%Secretaire%');
            });
        }

        // --- CAS PAR DÉFAUT (Si le poste n'est pas reconnu) ---
        else {
            $this->dispatch('notify', message: "Votre poste ne permet pas la transmission automatique.");
            return;
        }

        // Exécution de la requête
        // On trie par poste pour regrouper les Chefs ensemble visuellement, puis par nom
        $this->targetRecipients = $query->orderBy('poste', 'asc')
                                        ->orderBy('first_name', 'asc')
                                        ->get();

        if ($this->targetRecipients->isEmpty()) {
            $this->dispatch('notify', message: "Aucun destinataire éligible trouvé pour le groupe : {$this->targetRoleName}.");
            return;
        }

        // Si un seul résultat, on le pré-coche
        if ($this->targetRecipients->count() === 1) {
            $this->selectedRecipients[] = $this->targetRecipients->first()->id;
        }

        // OUVERTURE DU MODAL
        $this->isTransModalOpen = true;
    }

    public function closeRegistrationModal()
    {
        $this->isRegistrationModalOpen = false;
        $this->memoIdToTrans = null;
    }

   
    public function closeTransModal()
    {
        $this->isTransModalOpen = false;
        $this->selectedRecipients = [];
        $this->comment = '';
        $this->memoIdToTrans = null;
        $this->targetRecipients = [];
    }

    public function confirmTransmission()
    {
        // 1. Validation
        $this->validate([
            'selectedRecipients' => 'required|array|min:1',
        ], [
            'selectedRecipients.required' => 'Veuillez sélectionner au moins un destinataire.',
        ]);

        $memo = Memo::find($this->memoIdToTrans);
        if (!$memo) {
            $this->dispatch('notify', message: "Erreur : Mémo introuvable.");
            $this->closeTransModal();
            return;
        }

        $senderId = Auth::id();
        $senderUser = Auth::user(); 

        // 2. Mise à jour des détenteurs (current_holders)
        $holders = $memo->current_holders;
        
        // Normalisation du JSON
        if (is_null($holders)) {
            $holders = [];
        } elseif (is_string($holders)) {
            $holders = json_decode($holders, true) ?? [];
        }

        // A. On retire l'expéditeur actuel de la liste (car il transmet le dossier)
        $holders = array_values(array_diff($holders, [$senderId]));

        // B. On ajoute les nouveaux destinataires
        foreach ($this->selectedRecipients as $recipientId) {
            $rId = (int) $recipientId;
            if (!in_array($rId, $holders)) {
                $holders[] = $rId;
            }
        }

        // C. Sauvegarde
        $memo->current_holders = $holders;
        $memo->save();

        // 3. Historique & Notification
        // On récupère les objets User correspondant aux IDs sélectionnés (les nouveaux détenteurs)
        $nextHolders = User::whereIn('id', $this->selectedRecipients)->get();

        foreach ($nextHolders as $recipient) {
            
            // A. Création de l'historique pour chaque destinataire
            Historiques::create([
                'user_id' => $senderId,
                'memo_id' => $memo->id,
                'visa'    => 'Coté / Transmis', 
                'workflow_comment' => $this->comment . " (Pour: " . $recipient->first_name . " " . $recipient->last_name . ")"
            ]);

            // B. Envoi de la notification "Cotation"
            try {
                // On notifie le destinataire qui est maintenant détenteur du mémo
                $recipient->notify(new MemoActionNotification($memo, 'cotation', $senderUser));
            } catch (\Exception $e) {
                // Log::error("Erreur notif cotation : " . $e->getMessage());
            }
        }

        // 4. Feedback et Fermeture
        $this->dispatch('notify', message: "Mémo coté et transmis avec succès.");
        $this->closeTransModal();
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

    // =================================================================
    // NOUVELLE FONCTION : GÉNÉRATION PDF
    // =================================================================
    public function downloadMemoPDF()
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($this->memo_id);
        
        // Groupement des destinataires
        $recipientsByAction = $memo->destinataires->groupBy('action');

        // 1. Image Logo en Base64
        $pathLogo = public_path('images/logo.jpg');
        $logoBase64 = file_exists($pathLogo) 
            ? 'data:image/jpg;base64,' . base64_encode(file_get_contents($pathLogo)) 
            : null;

        // 2. QR Code en Base64
        $qrCodeBase64 = null;
        if ($memo->qr_code) {
            $qrImage = QrCode::format('svg')->size(100)->generate(route('memo.verify', $memo->qr_code));
            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrImage);
        }

        // 3. Génération du PDF
        $pdf = Pdf::loadView('pdf.memo-layout', [
            'memo' => $memo,
            'recipientsByAction' => $recipientsByAction,
            'logo' => $logoBase64,
            'qrCode' => $qrCodeBase64,
            'date' => $memo->created_at->format('d/m/Y'),
        ]);

        // Configuration A4 Portrait
        $pdf->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Memo_' . $memo->id . '.pdf');
    }

    /**
     * Action : Le Directeur termine/classe le mémo.
     * Le mémo n'est pas supprimé, mais retiré de sa bannette.
     */

    public function closeMemo($id)
    {
        $memo = Memo::find($id);
        if (!$memo) return;

        $userId = Auth::id();

        // On change la direction du workflow pour le sortir de la liste "Entrant"
        $memo->workflow_direction = "terminer";
        
        // Optionnel : Vous pouvez aussi mettre à jour le statut
        // $memo->status = 'traité';

        $memo->save();

        // Historique
        Historiques::create([
            'user_id' => $userId,
            'memo_id' => $memo->id,
            'visa'    => 'Terminé / Classé',
            'workflow_comment' => "Dossier traité et clôturé."
        ]);

        $this->dispatch('notify', message: "Le mémo a été terminé avec succès.");
    }
    
   


     /**
     * 1. Ouvre le modal de clôture
     */
    public function openCloseModal($id)
    {
        $this->memoIdToClose = $id;
        $this->closingComment = ''; // Réinitialiser le commentaire
        $this->isCloseModalOpen = true;
    }

    /**
     * 2. Ferme le modal sans action
     */
    public function cancelCloseModal()
    {
        $this->isCloseModalOpen = false;
        $this->memoIdToClose = null;
        $this->closingComment = '';
    }
    

    /**
     * 3. Action : Confirme la clôture (Exécuté depuis le modal)
     */
    

    public function confirmCloseMemo()
    {
        $memo = Memo::find($this->memoIdToClose);
        $user = Auth::user();
        
        // 1. Récupérer l'entrée 'destinataires' correspondant à l'entité de l'utilisateur courant
        $myDestinataireRecord = Destinataires::where('memo_id', $memo->id)
            ->where('entity_id', $user->entity_id)
            ->first();

        if (!$myDestinataireRecord) {
            $this->dispatch('notify', message: "Erreur : Votre entité n'est pas destinataire de ce mémo.");
            return;
        }

        // =================================================================
        // LOGIQUE DE BLOCAGE (DEPENDANCE "DÉCIDER")
        // =================================================================
        
        // On cherche s'il y a UNE AUTRE entité qui doit "Décider"
        $decisionMakers = Destinataires::where('memo_id', $memo->id)
            ->where('action', 'like', '%Décider%') // Ou 'Decider' selon ta DB
            ->where('entity_id', '!=', $user->entity_id) // Ce n'est pas moi
            ->get();

        foreach ($decisionMakers as $maker) {
            // Si le décideur n'a pas encore rendu sa décision (statut pas 'decision_prise')
            if ($maker->processing_status !== 'decision_prise') {
                
                // ON BLOQUE TOUT
                $entityName = $maker->entity->name ?? 'l\'entité responsable';
                $this->dispatch('notify', message: "Impossible de terminer : Vous devez attendre la décision de {$entityName}.");
                $this->cancelCloseModal(); // Ferme le modal
                return;
            }
        }

        // =================================================================
        // SI PAS BLOQUÉ : CLÔTURE LOCALE
        // =================================================================

        // 1. Mise à jour du statut LOCAL (dans la table destinataires)
        $myDestinataireRecord->update([
            'processing_status' => 'traite',
            'completed_at' => now()
        ]);

        // 2. Retirer l'utilisateur des current_holders (Comme avant)
        $holders = $memo->current_holders ?? [];
        if (is_string($holders)) $holders = json_decode($holders, true);
        $holders = array_values(array_diff($holders, [$user->id]));
        $memo->current_holders = $holders;

        // 3. Historique
        Historiques::create([
            'user_id' => $user->id,
            'memo_id' => $memo->id,
            'visa'    => 'Terminé (Entité)',
            'workflow_comment' => $this->closingComment ?: "Traitement terminé pour " . $user->entity->name
        ]);

        // =================================================================
        // VÉRIFICATION GLOBALE (Est-ce que tout le monde a fini ?)
        // =================================================================
        
        $pendingEntities = Destinataires::where('memo_id', $memo->id)
            ->where('processing_status', '!=', 'traite')
            ->where('processing_status', '!=', 'decision_prise') // On considère decision_prise comme fini aussi
            ->count();

        if ($pendingEntities === 0) {
            // C'était la dernière entité ! On ferme le mémo globalement.
            $memo->workflow_direction = "terminer";
            $memo->status = "archive"; // Ou autre statut final
        }

        $memo->save();

        $this->dispatch('notify', message: "Dossier traité pour votre entité.");
        $this->cancelCloseModal();
    }

    public function submitDecision($id, $decision) // $decision = 'accord' ou 'refus'
    {
        $memo = Memo::find($id);
        $user = Auth::user();

        // Trouver le record destinataire
        $destRecord = Destinataire::where('memo_id', $memo->id)
            ->where('entity_id', $user->entity_id)
            ->where('action', 'like', '%Décider%')
            ->first();

        if (!$destRecord) return;

        // Mettre à jour le statut spécial
        $destRecord->update([
            'processing_status' => 'decision_prise', // Ce statut débloque les autres !
            'decision_result' => $decision,
            'completed_at' => now()
        ]);

        // Retirer de la bannette
        $holders = $memo->current_holders ?? [];
        // ... (retrait user id) ...
        $memo->save();

        // Historique
        Historiques::create([
            'user_id' => $user->id,
            'memo_id' => $memo->id,
            'visa'    => 'DÉCISION RENDUE',
            'workflow_comment' => "Décision : " . strtoupper($decision)
        ]);

        $this->dispatch('notify', message: "Décision enregistrée. Les autres entités peuvent maintenant clôturer.");
    }

    // 1. La méthode déclenchée par le bouton "Répondre"
    public function replyMemo($id)
    {
        $parent = Memo::with('user.entity')->find($id);
        if (!$parent) return;

        $this->parent_id = $id;
        
        // Pré-remplissage intelligent
        $this->new_object = "RE: " . $parent->object;
        $this->new_concern = "Réponse au mémo réf: " . ($parent->reference ?? 'N/A');
        $this->new_content = ""; // Vide pour la réponse
        
        // Auto-ajout de l'expéditeur original comme premier destinataire
        $this->recipients = [];
        if($parent->user && $parent->user->entity_id) {
            $this->recipients[] = [
                'entity_id' => $parent->user->entity_id,
                'entity_name' => $parent->user->entity->name,
                'action' => 'Faire le nécessaire'
            ];
        }

        $this->isCreatingReply = true; // On affiche l'interface de création
    }

    // 2. Méthode pour annuler et revenir à la liste
    public function cancelReply()
    {
        $this->isCreatingReply = false;
        $this->reset(['new_object', 'new_concern', 'new_content', 'recipients', 'parent_id']);
    }

    // 3. Méthode pour ajouter un destinataire (logique de votre interface)
    public function addRecipient()
    {
        $this->validate([
            'newRecipientEntity' => 'required',
            'newRecipientAction' => 'required',
        ]);

        $entity = Entity::find($this->newRecipientEntity);
        $this->recipients[] = [
            'entity_id' => $entity->id,
            'entity_name' => $entity->name,
            'action' => $this->newRecipientAction
        ];

        $this->reset(['newRecipientEntity', 'newRecipientAction']);
    }

    // 4. Enregistrement final du mémo de réponse
    public function saveReply()
{
    // 1. Validation des champs du formulaire
    $this->validate([
        'new_object'  => 'required|string|max:255',
        'new_concern' => 'required|string|max:255',
        'new_content' => 'required',
        'recipients'  => 'required|array|min:1',
    ]);

    // Vérification de sécurité : Avons-nous bien le mémo original ?
    if (!$this->parent_id) {
        $this->dispatch('notify', message: "Erreur : Impossible de retrouver le mémo d'origine pour lier la réponse.");
        return;
    }

    $currentUser = Auth::user();
    $poste = Str::lower(trim($currentUser->poste));
    $targetId = null;
    $targetType = ""; 

    // 2. LOGIQUE DE DÉTERMINATION DU DESTINATAIRE (DETENTEUR)
    if (Str::contains($poste, 'directeur') && !Str::contains($poste, 'sous-directeur')) {
        $secretary = User::where('entity_id', $currentUser->entity_id)
                        ->where('poste', 'like', '%secretaire%')
                        ->where('is_active', true)
                        ->first();
        
        if ($secretary) {
            $targetId = $secretary->id;
            $targetType = "au Secrétariat pour enregistrement";
        } else {
            $targetId = $currentUser->manager_id;
            $targetType = "au Manager (Secrétaire introuvable)";
        }
    } else {
        $targetId = $currentUser->manager_id;
        $targetType = "au Manager pour validation";
    }

    if (!$targetId) {
        $this->dispatch('notify', message: "Erreur : Aucun destinataire trouvé.");
        return;
    }

    $newMemo = null;

    DB::transaction(function () use (&$newMemo, $currentUser, $targetId, $targetType) {
        
        // --- A. RÉCUPÉRATION ET CLÔTURE DU MÉMO ORIGINAL (LE PARENT) ---
        $parentMemo = Memo::find($this->parent_id);
        
        if ($parentMemo) {
            $parentMemo->update([
                'workflow_direction' => 'terminer',
                'status' => 'terminer',
                'current_holders' => [] // On vide les détenteurs car il est traité
            ]);

            // Historique sur le mémo ORIGINAL
            Historiques::create([
                'user_id'          => $currentUser->id,
                'memo_id'          => $parentMemo->id, // L'ID du parent
                'visa'             => 'CLÔTURÉ PAR RÉPONSE',
                'workflow_comment' => "Le dossier a été clôturé. Une réponse a été émise sous la référence en attente."
            ]);
        }

        // --- B. CRÉATION DU NOUVEAU MÉMO (LA RÉPONSE) ---
        // Ici on utilise explicitement $this->parent_id pour créer le lien hiérarchique
        $newMemo = Memo::create([
            'object'             => $this->new_object,
            'concern'            => $this->new_concern,
            'content'            => $this->new_content,
            'user_id'            => $currentUser->id,
            'parent_id'          => $parentMemo->id, // L'ID du mémo qu'on vient de clôturer (Lien de parenté)
            'workflow_direction' => 'sortant',        
            'status'             => 'reponse',       
            'current_holders'    => [$targetId], 
        ]);

        // --- C. CRÉATION DES DESTINATAIRES POUR LA RÉPONSE ---
        foreach ($this->recipients as $item) {
            Destinataires::create([
                'memo_id'           => $newMemo->id,
                'entity_id'         => $item['entity_id'],
                'action'            => $item['action'],
                'processing_status' => 'en_cours'
            ]);
        }

        // --- D. HISTORIQUE SUR LE NOUVEAU MÉMO (L'ENFANT) ---
        Historiques::create([
            'user_id'          => $currentUser->id,
            'memo_id'          => $newMemo->id,
            'visa'             => 'CRÉATION RÉPONSE',
            'workflow_comment' => "Ce mémo est une réponse au mémo ID #{$this->parent_id}. Transmis {$targetType}."
        ]);
    });

    // 3. NOTIFICATION DU DESTINATAIRE CIBLE
    if ($newMemo && isset($targetId)) {
        $targetUser = User::find($targetId);
        if ($targetUser) {
            $targetUser->notify(new MemoActionNotification($newMemo, 'envoyer', $currentUser));
        }
    }

    // 4. Finalisation
    $this->dispatch('notify', message: "Dossier original clos. Réponse transmise avec succès.");
    
    // Fermeture de l'interface de création et reset
    $this->isCreatingReply = false;
    $this->resetReplyForm();
}



    private function resetReplyForm() {
        $this->reset(['isCreatingReply', 'parent_id', 'new_object', 'new_concern', 'new_content', 'recipients']);
    }


    public function render()
    {
       // On récupère l'ID tel quel (c'est un entier par défaut dans Laravel)
        $userId = Auth::id(); 

        $memos = Memo::with(['user', 'destinataires.entity'])
            ->where('workflow_direction', 'entrant')
            
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

        return view('livewire.memos.incoming2-memos', [
            'memos' => $memos,
        ]);
    }

}
