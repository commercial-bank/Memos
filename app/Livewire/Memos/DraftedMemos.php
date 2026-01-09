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
   

    // --- États de navigation ---
    public $isEditing = false; 
    public $isViewingPdf = false;
    public $search = '';

    // --- États des Modals ---
    public $isOpen3 = false;     
    public $isOpen4 = false;     

    // --- Données du Mémo (Formulaire) ---
    public $memo_id = null;

    // --- Propriétés pour le circuit particulier ---
    public $selected_project_path = []; // Tableau d'IDs ordonnés [ID_1, ID_2, ID_3]
    public $search_project_user = '';   // Pour la recherche de membres

    #[Rule('required|string|max:255')]
    public string $object = '';

    #[Rule('nullable|string|max:255')]
    public string $concern = '';

    #[Rule('required|string')]
    public string $content = '';

    // --- Gestion des Destinataires ---
    public $recipients = []; 
    public $newRecipientEntity = '';
    public $newRecipientAction = '';
    public $allEntities = []; 

    // --- Gestion des Pièces Jointes ---
    // $attachments est utilisé pour les nouveaux uploads (WithFileUploads)
    public $attachments = []; 
    // $existingAttachments stocke les chemins des fichiers déjà en base de données
    public $existingAttachments = []; 

    // --- Workflow & Assignation ---
    public $memo_type = 'standard'; 
    public $workflow_comment = '';
    public $selected_visa = ''; 
    public $managerData = null;     
    public $projectUsersList = [];  
    public $selected_project_users = [];

    // --- Données pour l'Aperçu PDF ---
    public $pdfBase64 = '';
    public $date;
    public $user_entity_name;

    // --- Options Statiques ---
    public $actionsList = ['Faire le nécessaire', 'Prendre connaissance', 'Prendre position', 'Décider'];

    public $isSecretary = false;
    public $standardRecipientsList = []; // Liste Director + Sous-directeurs
    public $selected_standard_users = []; // Les IDs sélectionnés en mode Standard

    public function mount()
    {
        $this->allEntities = Entity::orderBy('name', 'asc')->get(); 
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // =========================================================
    // LOGIQUE D'ÉDITION
    // =========================================================

    public function editMemo($id)
    {
        // On utilise DraftedMemo au lieu de Memo
        // Note: On ne charge plus 'destinataires.entity' car c'est du JSON maintenant
        $memo = DraftedMemo::findOrFail($id);
        
        $this->memo_id = $memo->id;
        $this->object = $memo->object;
        $this->concern = $memo->concern ?? '';
        $this->content = $memo->content;
        
        // Gestion des pièces jointes (Déjà casté en array si configuré dans le Model)
        $pj = $memo->pieces_jointes;
        $this->existingAttachments = is_array($pj) ? $pj : (json_decode($pj, true) ?? []);
        
        $this->attachments = [];

        // Chargement des destinataires depuis la colonne JSON
        // On doit rajouter le nom de l'entité pour l'affichage dans le tableau de l'interface
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
        // Si votre modèle DraftedMemo a le cast 'array' pour destinataires et pieces_jointes, 
        // pas besoin de json_encode.
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

        // Suppression de l'ancienne logique de la table 'destinataires' 
        // car tout est centralisé dans le JSON du brouillon.

        $this->isEditing = false;
        $this->dispatch('notify', message: "Brouillon mis à jour avec succès !");
    }

    // =========================================================
    // LOGIQUE D'ENVOI (WORKFLOW)
    // =========================================================

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

    // --- Méthodes de gestion de la liste ---
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
            // 1. Validation
            // 1. DÉFINITION DES RÈGLES DE VALIDATION DYNAMIQUES
            $rules = [
                'workflow_comment' => 'nullable|string|max:1000',
            ];

            // Si on est en mode PROJET : On exige la liste du projet
            if ($this->memo_type === 'projet') {
                $rules['selected_project_path'] = 'required|array|min:1';
            } 
            // Si on est en mode STANDARD et qu'on est SECRÉTAIRE : On exige la liste standard
            elseif ($this->memo_type === 'standard' && $this->isSecretary) {
                $rules['selected_standard_users'] = 'required|array|min:1';
            }

            // 2. EXÉCUTION DE LA VALIDATION
            $this->validate($rules, [
                'selected_project_path.required' => 'Veuillez ajouter au moins un collaborateur au circuit.',
                'selected_project_path.min' => 'Le circuit doit comporter au moins une personne.',
                'selected_standard_users.required' => 'Veuillez sélectionner un destinataire dans la liste hiérarchique.',
            ]);

            // 2. Récupérer le BROUILLON (DraftedMemo)
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

            // 3. Déterminer les futurs détenteurs (Next Holders)
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

            // 4. CRÉATION DU MÉMO (Transfert de DraftedMemo vers Memo)
            // On convertit le brouillon en mémo officiel
            $memo = Memo::create([
                'object'             => $draft->object,
                'reference'          => $draft->reference,
                'concern'            => $draft->concern,
                'content'            => $draft->content,
                'status'             => 'envoyer',
                'workflow_direction' => 'sortant',
                'pieces_jointes'     => $draft->pieces_jointes, // Array ou JSON selon cast
                'user_id'            => $draft->user_id,
                'parent_id'          => $draft->parent_id,
                // Gestion des détenteurs
                'previous_holders'   => [$user->id], 
                'current_holders'    => array_unique($nextHolders), // Puisque c'est le 1er envoi
                'treatment_holders'  => array_unique($nextHolders),
            ]);

            // 5. ENREGISTREMENT DES DESTINATAIRES DANS LA TABLE 'destinataires'
            // On récupère les destinataires du JSON du brouillon
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

            // 6. CRÉATION DE L'HISTORIQUE
            Historiques::create([
                'user_id'          => $user->id,
                'memo_id'          => $memo->id,
                'visa'             => 'valider', // Forcé en "valider"
                'workflow_comment' => $finalComment ?? 'R.A.S',
            ]);

          
             // 7. Notification uniquement au premier maillon de la chaîne
            foreach (User::whereIn('id', $nextHolders)->get() as $recipient) {
                try {
                    $recipient->notify(new MemoActionNotification($memo, 'envoyer', $user));
                } catch (\Exception $e) {}
            }

            // *** ENVOI EMAIL STANDARD ICI ***
            $this->sendEmailNotification($memo, $nextHolders, $user);

            // 8. SUPPRESSION DU BROUILLON
            $draft->delete();

            // 9. FINALISATION
            $this->closeModalTrois();
            $this->isEditing = false;
            $this->dispatch('notify', message: "Mémo transmis et brouillon supprimé avec succès.");



            } elseif ($this->memo_type === 'projet') {

            // 1. Identification de la secrétaire de direction (Dernier maillon par défaut)
            $directionSecretary = User::where('dir_id', $user->dir_id)
                ->where('poste', 'like', '%Secretaire%')
                ->first();

            // 2. Construction de la chaîne complète (Ordre choisi + Secrétaire à la fin)
            $fullChainIds = $this->selected_project_path;

            if ($directionSecretary && !in_array($directionSecretary->id, $fullChainIds)) {
                $fullChainIds[] = $directionSecretary->id; // On l'ajoute à la fin
            }

            // 3. Le premier intervenant de la chaîne reçoit le mémo en premier (Logique Séquentielle)
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
                // On met TOUTE la chaîne dans current_holders pour que tout le monde puisse suivre le mémo
                'current_holders'    => array_values(array_unique(array_merge([$user->id], $fullChainIds))),
                // Seul le PREMIER maillon peut traiter pour l'instant
                'treatment_holders'  => $nextHolders, 
            ]);

            // 5. Enregistrement des destinataires (Entités finales)
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

            // 6. Création de l'historique spécifique au circuit particulier
            Historiques::create([
                'user_id'          => $user->id,
                'memo_id'          => $memo->id,
                'visa'             => 'Initialisation Circuit Particulier', 
                'workflow_comment' => $finalComment ?? 'Démarrage de la chaîne de validation personnalisée',
            ]);

            // 7. Notification uniquement au premier maillon de la chaîne
            foreach (User::whereIn('id', $nextHolders)->get() as $recipient) {
                try {
                    $recipient->notify(new MemoActionNotification($memo, 'envoyer', $user));
                } catch (\Exception $e) {}
            }

            

            // 8. Suppression du brouillon et finalisation
            $draft->delete();
            $this->closeModalTrois();
            $this->isEditing = false;
            $this->dispatch('notify', message: "Circuit particulier initié et transmis au premier intervenant.");
        }
   
        
    }

    /**
     * Envoie une notification par email aux destinataires du mémo
     * 
     * @param Memo $memo Le mémo à notifier
     * @param array $nextHolders IDs des utilisateurs destinataires
     * @param User $sender L'utilisateur qui envoie le mémo
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
                
                // Corps HTML de l'email
                $mail->Body = $this->buildEmailBody($memo, $recipient, $sender, $memoType, $actionRequired, $entitiesNames);
                
                // Version texte brut (fallback)
                $mail->AltBody = $this->buildEmailAltBody($memo, $recipient, $sender, $memoType);

                // Envoi
                $mail->send();
                
                Log::info("Email envoyé avec succès à {$recipient->email} pour le mémo #{$memo->id}");

            } catch (Exception $e) {
                Log::error("Erreur lors de l'envoi d'email à {$recipient->email} pour le mémo #{$memo->id}: {$mail->ErrorInfo}");
            }
        }
    }

    /**
     * Construit le corps HTML de l'email
     */
    private function buildEmailBody($memo, $recipient, $sender, $memoType, $actionRequired, $entitiesNames)
    {
        $senderName = $sender->first_name . ' ' . $sender->last_name;
        $senderPoste = is_object($sender->poste) ? $sender->poste->value : $sender->poste;
        $recipientName = $recipient->first_name . ' ' . $recipient->last_name;
        $memoUrl = route('dashboard'); // Ajustez selon votre routing
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
                .header { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); padding: 30px; text-align: center; }
                .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; }
                .header p { color: #dbeafe; margin: 5px 0 0 0; font-size: 14px; }
                .content { padding: 30px; background: #f8fafc; }
                .memo-box { background: white; border-left: 4px solid #daaf2c; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .memo-title { font-size: 18px; font-weight: bold; color: #1e3a8a; margin-bottom: 15px; }
                .info-row { margin: 10px 0; padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
                .info-label { font-weight: 600; color: #6b7280; font-size: 13px; text-transform: uppercase; }
                .info-value { color: #111827; margin-top: 3px; }
                .action-box { background: #fef3c7; border: 2px solid #daaf2c; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center; }
                .action-box strong { color: #92400e; font-size: 16px; }
                .btn { display: inline-block; padding: 12px 30px; background: #daaf2c; color: #000; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 20px 0; }
                .btn:hover { background: #b8941f; }
                .footer { background: #1f2937; color: #9ca3af; padding: 20px; text-align: center; font-size: 12px; }
                .footer a { color: #60a5fa; text-decoration: none; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📋 CBC MEMOS</h1>
                    <p>Système de Gestion des Mémorandums</p>
                </div>
                
                <div class='content'>
                    <p>Bonjour <strong>{$recipientName}</strong>,</p>
                    
                    <p>Vous avez reçu un nouveau mémorandum qui nécessite votre attention.</p>
                    
                    <div class='memo-box'>
                        <div class='memo-title'>📄 {$memo->object}</div>
                        
                        <div class='info-row'>
                            <div class='info-label'>Expéditeur</div>
                            <div class='info-value'>{$senderName} - {$senderPoste}</div>
                        </div>
                        
                        <div class='info-row'>
                            <div class='info-label'>Concerne</div>
                            <div class='info-value'>{$memo->concern}</div>
                        </div>
                        
                        <div class='info-row'>
                            <div class='info-label'>Type de Circuit</div>
                            <div class='info-value'>{$memoType}</div>
                        </div>
                        
                        <div class='info-row'>
                            <div class='info-label'>Entités Destinataires</div>
                            <div class='info-value'>{$entitiesNames}</div>
                        </div>
                        
                        <div class='info-row'>
                            <div class='info-label'>Date d'envoi</div>
                            <div class='info-value'>" . now()->format('d/m/Y à H:i') . "</div>
                        </div>
                    </div>
                    
                    <div class='action-box'>
                        <strong>⚠️ {$actionRequired}</strong>
                    </div>
                    
                    <div style='text-align: center;'>
                        <a href='http://127.0.0.1:8000/dashboard?tab=memos&view=memos-content' class='btn'>Consulter le Mémo</a>
                    </div>
                    
                    <p style='margin-top: 30px; font-size: 13px; color: #6b7280;'>
                        <strong>Note :</strong> Ce mémo requiert votre traitement dans les meilleurs délais. 
                        Veuillez vous connecter à la plateforme pour consulter le contenu complet et effectuer l'action requise.
                    </p>
                </div>
                
                <div class='footer'>
                    <p><strong>Commercial Bank Cameroun</strong></p>
                    <p>Cet email a été généré automatiquement par le système CBC MEMOS. Merci de ne pas y répondre.</p>
                    <p style='margin-top: 10px;'>
                        <a href='{$memoUrl}'>Accéder à la plateforme</a>
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Construit la version texte brut de l'email (fallback)
     */
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

    // =========================================================
    // APERÇU PDF
    // =========================================================

    private function getPdfData($memo)
    {
        // 1. Transformer le JSON des destinataires en collection d'objets pour la vue PDF
        // La vue PDF attend probablement $dest->entity->name et $dest->action
        $recipientsJson = is_array($memo->destinataires) 
            ? $memo->destinataires 
            : json_decode($memo->destinataires, true) ?? [];

        $formattedRecipients = collect($recipientsJson)->map(function($item) {
            $entity = Entity::find($item['entity_id']);
            // On crée un objet "factice" qui imite le comportement du modèle Destinataire
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

    /**
     * Ouvre l'aperçu du mémo
     */
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

    // =========================================================
    // HELPERS
    // =========================================================

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

    public function addRecipient()
    {
        $this->validate(['newRecipientEntity' => 'required', 'newRecipientAction' => 'required']);
        $entity = Entity::find($this->newRecipientEntity);
        $this->recipients[] = [
            'entity_id'   => $entity->id,
            'entity_name' => $entity->name,
            'action'      => $this->newRecipientAction
        ];
        $this->reset(['newRecipientEntity', 'newRecipientAction']);
    }

    public function removeRecipient($index) {
        unset($this->recipients[$index]);
        $this->recipients = array_values($this->recipients);
    }

    public function deleteMemo($id) { $this->memo_id = $id; $this->isOpen4 = true; }
    
    public function del() {
        DraftedMemo::where('id', $this->memo_id)->where('user_id', Auth::id())->delete();
        $this->isOpen4 = false;
        $this->dispatch('notify', message: "Mémo supprimé.");
    }

    public function closeModalTrois() { $this->isOpen3 = false; }
    public function closeModalQuatre() { $this->isOpen4 = false; }

    

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