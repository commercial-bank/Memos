<?php

namespace App\Livewire\Memos;

use Carbon\Carbon;
use App\Models\Memo;
use App\Models\User;
use App\Models\Entity;
use Livewire\Component;
use App\Models\DraftedMemo;
use App\Models\Historiques;
use Illuminate\Support\Str;
use App\Models\ReplacesUser;
use Livewire\WithPagination;
use App\Mail\NouveauMemoMail;
use App\Models\Destinataires;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Notifications\MemoActionNotification;

// ⭐ AJOUTEZ CES DEUX LIGNES ICI ⭐ 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class DraftedMemos extends Component
{
    use WithPagination, WithFileUploads;

    // =================================================================================================
    // 1. PROPRIÉTÉS D'ÉTAT DE L'INTERFACE (UI) & MODALS
    // =================================================================================================

    // --- États de navigation ---
    public $search = '';
    public $isEditing = false; 
    public $isViewingPdf = false;
    public $darkMode = false;

    // --- États des Modals ---
    public $isOpen3 = false; // Modal Workflow/Assignation
    public $isOpen4 = false; // Modal Suppression

    
    // =================================================================================================
    // 2. PROPRIÉTÉS DU FORMULAIRE (MÉMO)
    // =================================================================================================

    public $memo_id = null;

    #[Rule('required|string|max:255')]
    public string $object = '';

    #[Rule('nullable|string|max:255')]
    public string $concern = '';

    #[Rule('required|string')]
    public string $content = '';

    // --- Données pour l'Aperçu PDF ---
    public $pdfBase64 = '';
    public $date;
    public $user_entity_name;


    // =================================================================================================
    // 3. PROPRIÉTÉS DE GESTION DES DESTINATAIRES (RECHERCHE & LISTE)
    // =================================================================================================

    public $recipients = []; 
    public $allEntities = []; 
    public $actionsList = ['Faire le nécessaire', 'Prendre connaissance', 'Prendre position', 'Décider'];

    // --- Recherche Destinataire ---
    public $searchRecipient = '';
    public $newRecipientEntity = null; // ID de l'entité sélectionnée
    public $newRecipientAction = '';
    
    // Drapeau pour empêcher le reset lors de la sélection
    protected $isSelection = false; 


    // =================================================================================================
    // 4. PROPRIÉTÉS DE WORKFLOW & CIRCUIT
    // =================================================================================================

    public $memo_type = 'standard'; 
    public $workflow_comment = '';
    public $selected_visa = ''; 
    public $isSecretary = false;

    // --- Listes d'utilisateurs ---
    public $managerData = null;     
    public $standardRecipientsList = [];  // Liste Director + Sous-directeurs
    public $selected_standard_users = []; // Les IDs sélectionnés en mode Standard
    public $projectUsersList = [];  
    public $selected_project_users = [];
    
    // --- Circuit Particulier (Projet) ---
    public $selected_project_path = []; // Tableau d'IDs ordonnés [ID_1, ID_2, ID_3]
    public $search_project_user = '';   // Pour la recherche de membres


    // =================================================================================================
    // 5. PROPRIÉTÉS DES PIÈCES JOINTES
    // =================================================================================================

    // $attachments est utilisé pour les nouveaux uploads (WithFileUploads)
    public $attachments = []; 
    // $existingAttachments stocke les chemins des fichiers déjà en base de données
    public $existingAttachments = []; 


    // =================================================================================================
    // 6. INITIALISATION (LIFECYCLE)
    // =================================================================================================

    public function mount()
    {
        $this->allEntities = Entity::orderBy('name', 'asc')->get(); 
    }


    // =================================================================================================
    // 7. GESTION DE L'INTERFACE & NAVIGATION
    // =================================================================================================

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function closeModalTrois() { $this->isOpen3 = false; }
    public function closeModalQuatre() { $this->isOpen4 = false; }


    // =================================================================================================
    // 8. LOGIQUE D'ÉDITION & SAUVEGARDE (CRUD)
    // =================================================================================================

    public function editMemo($id)
    {
        // On utilise DraftedMemo au lieu de Memo
        $memo = DraftedMemo::findOrFail($id);
        
        $this->memo_id = $memo->id;
        $this->object = $memo->object;
        $this->concern = $memo->concern ?? '';
        $this->content = $memo->content;
        
        // Gestion des pièces jointes
        $pj = $memo->pieces_jointes;
        $this->existingAttachments = is_array($pj) ? $pj : (json_decode($pj, true) ?? []);
        
        $this->attachments = [];

        // Chargement des destinataires depuis la colonne JSON
        $this->recipients = collect($memo->destinataires ?? [])->map(function($dest) {
            $entity = \App\Models\Entity::find($dest['entity_id']);
            return [
                'entity_id'   => $dest['entity_id'],
                'entity_name' => $entity->name ?? 'Inconnu',
                'action'      => $dest['action']
            ];
        })->toArray();

        $this->date = $memo->created_at->format('d/m/Y');   
        $this->user_entity_name = $memo->user->entity->name ?? 'Entité';

        $this->isEditing = true;
        $this->isViewingPdf = false;
        $this->resetValidation();
    }

    public function cancelEdit()
    {
        $this->isEditing = false;
        $this->reset(['memo_id', 'object', 'concern', 'content', 'recipients', 'attachments', 'existingAttachments']);
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        // 1. Gérer les fichiers
        $finalPaths = $this->existingAttachments;
        
        if ($this->attachments) {
            foreach ($this->attachments as $file) {
                $finalPaths[] = $file->store('attachments/drafts', 'public');
            }
        }

        // 2. Mise à jour ou Création dans DRAFTED_MEMOS
        DraftedMemo::updateOrCreate(
            ['id' => $this->memo_id],
            [
                'object'          => $this->object,
                'concern'         => $this->concern,
                'content'         => $this->content,
                'pieces_jointes'  => $finalPaths,
                'destinataires'   => $this->recipients, // On sauve le tableau directement en JSON
                'user_id'         => Auth::id(),
                'status'          => 'brouillon',
                'workflow_direction' => 'sortant'
            ]
        );

        $this->isEditing = false;
        $this->dispatch('notify', message: "Brouillon mis à jour avec succès !");
    }

    public function deleteMemo($id) { $this->memo_id = $id; $this->isOpen4 = true; }
    
    public function del() {
        DraftedMemo::where('id', $this->memo_id)->where('user_id', Auth::id())->delete();
        $this->isOpen4 = false;
        $this->dispatch('notify', message: "Mémo supprimé.");
    }


    // =================================================================================================
    // 9. LOGIQUE DES PIÈCES JOINTES
    // =================================================================================================

    // Supprimer un fichier qui est déjà sur le serveur
    public function removeExistingAttachment($index)
    {
        if (isset($this->existingAttachments[$index])) {
            unset($this->existingAttachments[$index]);
            $this->existingAttachments = array_values($this->existingAttachments);
        }
    }

    // Supprimer un fichier qui vient d'être sélectionné (upload temporaire)
    public function removeAttachment($index)
    {
        if (isset($this->attachments[$index])) {
            unset($this->attachments[$index]);
            $this->attachments = array_values($this->attachments);
        }
    }


    // =================================================================================================
    // 10. LOGIQUE DES DESTINATAIRES & RECHERCHE
    // =================================================================================================

    /**
     * Détecte quand l'utilisateur tape dans le champ
     */
    public function updatedSearchRecipient()
    {
        // Si le changement vient d'un clic (flag true), on ne fait rien
        if ($this->isSelection) {
            $this->isSelection = false;
            return;
        }

        // Sinon, c'est que l'utilisateur tape : on invalide l'ID précédent
        $this->newRecipientEntity = null;
    }

    /**
     * Sélectionne une entité depuis la liste
     */
    public function selectRecipientEntity($id, $name)
    {
        $this->isSelection = true; // On active le drapeau
        $this->newRecipientEntity = $id;
        $this->searchRecipient = $name; // Met à jour le texte affiché
    }

    public function getFilteredEntitiesProperty()
    {
        if (empty($this->searchRecipient)) {
            return [];
        }

        $term = '%' . $this->searchRecipient . '%';

        return Entity::whereIn('type', ['Direction', 'Sous-Direction'])
            ->where(function($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('ref', 'like', $term);
            })
            ->orderBy('name', 'asc')
            ->limit(10)
            ->get();
    }
 
    public function addRecipient()
    {
        // Validation
        $this->validate([
            'newRecipientEntity' => 'required|integer|exists:entities,id',
            'newRecipientAction' => 'required|string'
        ], [
            'newRecipientEntity.required' => 'Veuillez sélectionner une entité valide dans la liste.', 
            'newRecipientAction.required' => 'Veuillez choisir une action.'
        ]);

        // Vérification doublon
        if (collect($this->recipients)->contains('entity_id', $this->newRecipientEntity)) {
            $this->addError('newRecipientEntity', 'Cette entité est déjà dans la liste.');
            return;
        }

        $entity = Entity::find($this->newRecipientEntity);

        if ($entity) {
            $this->recipients[] = [
                'entity_id'   => $entity->id,
                'entity_name' => $entity->name,
                'action'      => $this->newRecipientAction
            ];

            // Reset complet pour la prochaine entrée
            $this->reset(['newRecipientEntity', 'newRecipientAction', 'searchRecipient']);
        }
    }

    public function removeRecipient($index)
    {
        unset($this->recipients[$index]);
        $this->recipients = array_values($this->recipients);
    }


    // =================================================================================================
    // 11. LOGIQUE DE WORKFLOW (ASSIGNATION & ENVOI)
    // =================================================================================================

    public function assignMemo($id)
    {
        $this->memo_id = $id;
        $this->reset(['workflow_comment', 'selected_project_users', 'selected_standard_users']);
        $this->memo_type = 'standard';

        $currentUser = Auth::user();
        $today = Carbon::now()->format('Y-m-d');
        
        $activeReplacements = ReplacesUser::where('date_begin_replace', '<=', $today)
            ->where('date_end_replace', '>=', $today)
            ->get()
            ->keyBy('user_id');

        $posteString = $currentUser->poste->value ?? (string)$currentUser->poste;
        $this->isSecretary = Str::contains($posteString, 'Secretaire');

        if ($this->isSecretary) {
            // RÉCUPÉRATION : Manager + tous les Directeurs et Sous-Directeurs de la MÊME entité
            $this->standardRecipientsList = User::where('dir_id', $currentUser->dir_id)
                ->where('id', '!=', $currentUser->id) // Exclure soi-même
                ->where(function ($q) use ($currentUser) {
                    $q->where('id', $currentUser->manager_id) // Son manager direct
                    ->orWhere('poste', 'like', '%Directeur%') // Tous les Directeurs
                    ->orWhere('poste', 'like', '%Sous-Directeur%'); // Tous les Sous-Directeurs
            })
            ->orderBy('last_name')
            ->get()
            ->map(fn($user) => $this->resolveUserAvailability($user, $activeReplacements));
        } else {
            // Logique classique pour les autres postes
            if ($currentUser->manager_id) {
                $manager = User::find($currentUser->manager_id);
                $this->managerData = $this->resolveUserAvailability($manager, $activeReplacements);
            }
        }

        $excludeIds = array_filter([$currentUser->id, $currentUser->manager_id]);
        $this->projectUsersList = User::whereNotIn('id', $excludeIds)
            ->orderBy('last_name')
            ->get()
            ->map(fn($user) => $this->resolveUserAvailability($user, $activeReplacements));

        $this->isOpen3 = true;
    }

    // --- Méthodes de gestion du circuit particulier ---
    
    public function addToPath($userId)
    {
        if (!in_array($userId, $this->selected_project_path)) {
            $this->selected_project_path[] = $userId;
        }
        $this->search_project_user = '';
    }

    public function removeFromPath($index)
    {
        unset($this->selected_project_path[$index]);
        $this->selected_project_path = array_values($this->selected_project_path);
    }

    // Recherche filtrée pour le circuit particulier
    public function getAvailableProjectUsersProperty()
    {
            if (strlen($this->search_project_user) < 2) return [];

            return User::where('id', '!=', Auth::id())
                ->where(function($q) {
                    $q->where('first_name', 'like', '%'.$this->search_project_user.'%')
                    ->orWhere('last_name', 'like', '%'.$this->search_project_user.'%');
                })
                ->whereNotIn('id', $this->selected_project_path)
                ->limit(5)->get();
    }

    public function sendMemo()
    {
            // 1. DÉFINITION DES RÈGLES DE VALIDATION DYNAMIQUES
            $rules = [
                'workflow_comment' => 'nullable|string|max:1000',
            ];

            // Si on est en mode PROJET
            if ($this->memo_type === 'projet') {
                $rules['selected_project_path'] = 'required|array|min:1';
            } 
            // Si on est en mode STANDARD et qu'on est SECRÉTAIRE
            elseif ($this->memo_type === 'standard' && $this->isSecretary) {
                $rules['selected_standard_users'] = 'required|array|min:1';
            }

            // 2. EXÉCUTION DE LA VALIDATION
            $this->validate($rules, [
                'selected_project_path.required' => 'Veuillez ajouter au moins un collaborateur au circuit.',
                'selected_project_path.min' => 'Le circuit doit comporter au moins une personne.',
                'selected_standard_users.required' => 'Veuillez sélectionner un destinataire dans la liste hiérarchique.',
            ]);

            // 3. Récupérer le BROUILLON
            $draft = DraftedMemo::findOrFail($this->memo_id);
            $user = Auth::user();
            $today = Carbon::now()->format('Y-m-d');
            
            // Gestion des remplacements
            $activeReplacements = ReplacesUser::where('date_begin_replace', '<=', $today)
                ->where('date_end_replace', '>=', $today)
                ->get()
                ->keyBy('user_id');

            // Commentaire spécial si P/O (Par Ordre)
            $replacementContext = $this->getReplacementRights($draft);
            $finalComment = $this->workflow_comment;
            if ($replacementContext && in_array('viser', $replacementContext['actions_allowed'])) {
                $titulaire = $replacementContext['original_user'];
                $finalComment = "[P/O " . $titulaire->poste . "] " . $this->workflow_comment;
            }

            // 4. Déterminer les futurs détenteurs (Next Holders)
            $nextHolders = [];

            if ($this->memo_type === 'standard') {

                if ($this->isSecretary) {
                    $selectedUsers = User::whereIn('id', $this->selected_standard_users)->get();
                    foreach ($selectedUsers as $u) {
                        $avail = $this->resolveUserAvailability($u, $activeReplacements);
                        $nextHolders[] = $avail['effective']->id;
                    }
                } else {
                    if ($this->managerData) {
                        $nextHolders[] = $this->managerData['effective']->id;
                    }
                }


                if (empty($nextHolders)) {
                $this->addError('general', 'Aucun destinataire sélectionné.');
                return;
            }

            // --- CRÉATION DU MÉMO (Transfert de DraftedMemo vers Memo) ---
            $memo = Memo::create([
                'object'             => $draft->object,
                'reference'          => $draft->reference,
                'concern'            => $draft->concern,
                'content'            => $draft->content,
                'status'             => 'envoyer',
                'workflow_direction' => 'sortant',
                'pieces_jointes'     => $draft->pieces_jointes,
                'user_id'            => $draft->user_id,
                'parent_id'          => $draft->parent_id,
                'previous_holders'   => [$user->id], 
                'current_holders'    => array_unique($nextHolders), 
                'treatment_holders'  => array_unique($nextHolders),
                'circuit_type' => 'standard', 
                'circuit_path' => null,       
            ]);

            // Enregistrement des destinataires
            $recipientsData = is_array($draft->destinataires) ? $draft->destinataires : json_decode($draft->destinataires, true);
            
            if (!empty($recipientsData)) {
                foreach ($recipientsData as $dest) {
                    Destinataires::create([
                        'memo_id'   => $memo->id,
                        'entity_id' => $dest['entity_id'],
                        'action'    => $dest['action'],
                    ]);
                }
            }

            // Création de l'historique
            Historiques::create([
                'user_id'          => $user->id,
                'memo_id'          => $memo->id,
                'visa'             => 'valider', 
                'workflow_comment' => $finalComment ?? 'R.A.S',
            ]);

            // Notifications
            foreach (User::whereIn('id', $nextHolders)->get() as $recipient) {
                try {
                    $recipient->notify(new MemoActionNotification($memo, 'envoyer', $user));
                } catch (\Exception $e) {}
            }

            // Envoi Email
            $this->sendEmailNotification($memo, $nextHolders, $user);

            // Suppression et Finalisation
            $draft->delete();
            $this->closeModalTrois();
            $this->isEditing = false;
            $this->dispatch('notify', message: "Mémo transmis et brouillon supprimé avec succès.");


            } elseif ($this->memo_type === 'projet') {

            // 1. Identification de la secrétaire de direction
            $directionSecretary = User::where('dir_id', $user->dir_id)
                ->where('poste', 'like', '%Secretaire%')
                ->first();

            // 2. Construction de la chaîne complète
            $fullChainIds = $this->selected_project_path;

            if ($directionSecretary && !in_array($directionSecretary->id, $fullChainIds)) {
                $fullChainIds[] = $directionSecretary->id; 
            }

            // 3. Le premier intervenant reçoit le mémo
            $firstUserId = $fullChainIds[0];
            $firstInLine = User::find($firstUserId);  
            
            if ($firstInLine) {
                $avail = $this->resolveUserAvailability($firstInLine, $activeReplacements);
                $nextHolders = [$avail['effective']->id];
            }

            if (empty($nextHolders)) {
                $this->addError('general', 'Impossible de déterminer le premier destinataire du circuit.');
                return;
            }

            // 4. Création du mémo officiel (Projet)
            $memo = Memo::create([
                'object'             => $draft->object,
                'reference'          => $draft->reference,
                'concern'            => $draft->concern,
                'content'            => $draft->content,
                'status'             => 'envoyer',
                'workflow_direction' => 'sortant',
                'pieces_jointes'     => $draft->pieces_jointes,
                'user_id'            => $draft->user_id,
                'parent_id'          => $draft->parent_id,
                'previous_holders'   => [$user->id], 
                'current_holders'    => array_values(array_unique(array_merge([$user->id], $fullChainIds))),
                'treatment_holders'  => $nextHolders, 
                'circuit_type' => 'projet',           
                'circuit_path' => $fullChainIds,
            ]);

            // 5. Enregistrement des destinataires
            $recipientsData = is_array($draft->destinataires) ? $draft->destinataires : json_decode($draft->destinataires, true);
            if (!empty($recipientsData)) {
                foreach ($recipientsData as $dest) {
                    Destinataires::create([
                        'memo_id'   => $memo->id,
                        'entity_id' => $dest['entity_id'],
                        'action'    => $dest['action'],
                    ]);
                }
            }

            // 6. Historique
            Historiques::create([
                'user_id'          => $user->id,
                'memo_id'          => $memo->id,
                'visa'             => 'Initialisation Circuit Particulier', 
                'workflow_comment' => $finalComment ?? 'Démarrage de la chaîne de validation personnalisée',
            ]);

            // 7. Notifications
            foreach (User::whereIn('id', $nextHolders)->get() as $recipient) {
                try {
                    $recipient->notify(new MemoActionNotification($memo, 'envoyer', $user));
                } catch (\Exception $e) {}
            }

            // Envoi Email
            $this->sendEmailNotification($memo, $nextHolders, $user);

            // 8. Finalisation
            $draft->delete();
            $this->closeModalTrois();
            $this->isEditing = false;
            $this->dispatch('notify', message: "Circuit particulier initié et transmis au premier intervenant.");
        }
    }


    // =================================================================================================
    // 12. GESTION DES EMAILS (PHPMAILER)
    // =================================================================================================

    /**
     * Envoie une notification par email aux destinataires du mémo
     */
    private function sendEmailNotification($memo, $nextHolders, $sender)
    {
        // Récupérer tous les destinataires
        $recipients = User::whereIn('id', $nextHolders)
            ->whereNotNull('email')
            ->get();

        if ($recipients->isEmpty()) {
            Log::warning("Aucun destinataire avec email valide pour le mémo #{$memo->id}");
            return;
        }

        // Récupérer les entités destinataires pour l'affichage
        $recipientsData = is_array($memo->destinataires) 
            ? $memo->destinataires 
            : json_decode($memo->destinataires, true) ?? [];
        
        $entitiesNames = collect($recipientsData)->map(function($dest) {
            $entity = Entity::find($dest['entity_id']);
            return $entity ? $entity->name : 'Inconnu';
        })->implode(', ');

        // Préparer les informations communes
        $memoType = $this->memo_type === 'projet' ? 'Circuit Particulier' : 'Circuit Standard';
        $actionRequired = $this->memo_type === 'projet' ? 'Validation requise dans le circuit' : 'Action requise';

        foreach ($recipients as $recipient) {
            try {
                $mail = new PHPMailer(true);

                // Configuration SMTP
                $mail->isSMTP();
                $mail->Host = env('MAIL_HOST', 'smtp.gie.local');
                $mail->SMTPAuth = false;
                $mail->Port = env('MAIL_PORT', 25);
                $mail->SMTPSecure = false;
                $mail->SMTPAutoTLS = false;
                $mail->CharSet = 'UTF-8';
                
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ];

                // Expéditeur
                $mail->setFrom(
                    env('MAIL_FROM_ADDRESS', 'cbc_infos@groupecommercialbank.com'),
                    env('MAIL_FROM_NAME', 'CBC MEMOS')
                );

                // Destinataire
                $mail->addAddress($recipient->email, $recipient->first_name . ' ' . $recipient->last_name);

                // Contenu de l'email
                $mail->isHTML(true);
                $mail->Subject = "Nouveau Mémorandum : {$memo->object}";
                
                $mail->Body = $this->buildEmailBody($memo, $recipient, $sender, $memoType, $actionRequired, $entitiesNames);
                $mail->AltBody = $this->buildEmailAltBody($memo, $recipient, $sender, $memoType);

                // Envoi
                $mail->send();
                
                Log::info("Email envoyé avec succès à {$recipient->email} pour le mémo #{$memo->id}");

            } catch (Exception $e) {
                Log::error("Erreur lors de l'envoi d'email à {$recipient->email} pour le mémo #{$memo->id}: {$mail->ErrorInfo}");
            }
        }
    }

    private function buildEmailBody($memo, $recipient, $sender, $memoType, $actionRequired, $entitiesNames)
    {
        // Données préparées
        $senderName = $sender->first_name . ' ' . $sender->last_name;
        $senderPoste = is_object($sender->poste) ? $sender->poste->value : $sender->poste;
        $recipientName = $recipient->first_name . ' ' . $recipient->last_name;
        $dateEnvoi = now()->translatedFormat('d F Y à H:i'); // Format date plus naturel (ex: 12 Janvier 2024)
        
        $memoUrl = route('dashboard', [
            'view' => 'memos-content', 
            'tab'  => 'incoming'      
        ]);

        // Charte Graphique CBC
        $cBlue      = '#1e3a8a'; // Bleu Nuit
        $cGold      = '#daaf2c'; // Or CBC
        $cGoldLight = '#fefce8'; // Fond jaune très pâle pour l'alerte
        $cText      = '#374151'; // Gris foncé (plus doux que noir)
        $cLabel     = '#6b7280'; // Gris moyen
        $cBg        = '#f1f5f9'; // Fond de la page (Gris-Bleu très clair)

        return "
        <!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Notification CBC Memos</title>
            <style>
                /* Reset */
                body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
                table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
                img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
                
                /* Global */
                body { background-color: $cBg; font-family: 'Segoe UI', 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; width: 100% !important; }
                
                /* Typography */
                h1, h2, h3, p { margin: 0; }
                
                /* Components */
                .wrapper { width: 100%; background-color: $cBg; padding: 40px 0; }
                .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025); }
                
                /* Header */
                .header { background-color: #ffffff; padding: 30px 40px; text-align: center; border-bottom: 1px solid #f3f4f6; }
                .logo-text { font-size: 26px; font-weight: 800; color: $cBlue; letter-spacing: -0.5px; }
                .logo-accent { color: $cGold; }
                
                /* Content */
                .content { padding: 40px; }
                .welcome-text { font-size: 16px; color: $cText; margin-bottom: 25px; line-height: 1.6; }
                
                /* The Memo Box */
                .memo-container { background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; margin-bottom: 30px; }
                .memo-header { background-color: $cBlue; padding: 15px 25px; }
                .memo-type { color: $cGold; font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 1px; }
                
                .memo-body { padding: 25px; }
                .memo-title { font-size: 20px; font-weight: 700; color: #111827; margin-bottom: 20px; line-height: 1.4; }
                
                /* Grid Info */
                .info-table { width: 100%; }
                .info-td-icon { width: 24px; vertical-align: top; padding-right: 10px; padding-bottom: 12px; font-size: 18px; }
                .info-td-content { padding-bottom: 15px; vertical-align: top; }
                .info-label { font-size: 11px; color: $cLabel; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 2px; }
                .info-value { font-size: 14px; color: $cText; font-weight: 500; }
                
                /* Action Box */
                .action-wrapper { text-align: center; margin-top: 10px; padding: 20px; background-color: $cGoldLight; border-radius: 8px; border: 1px dashed $cGold; }
                .action-label { color: #854d0e; font-size: 13px; font-weight: 600; margin-bottom: 15px; display: block; }
                
                /* Button */
                .btn { background-color: $cGold; color: #000000; display: inline-block; padding: 14px 35px; border-radius: 6px; font-weight: 700; text-decoration: none; font-size: 15px; transition: all 0.3s ease; box-shadow: 0 4px 6px rgba(218, 175, 44, 0.2); }
                
                /* Footer */
                .footer { background-color: $cBg; padding: 30px; text-align: center; font-size: 12px; color: #9ca3af; }
                .footer-links a { color: $cBlue; text-decoration: none; font-weight: 500; }
                
            </style>
        </head>
        <body>
            <div class='wrapper'>
                <table role='presentation' border='0' cellpadding='0' cellspacing='0' width='100%'>
                    <tr>
                        <td align='center'>
                            <div class='container'>
                                
                                <!-- HEADER : LOGO MINIMALISTE -->
                                <div class='header'>
                                    <div class='logo-text'>CBC <span class='logo-accent'>MEMOS</span></div>
                                </div>

                                <!-- BODY -->
                                <div class='content'>
                                    <p class='welcome-text'>
                                        Bonjour <strong>{$recipientName}</strong>,<br>
                                        Un nouveau document a été transmis à votre attention via le circuit <strong>{$memoType}</strong>.
                                    </p>

                                    <!-- CADRE DU MEMO -->
                                    <div class='memo-container'>
                                        <!-- Bandeau titre Bleu -->
                                        <div class='memo-header'>
                                            <span class='memo-type'>Mémorandum Interne</span>
                                        </div>
                                        
                                        <div class='memo-body'>
                                            <!-- Objet -->
                                            <div class='memo-title'>{$memo->object}</div>

                                            <!-- Détails avec icônes (HTML entities) -->
                                            <table class='info-table' border='0' cellpadding='0' cellspacing='0'>
                                                <tr>
                                                    <td class='info-td-icon'>👤</td>
                                                    <td class='info-td-content'>
                                                        <span class='info-label'>Expéditeur</span>
                                                        <div class='info-value'>{$senderName} <span style='color:#9ca3af; font-size:12px;'>— {$senderPoste}</span></div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class='info-td-icon'>🎯</td>
                                                    <td class='info-td-content'>
                                                        <span class='info-label'>Concerne</span>
                                                        <div class='info-value'>{$memo->concern}</div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class='info-td-icon'>🏢</td>
                                                    <td class='info-td-content'>
                                                        <span class='info-label'>Entités Destinataires</span>
                                                        <div class='info-value'>{$entitiesNames}</div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class='info-td-icon'>📅</td>
                                                    <td class='info-td-content' style='padding-bottom:0;'>
                                                        <span class='info-label'>Date d'émission</span>
                                                        <div class='info-value'>{$dateEnvoi}</div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- ZONE D'ACTION -->
                                    <div class='action-wrapper'>
                                        <span class='action-label'>ACTION REQUISE : " . strtoupper($actionRequired) . "</span>
                                        <a href='{$memoUrl}' class='btn'>Traiter le Mémorandum</a>
                                    </div>
                                    
                                    <p style='text-align:center; font-size:12px; color:#9ca3af; margin-top:25px;'>
                                        Pour des raisons de sécurité, ce lien expirera si votre session n'est pas active.
                                    </p>
                                </div>
                            </div>
                            
                            <!-- FOOTER -->
                            <div class='footer'>
                                <p style='margin-bottom:10px;'><strong>Commercial Bank Cameroun</strong></p>
                                <p style='margin-top:15px;'>Ceci est un message automatique, merci de ne pas y répondre.</p>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </body>
        </html>
        ";
    }

    private function buildEmailAltBody($memo, $recipient, $sender, $memoType)
    {
        $senderName = $sender->first_name . ' ' . $sender->last_name;
        $recipientName = $recipient->first_name . ' ' . $recipient->last_name;
        
        return "
        CBC MEMOS - Nouveau Mémorandum
        Bonjour {$recipientName},
        Vous avez reçu un nouveau mémorandum :
        OBJET : {$memo->object}
        EXPÉDITEUR : {$senderName}
        CONCERNE : {$memo->concern}
        TYPE : {$memoType}
        DATE : " . now()->format('d/m/Y à H:i') . "
        Veuillez vous connecter à la plateforme CBC MEMOS pour consulter le contenu complet et effectuer l'action requise.
        ---
        Commercial Bank Cameroun
        Cet email a été généré automatiquement. Merci de ne pas y répondre.
            ";
    }


    // =================================================================================================
    // 13. LOGIQUE PDF & APERÇU
    // =================================================================================================

    private function getPdfData($memo)
    {
        // 1. Transformer le JSON des destinataires en collection d'objets pour la vue PDF
        $recipientsJson = is_array($memo->destinataires) 
            ? $memo->destinataires 
            : json_decode($memo->destinataires, true) ?? [];

        $formattedRecipients = collect($recipientsJson)->map(function($item) {
            $entity = Entity::find($item['entity_id']);
            return (object)[
                'action' => $item['action'],
                'entity' => $entity
            ];
        });

        // 2. Trouver le directeur de l'entité
        $director = User::where('dir_id', $memo->user->dir_id)
                        ->where('poste', 'Directeur')
                        ->first();

        return [
            'memo'               => $memo,
            'recipientsByAction' => $formattedRecipients->groupBy('action'),
            'date'               => $memo->created_at ? $memo->created_at->format('d/m/Y') : now()->format('d/m/Y'),
            'logo'               => $this->getLogoBase64(),
            'director'           => $director,
        ];
    }

    public function viewMemo($id)
    {
        // On récupère le brouillon (on charge l'utilisateur et son entité pour le header du PDF)
        $memo = DraftedMemo::with(['user.dir'])->findOrFail($id);
        $this->memo_id = $memo->id;

        // Génération du PDF avec les données formatées
        $pdf = Pdf::loadView('pdf.memo-layout', $this->getPdfData($memo))
            ->setPaper('a4', 'portrait');

        $this->pdfBase64 = base64_encode($pdf->output());
        $this->isViewingPdf = true;
        $this->isEditing = false;
    }

    public function closePdfView()
    {
        $this->isViewingPdf = false;
        $this->pdfBase64 = '';
    }

    public function downloadMemoPDF()
    {
        $memo = DraftedMemo::findOrFail($this->memo_id);
        $pdf = Pdf::loadView('pdf.memo-layout', $this->getPdfData($memo));
        
        return response()->streamDownload(
            fn() => print($pdf->output()), 
            "Brouillon_Memo_{$memo->id}.pdf"
        );
    }


    // =================================================================================================
    // 14. HELPERS & UTILITAIRES
    // =================================================================================================

    public function getReplacementRights($memo)
    {
        $user = Auth::user();
        $today = Carbon::now()->format('Y-m-d');
        $prev = is_array($memo->previous_holders) ? $memo->previous_holders : json_decode($memo->previous_holders, true);
        
        if (empty($prev)) return null;
        $lastSender = User::find(end($prev));
        if (!$lastSender) return null;

        $rep = ReplacesUser::where('user_id_replace', $user->id)
            ->where('date_begin_replace', '<=', $today)
            ->where('date_end_replace', '>=', $today)
            ->where('user_id', $lastSender->manager_id)
            ->first();

        if ($rep) {
            $replaced = User::find($rep->user_id);
            return [
                'is_active' => true,
                'original_user' => $replaced,
                'actions_allowed' => is_array($rep->action_replace) ? $rep->action_replace : explode(',', (string)$rep->action_replace),
            ];
        }
        return null;
    }

    private function getLogoBase64() {
        $path = public_path('images/logo.jpg');
        return file_exists($path) ? 'data:image/jpg;base64,' . base64_encode(file_get_contents($path)) : null;
    }

    private function resolveUserAvailability($user, $activeReplacements)
    {
        if (!$user) return null;
        $replacement = $activeReplacements->get($user->id);

        if ($replacement) {
            $replacingUser = User::find($replacement->user_id_replace);
            return [
                'original'    => (object)$user->only(['id', 'first_name', 'last_name', 'poste']),
                'effective'   => (object)$replacingUser->only(['id', 'first_name', 'last_name', 'poste']),
                'is_replaced' => true
            ];
        }

        return [
            'original'    => (object)$user->only(['id', 'first_name', 'last_name', 'poste']),
            'effective'   => (object)$user->only(['id', 'first_name', 'last_name', 'poste']),
            'is_replaced' => false
        ];
    }


    // =================================================================================================
    // 15. RENDU FINAL
    // =================================================================================================

    public function render()
    {
        $memos = DraftedMemo::query()
            ->where('user_id', Auth::id())
            ->when($this->search, function($query) {
                $query->where('object', 'like', '%'.$this->search.'%')
                      ->orWhere('concern', 'like', '%'.$this->search.'%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        return view('livewire.memos.drafted-memos', [
            'memos' => $memos, 
            'entities' => $this->allEntities
        ]);
    }
}