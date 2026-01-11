<?php

namespace App\Livewire\Memos;

use Carbon\Carbon;
use App\Models\Memo;
use App\Models\User;
use App\Models\Entity;
use Livewire\Component;
use App\Models\References;
use App\Models\DraftedMemo;
use App\Models\Historiques;
use App\Models\MemoHistory;
use Illuminate\Support\Str;
use App\Models\ReplacesUser;
use Livewire\WithPagination;
use App\Models\Destinataires;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use App\Traits\ManageFavorites;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\PHPMailer;
use App\Models\BlocEnregistrements;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Notifications\MemoActionNotification;

class IncomingMemos extends Component
{
    use ManageFavorites, WithPagination;
    use WithFileUploads;

    // =========================================================
    // 1. PROPRIÉTÉS DU COMPOSANT
    // =========================================================

    public $search = '';
    public $isViewingPdf = false;
    public $isEditing = false;

    // === AJOUTEZ CETTE LIGNE ICI ===
    public $suggestedNextUser = null; 

    // --- États des Modals ---
    public $isOpen = false;        
    public $isOpen2 = false;       
    public $isOpen3 = false;       
    public $isOpen4 = false;       
    public $isOpenReject = false;  
    public $isOpenTrans = false;   
    public $selectedMemo = null;
    

    // --- Données du Formulaire ---
    public $memo_id = null;
    public $isOpenHistory = false;  

    // --- Données du Mémo ---
    public $memoHistory = [];
    
    #[Rule('required|string|max:255')]
    public string $object = '';

    #[Rule('required|string|max:255')]
    public string $concern = '';

    #[Rule('required|string')]
    public string $content = '';

    // --- Variables de Workflow ---
    public $memo_type = 'standard'; 
    public $workflow_comment = '';
    public $selected_visa = '';
    public $managerData = null;     
    public $projectUsersList = [];  
    public $selected_project_users = [];
    public $target_users_ids = [];
    public $isCircuitLocked = false; 

    // --- Variables de Rejet ---
    public $reject_comment = '';
    public $reject_mode = '';

    // --- Variables de Transmission ---
    public $transRecipients = [];
    public $generatedReference = '';

    // --- Destinataires ---
    public $recipients = []; 
    public $newRecipientEntity = '';
    public $newRecipientAction = '';
    public $allEntities = []; 
    public $actionsList = ['Faire le nécessaire', 'Prendre connaissance', 'Prendre position', 'Décider'];

    // --- Pièces Jointes ---
    public $attachments = [];  
    public $newAttachments = [];    
    public $existingAttachments = []; 

 

    // --- Données d'Affichage ---
    public $date;
    public $user_service;
    public $user_first_name;
    public $user_last_name;
    public $user_entity_name;

    public $pdfBase64 = '';

    
    // --- Options Statiques ---
    public $visaOptions = [
        'Vu' => 'Vu (Simple transmission)',
        'Vu & Accord' => 'Vu & D\'accord',
        'Vu & Pas d\'accord' => 'Vu & Pas d\'accord',
    ];

    public $isSecretary = false;
    public $standardRecipientsList = []; // Liste Director + Sous-directeurs
    public $selected_standard_users = []; // Les IDs sélectionnés en mode Standard

    


    // =========================================================
    // 2. INITIALISATION
    // =========================================================

    public function mount()
    {
        $this->allEntities = Entity::orderBy('name')->get();
    }


    // =========================================================
    // 2. ACTIONS DE NAVIGATION
    // =========================================================

    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
 * Logique partagée pour récupérer les données du PDF
 */
    private function getPdfData($memo)
    {
        // On cherche le directeur de l'entité du créateur du mémo
        $director = User::where('dir_id', $memo->user->dir_id)
                        ->where('poste', 'Directeur')
                        ->first();

        return [
            'memo'               => $memo,
            'recipientsByAction' => $memo->destinataires->groupBy('action'),
            'date'               => $memo->created_at->format('d/m/Y'),
            'logo'               => $this->getLogoBase64(),
            'director'           => $director, // On passe l'objet director à la vue
        ];
    }

    /**
     * Ouvre l'aperçu du mémo
     */
    public function viewMemo($id)
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($id);
        $this->memo_id = $memo->id;

        // Utilisation de la méthode partagée
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

    private function getLogoBase64() {
        $path = public_path('images/logo.jpg');
        return file_exists($path) ? 'data:image/jpg;base64,' . base64_encode(file_get_contents($path)) : null;
    }

    public function closeModal() 
    { 
        $this->isOpen = false; 
        $this->reset(['selectedMemo', 'memo_id', 'content', 'object']);
    }

    public function closeModalTrois() 
    { 
        $this->isOpen3 = false; 
        $this->reset(['projectUsersList', 'managerData', 'workflow_comment']);
    }

    public function viewHistory($id)
    {
        $this->memo_id = $id;
        
        $allRelatedMemoIds = Memo::where('parent_id', $id)
            ->orWhere('id', $id)
            ->pluck('id')
            ->toArray();

        $this->memoHistory = Historiques::with(['user', 'memo'])
            ->whereIn('memo_id', $allRelatedMemoIds)
            ->orderBy('created_at', 'desc') 
            ->get();

        $this->isOpenHistory = true;
    }

    public function closeHistoryModal() { 
        $this->isOpenHistory = false; $this->memoHistory = []; 
    }

    // =========================================================
    // 3. LOGIQUE D'ASSIGNATION & WORKFLOW
    // =========================================================

     public function assignMemo($id)
    {
        $this->memo_id = $id;
        $this->reset(['workflow_comment', 'selected_project_users', 'selected_standard_users', 'managerData', 'suggestedNextUser']);
        
        $memo = Memo::findOrFail($id);
        $currentUser = Auth::user();
        $today = Carbon::now()->format('Y-m-d');

        // Récupération des remplacements
        $activeReplacements = ReplacesUser::where('date_begin_replace', '<=', $today)
            ->where('date_end_replace', '>=', $today)
            ->get()
            ->keyBy('user_id');

        $this->memo_type = $memo->circuit_type ?? 'standard'; 

        // --- 2. CONFIGURATION DE L'INTERFACE SELON LA DÉTECTION ---

        if ($this->memo_type === 'projet') {

                // Mode PROJET détecté
                $this->isCircuitLocked = true;

                $path = is_array($memo->circuit_path) ? $memo->circuit_path : json_decode($memo->circuit_path, true);
                
            if (!empty($path)) 
            {
                // Trouver la position de l'utilisateur actuel (ou de celui qu'il remplace)
                // On cherche l'ID de l'utilisateur courant dans le tableau
                $currentIndex = array_search($currentUser->id, $path);
                
                // Si l'utilisateur n'est pas trouvé, peut-être est-il remplaçant ?
                if ($currentIndex === false) {
                    // Logique pour trouver si je remplace quelqu'un qui est dans la liste
                    // (Simplifié ici, mais à adapter selon votre logique ReplacesUser)
                }

                if ($currentIndex !== false && isset($path[$currentIndex + 1])) {
                    // LE PROCHAIN EST DÉFINI PAR L'INDEX SUIVANT
                    $nextUserId = $path[$currentIndex + 1];
                    
                    $nextUser = User::find($nextUserId);
                    
                    if ($nextUser) {
                        // On pré-remplit la sélection et on suggère l'utilisateur
                        $this->selected_project_users = [$nextUser->id];
                        $this->suggestedNextUser = $nextUser;
                        
                        // On limite la liste déroulante à CE SEUL utilisateur pour empêcher le changement
                        $this->projectUsersList = collect([
                            $this->resolveUserAvailability($nextUser, $activeReplacements)
                        ]);
                    }
                } else {
                    // Fin de chaîne ou erreur : On peut fallback sur la secrétaire par défaut si non définie
                    $this->addError('general', 'Fin du circuit défini ou utilisateur non trouvé dans le chemin.');
                }
            }

        } else {
            // Mode STANDARD (Comportement par défaut)
            $this->memo_type = 'standard';
            $this->suggestedNextUser = null;
            
            // Conversion sécurisée du poste pour la comparaison
            $posteString = is_object($currentUser->poste) ? $currentUser->poste->value : (string)$currentUser->poste;
            
            if ($posteString == 'Directeur' && !$currentUser->manager_id) {
                $this->isSecretary = true; 
                $this->standardRecipientsList = User::where('dir_id', $currentUser->dir_id)
                    ->where('poste', 'Secretaire') 
                    ->where('id', '!=', $currentUser->id)
                    ->orderBy('last_name')
                    ->get()
                    ->map(fn($user) => $this->resolveUserAvailability($user, $activeReplacements));
            } 
            elseif (Str::contains($posteString, 'Secretaire')) {
                $this->isSecretary = true;
                $this->standardRecipientsList = User::where('dir_id', $currentUser->dir_id)
                    ->where('id', '!=', $currentUser->id)
                    ->where(function ($q) use ($currentUser) {
                        $q->where('id', $currentUser->manager_id)
                        ->orWhere('poste', 'like', '%Directeur%')
                        ->orWhere('poste', 'like', '%Sous-Directeur%');
                    })
                    ->orderBy('last_name')
                    ->get()
                    ->map(fn($user) => $this->resolveUserAvailability($user, $activeReplacements));
            } 
            else {
                $this->isSecretary = false;
                if ($currentUser->manager_id) {
                    $manager = User::find($currentUser->manager_id);
                    $this->managerData = $this->resolveUserAvailability($manager, $activeReplacements);
                }
            }

            // Chargement de la liste projet standard (pour recherche manuelle si besoin)
            $excludeIds = array_filter([$currentUser->id, $currentUser->manager_id]);
            $this->projectUsersList = User::whereNotIn('id', $excludeIds)
                ->orderBy('last_name')
                ->get()
                ->map(fn($user) => $this->resolveUserAvailability($user, $activeReplacements));
        }

        $this->isOpen3 = true;
    }

    public function sendMemo()
    {
        // 1. Validation
        $this->validate([
            'workflow_comment' => 'nullable|string|max:1000',
            'selected_project_users' => 'required_if:memo_type,projet|array',
            'selected_standard_users' => 'required_if:isSecretary,true|array', 
        ]);

        $memo = Memo::findOrFail($this->memo_id);
        $user = Auth::user();
        $today = Carbon::now()->format('Y-m-d');
        
        // Gestion des remplacements (Logique identique pour trouver le N+1 effectif)
        $activeReplacements = ReplacesUser::where('date_begin_replace', '<=', $today)
            ->where('date_end_replace', '>=', $today)
            ->get()
            ->keyBy('user_id');

        // 3. Déterminer les PROCHAINS détenteurs (Next Holders)
        $nextHolders = [];
        if ($this->memo_type === 'standard') {

          if (!empty($this->selected_standard_users)) {
        
                $targets = User::whereIn('id', $this->selected_standard_users)->get();
                
                foreach ($targets as $target) {
                    $avail = $this->resolveUserAvailability($target, $activeReplacements);
                    // On ajoute l'ID de la personne effective (le titulaire ou son remplaçant)
                    if ($avail) $nextHolders[] = $avail['effective']->id;
                }

            }  
            // Cas 3 : Cas Standard (ni Directeur, ni Secrétaire)
            else {
                // On prend le Manager direct (N+1) habituel
                if ($user->manager_id) {
                    $manager = User::find($user->manager_id);
                    if ($manager) {
                        $avail = $this->resolveUserAvailability($manager, $activeReplacements);
                        $nextHolders[] = $avail['effective']->id;
                    }
                }
            }

        } elseif ($this->memo_type === 'projet') {
            
            $users = User::whereIn('id', $this->selected_project_users)->get();
            foreach ($users as $u) {
                $avail = $this->resolveUserAvailability($u, $activeReplacements);
                if ($avail) $nextHolders[] = $avail['effective']->id;
            }
        }

        if (empty($nextHolders)) {
            $this->addError('general', 'Aucun destinataire trouvé pour la transmission.');
            return;
        }

        // 4. MISE À JOUR DU MÉMO (LOGIQUE D'AJOUT SANS ÉCRASER)
        
        // Récupération des anciennes listes (ou tableau vide si null)
        $oldCurrentHolders = is_array($memo->current_holders) ? $memo->current_holders : [];
        $oldPreviousHolders = is_array($memo->previous_holders) ? $memo->previous_holders : [];

        // Préparation des nouvelles listes
        // array_unique(array_merge(...)) permet d'ajouter sans doublons
        $newCurrentHolders = array_unique(array_merge($oldCurrentHolders, $nextHolders));
        $newPreviousHolders = array_unique(array_merge($oldPreviousHolders, [$user->id]));

        $memo->update([
            'current_holders'   => $newCurrentHolders,   // Ajouté aux anciens
            'previous_holders'  => $newPreviousHolders,  // Ajouté aux anciens
            'treatment_holders' => array_unique($nextHolders), // ÉCRASÉ : Seuls les nouveaux peuvent traiter
            'status'            => 'envoyer',
        ]);

        // 5. CRÉATION DE L'HISTORIQUE
        Historiques::create([
            'user_id'          => $user->id,
            'memo_id'          => $memo->id,
            'visa'             => 'valider', 
            'workflow_comment' => $this->workflow_comment ?? 'Transmis au niveau supérieur',
        ]);

        // 6. BOUCLE D'ENVOI (Correction ici)
        // On récupère les utilisateurs destinataires (les ID dans $nextHolders)
        $recipientsList = User::whereIn('id', $nextHolders)->get();

        $notifType = "envoyer";
        $emailColor = '#2563eb'; // Bleu
        $emailTitle = "📨 Nouveau Mémo";
        $actionLabel = "Transmis pour traitement";

        // 6. NOTIFICATIONS
        foreach (User::whereIn('id', $nextHolders)->get() as $recipient) {
            try {
                $recipient->notify(new MemoActionNotification($memo, 'envoyer', $user));
                $this->sendRejectEmail($memo, $author, $user, $emailTitle, $emailColor, $actionLabel);
                
                $this->sendMemoEmail(
                    $memo, 
                    $recipient, // C'est lui qui reçoit le mail
                    $user,      // C'est vous qui avez fait l'action
                    $emailTitle, 
                    $emailColor, 
                    $actionLabel
                );

            } catch (\Exception $e) {}
        }

        $this->closeModalTrois();
        $this->dispatch('notify', message: "Mémo envoyer avec succès.");
        
        
    }

    // =========================================================
    // 4. SIGNATURE ÉLECTRONIQUE
    // =========================================================

    public function sign($id)
    {
        $memo = Memo::findOrFail($id);
        $user = Auth::user();
        $replacementContext = $this->getReplacementRights($memo);
        
        $token = now()->timestamp . "-" . Str::upper(Str::random(8));
        $historyData = "";

        if ($replacementContext && in_array('signer', $replacementContext['actions_allowed'])) {
            $titulaire = $replacementContext['original_user'];
            if ($titulaire->poste === 'Sous-Directeur') {
                $memo->signature_sd = 'SD-INT-' . $token;
            } elseif ($titulaire->poste === 'Directeur') {
                $memo->signature_dir = 'DIR-INT-' . $token;
                $memo->qr_code = (string) Str::uuid();
                $memo->status = 'envoyer';
            }
            $historyData = "Signature P/O de {$titulaire->full_name} par {$user->full_name}";
        } else {
             if ($user->poste === 'Sous-Directeur') {
                $memo->signature_sd = 'SD-' . $token;
                $historyData = "Signé par SD";
             } elseif ($user->poste === 'Directeur') {
                $memo->signature_dir = 'DIR-' . $token;
                $memo->qr_code = (string) Str::uuid();
                $memo->status = 'envoyer';
                $historyData = "Signé par DIR (Final)";
             }
        }

        $memo->save();
        Historiques::create([
            'user_id' => $user->id, 'memo_id' => $memo->id, 'visa' => 'Signé', 'workflow_comment' => $historyData
        ]);
        $this->dispatch('notify', message: "Signature apposée.");
    }

    // =========================================================
    // 5. TRANSMISSION (SECRÉTARIAT)
    // =========================================================

    public function transMemo($id)
    {
        $this->memo_id = $id;
        // On charge le mémo avec ses destinataires
        $memo = Memo::with('destinataires')->findOrFail($id);
        $today = Carbon::now()->format('Y-m-d');

        // 1. On récupère tous les IDs des entités destinataires
        $targetEntityIds = $memo->destinataires->pluck('entity_id')->toArray();

        // 2. On récupère les remplaçants actifs
        $activeReplacements = ReplacesUser::where('date_begin_replace', '<=', $today)
            ->where('date_end_replace', '>=', $today)
            ->get()
            ->keyBy('user_id');

        // 3. REQUÊTE CORRIGÉE :
        $this->transRecipients = User::where('is_active', true) // Uniquement les comptes actifs
            ->where(function($q) {
                // Sécurité pour le poste (si String ou Enum)
                $q->where('poste', 'Secretaire')
                ->orWhere('poste', 'like', '%Secretaire%');
            })
            ->where(function($query) use ($targetEntityIds) {
                // Logique croisée : l'entité peut être une Direction OU une Sous-Direction
                $query->whereIn('dir_id', $targetEntityIds)
                    ->orWhereIn('sd_id', $targetEntityIds);
            })
            ->get()
            ->map(fn($u) => $this->resolveUserAvailability($u, $activeReplacements));

        // 4. Vérification si on a trouvé du monde
        if ($this->transRecipients->isEmpty()) {
            $this->dispatch('notify', 
                message: "Aucun secrétariat trouvé pour les entités destinataires.", 
                type: 'error'
            ); 
            return; 
        }

        $this->generatedReference = $this->generateSmartReference($memo);
        $this->isOpenTrans = true;
    }

    public function closeTransModal()
    {
        $this->isOpenTrans = false;
        $this->reset(['generatedReference', 'transRecipients']);
    }

    public function confirmTrans()
    {
        $this->validate(['generatedReference' => 'required|string']);

        $memo = Memo::findOrFail($this->memo_id);
        $user = Auth::user();

        // IDs des secrétaires cibles (nouveaux détenteurs)
        $nextHoldersIds = collect($this->transRecipients)
            ->pluck('effective.id')
            ->unique()
            ->toArray();


        DB::transaction(function () use ($memo, $user, $nextHoldersIds) {
            
            $qrToken = (string) \Illuminate\Support\Str::uuid();

            // 2. Calcul des nouveaux 'current_holders' (AJOUT sans écraser)
            // On récupère les anciens, on fusionne avec les nouveaux, et on garde les IDs uniques
            $oldCurrentHolders = is_array($memo->current_holders) ? $memo->current_holders : [];
            $updatedCurrentHolders = array_values(array_unique(array_merge($oldCurrentHolders, $nextHoldersIds)));

            BlocEnregistrements::create([
                'nature_memo' => 'Memo Sortant',
                'date_enreg' => now()->format('d/m/Y'),
                'reference' => $this->generatedReference,
                'memo_id' => $memo->id,
                'user_id' => $user->id,
            ]);

            $memo->update([
                'workflow_direction' => 'entrant', 
                'current_holders'    => $updatedCurrentHolders, // Liste cumulée (Historique des mains)
                'treatment_holders'  => $nextHoldersIds,       // Liste remplacée (Qui a la main maintenant)
                'status' => 'transmis',
                'qr_code' => $qrToken, 
                'reference' => $this->generatedReference,
            ]);

            Historiques::create([
                'user_id' => $user->id, 
                'memo_id' => $memo->id, 
                'visa' => 'Enregistré', 
                'workflow_comment' => "Réf: " . $this->generatedReference
            ]);

        });

         // E. NOTIFICATION DES SECRÉTAIRES DESTINATAIRES
        $recipients = \App\Models\User::whereIn('id', $nextHoldersIds)->get();

        foreach ($recipients as $recipient) {
            try {
                // On utilise votre système MemoActionNotification
                // Le type 'transmis' affichera le message avec la référence officielle
                $recipient->notify(new \App\Notifications\MemoActionNotification(
                    $memo, 
                    'transmis', 
                    $user
                ));
            } catch (\Exception $e) {
                // Log de l'erreur si la notification échoue, mais on ne bloque pas le processus
                \Illuminate\Support\Facades\Log::error("Échec notification secrétaire ID {$recipient->id} : " . $e->getMessage());
            }
        }

        $this->closeTransModal();
        $this->dispatch('notify', message: "Enregistré et transmis !");
    }

        /**
     * Génère automatiquement la référence intelligente du mémorandum
     * Format : Chrono / Direction / Sous-Direction / [Département] / [Service] / [Initiales]
     */
    private function generateSmartReference($memo)
    {
        $currentYear = now()->year;

        // 1. Calcul du Chrono (formatté sur 4 chiffres : 0001, 0002, etc.)
        // Basé sur le nombre de mémos enregistrés par l'utilisateur connecté (Secretaire) cette année
        $count = \App\Models\BlocEnregistrements::where('nature_memo', 'Memo Sortant')
            ->where('user_id', Auth::id())
            ->whereYear('created_at', $currentYear)
            ->count() + 1;

        $chrono = sprintf("%04d", $count);

        // 2. Identification de l'initiateur (Celui qui a rédigé le mémo à l'origine)
        $initiator = \App\Models\User::find($memo->user_id);
        
        if (!$initiator) {
            return $chrono . "/S-GEN/INITIATEUR-INCONNU";
        }

        // 3. Récupération des segments de référence depuis la table 'entities'
        // Direction et Sous-Direction sont toujours considérées comme présentes
        $dirRef = \App\Models\Entity::find($initiator->dir_id)?->ref ?? 'DIR';
        $sdRef  = \App\Models\Entity::find($initiator->sd_id)?->ref ?? 'SD';
        
        // Département et Service (Peuvent être null)
        $depRef = $initiator->dep_id ? \App\Models\Entity::find($initiator->dep_id)?->ref : null;
        $serRef = $initiator->serv_id ? \App\Models\Entity::find($initiator->serv_id)?->ref : null;

        // 4. Initiales de l'initiateur (Firstname Lastname)
        $initials = \Illuminate\Support\Str::upper(
            substr($initiator->first_name, 0, 1) . substr($initiator->last_name, 0, 1)
        );

        // 5. Normalisation du poste (Gestion du type Enum ou String)
        $poste = is_object($initiator->poste) ? $initiator->poste->value : (string)$initiator->poste;

        // 6. Construction dynamique des segments
        // On commence toujours par Chrono / Direction / Sous-Direction
        $segments = [$chrono, $dirRef, $sdRef];

        switch ($poste) {
            case 'Directeur':
            case 'Sous-Directeur':
                // Règle : Uniquement Direction et Sous-Direction.
                // On n'ajoute rien d'autre.
                break;

            case 'Chef-Departement':
                // Règle : On ajoute le département si renseigné.
                // Pas de service, pas d'initiales.
                if ($depRef) $segments[] = $depRef;
                break;

            case 'Chef-Service':
                // Règle : On ajoute Département et Service si renseignés.
                // Pas d'initiales.
                if ($depRef) $segments[] = $depRef;
                if ($serRef) $segments[] = $serRef;
                break;

            default:
                // Règle pour les autres (Agents, etc.) : Référence complète + Initiales
                if ($depRef) $segments[] = $depRef;
                if ($serRef) $segments[] = $serRef;
                $segments[] = $initials;
                break;
        }

        // 7. Assemblage final
        // array_filter permet de nettoyer les segments nulls pour éviter les doubles slashes (//)
        return implode('/', array_filter($segments));
    }

    private function abbreviate($string)
    {
        if (empty($string)) return '';
        
        // Prend les premières lettres de chaque mot majuscule
        // Ex simple: juste les 3 premières lettres en majuscule
        // Tu peux faire une logique plus complexe avec des Regex
        return Str::upper(substr($string, 0, 3)); 
    }

    // =========================================================
    // 6. GESTION DU REJET
    // =========================================================

    public function askReject($id, $mode)
    {
        $this->memo_id = $id;
        $this->reject_mode = $mode; // Stocke si on archive ou si on retourne
        $this->reject_comment = '';
        $this->isOpenReject = true;
    }

    public function closeRejectModal() { $this->isOpenReject = false; }

    public function processReject()
    {
        $this->validate(['reject_comment' => 'required|min:5']);

        $memo = Memo::findOrFail($this->memo_id);
        $user = Auth::user();

        $authorId = [$memo->user_id];

        if ($this->reject_mode === 'archive') {
            
            // CAS 1 : REJETER (ARCHIVAGE DÉFINITIF)
            $memo->update([
                'status' => 'rejeter',
                'workflow_direction' => 'terminer', // On termine le circuit
                'treatment_holders' => [],          // Plus personne ne peut le traiter
            ]);
            $actionLabel = "Rejet définitif (Archivé)";
            $notifType = "rejeter";
            $emailColor = '#ef4444'; // Rouge
            $emailTitle = "⛔ Mémo Rejeté";

        } else {

            // CAS 2 : RETOURNER (POUR CORRECTION)
            $memo->update([
                'status' => 'retourner',
                'workflow_direction' => 'sortant',   // Revient dans le flux de départ
                'treatment_holders' => $authorId,
            ]);
            $actionLabel = "Retourné pour correction";
            $notifType = "retourner";
            $emailColor = '#f59e0b'; // Orange
            $emailTitle = "↩️ Mémo Retourné";
        }

        // Historique
        Historiques::create([
            'user_id' => $user->id, 
            'memo_id' => $memo->id, 
            'visa' => $actionLabel, 
            'workflow_comment' => $this->reject_comment,
        ]);

        // Notification à l'auteur original
        $author = User::find($memo->user_id);
        if ($author) {
            try { 
                $author->notify(new MemoActionNotification($memo, $notifType, $user)); 
                $this->sendRejectEmail($memo, $author, $user, $emailTitle, $emailColor, $actionLabel);
            } catch (\Exception $e) {}
        }

        $this->closeRejectModal();
        $this->dispatch('notify', message: "Action effectuée : $actionLabel");
    }


        /**
     * Envoie l'email de Rejet ou de Retour via PHPMailer
     */
    private function sendRejectEmail($memo, $recipient, $actor, $title, $color, $actionLabel)
    {
        if (empty($recipient->email)) return;

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

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = "$title : " . $memo->object;
            
            $mail->Body = $this->buildRejectEmailBody($memo, $recipient, $actor, $title, $color, $actionLabel);
            
            // Texte brut (Fallback)
            $mail->AltBody = "Bonjour, votre mémo '{$memo->object}' a été : $actionLabel par {$actor->first_name} {$actor->last_name}.\nMotif : {$this->reject_comment}";

            $mail->send();

        } catch (Exception $e) {
            Log::error("Erreur envoi email rejet mémo #{$memo->id}: " . $mail->ErrorInfo);
        }
    }

    /**
     * Construit le HTML de l'email (Design Rouge ou Orange)
     */
    private function buildRejectEmailBody($memo, $recipient, $actor, $title, $color, $actionLabel)
    {
        $recipientName = $recipient->first_name . ' ' . $recipient->last_name;
        $actorName = $actor->first_name . ' ' . $actor->last_name;
        $actorPoste = $actor->poste->value ?? $actor->poste; // Gère Enum ou String
        
        // Si c'est retourné, on renvoie vers les brouillons ou envoyés pour modification
        // Si c'est rejeté, on renvoie vers l'historique/archives
        $targetTab = ($this->reject_mode === 'archive') ? 'archives' : 'drafted'; // ou 'document' selon votre logique
        
        $memoUrl = route('dashboard', [
            'view' => 'memos-content', 
            'tab' =>  'document'
        ]);

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f5; }
                .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .header { background-color: {$color}; padding: 30px; text-align: center; }
                .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: bold; text-transform: uppercase; }
                .content { padding: 30px; }
                .alert-box { background-color: #fff1f2; border-left: 4px solid {$color}; padding: 15px; margin: 20px 0; border-radius: 4px; }
                .info-label { font-size: 12px; font-weight: bold; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }
                .info-value { font-size: 15px; color: #111827; margin-bottom: 12px; font-weight: 500; }
                .comment-box { background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 15px; border-radius: 6px; font-style: italic; color: #4b5563; margin-top: 5px; }
                .btn { display: inline-block; padding: 12px 24px; background-color: {$color}; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
                .footer { background-color: #1f2937; color: #9ca3af; padding: 20px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>{$title}</h1>
                </div>
                
                <div class='content'>
                    <p>Bonjour <strong>{$recipientName}</strong>,</p>
                    
                    <p>Le statut de votre mémorandum a été mis à jour par <strong>{$actorName}</strong> ({$actorPoste}).</p>
                    
                    <div class='alert-box'>
                        <div class='info-label'>Objet du Mémo</div>
                        <div class='info-value'>{$memo->object}</div>
                        
                        <div class='info-label'>Action</div>
                        <div class='info-value' style='color: {$color};'>{$actionLabel}</div>
                    </div>

                    <div class='info-label'>Motif / Commentaire :</div>
                    <div class='comment-box'>
                        « {$this->reject_comment} »
                    </div>

                    <div style='text-align: center;'>
                        <a href='{$memoUrl}' class='btn'>Accéder au Document</a>
                    </div>
                </div>
                
                <div class='footer'>
                    <p><strong>Commercial Bank Cameroun</strong> - Système de Gestion des Mémos</p>
                    <p>Ceci est un message automatique, merci de ne pas répondre.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }


    
        /**
     * Envoie l'email de Rejet ou de Retour via PHPMailer
     */
    private function sendMemoEmail($memo, $recipient, $actor, $title, $color, $actionLabel)
    {
        if (empty($recipient->email)) return;

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

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = "$title : " . $memo->object;
            
            $mail->Body = $this->buildSendMemoEmailBody($memo, $recipient, $actor, $title, $color, $actionLabel);
            
            // Texte brut (Fallback)
            $mail->AltBody = "Bonjour, votre mémo '{$memo->object}' a été : $actionLabel par {$actor->first_name} {$actor->last_name}.\nMotif : {$this->reject_comment}";

            $mail->send();

        } catch (Exception $e) {
            Log::error("Erreur envoi email send mémo #{$memo->id}: " . $mail->ErrorInfo);
        }
    }

    /**
     * Construit le HTML de l'email (Design Rouge ou Orange)
     */
    private function buildSendMemoEmailBody($memo, $recipient, $actor, $title, $color, $actionLabel)
    {
        $recipientName = $recipient->first_name . ' ' . $recipient->last_name;
        $actorName = $actor->first_name . ' ' . $actor->last_name;
        $actorPoste = $actor->poste->value ?? $actor->poste; // Gère Enum ou String
        
        
        
        $memoUrl = route('dashboard', [
            'view' => 'memos-content', 
            'tab' =>  'document'
        ]);

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f5; }
                .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .header { background-color: {$color}; padding: 30px; text-align: center; }
                .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: bold; text-transform: uppercase; }
                .content { padding: 30px; }
                .alert-box { background-color: #fff1f2; border-left: 4px solid {$color}; padding: 15px; margin: 20px 0; border-radius: 4px; }
                .info-label { font-size: 12px; font-weight: bold; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }
                .info-value { font-size: 15px; color: #111827; margin-bottom: 12px; font-weight: 500; }
                .comment-box { background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 15px; border-radius: 6px; font-style: italic; color: #4b5563; margin-top: 5px; }
                .btn { display: inline-block; padding: 12px 24px; background-color: {$color}; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
                .footer { background-color: #1f2937; color: #9ca3af; padding: 20px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>{$title}</h1>
                </div>
                
                <div class='content'>
                    <p>Bonjour <strong>{$recipientName}</strong>,</p>
                    
                    <p>Le statut de votre mémorandum a été mis à jour par <strong>{$actorName}</strong> ({$actorPoste}).</p>
                    
                    <div class='alert-box'>
                        <div class='info-label'>Objet du Mémo</div>
                        <div class='info-value'>{$memo->object}</div>
                        
                        <div class='info-label'>Action</div>
                        <div class='info-value' style='color: {$color};'>{$actionLabel}</div>
                    </div>

                    <div class='info-label'>Motif / Commentaire :</div>
                    <div class='comment-box'>
                        « {$this->reject_comment} »
                    </div>

                    <div style='text-align: center;'>
                        <a href='{$memoUrl}' class='btn'>Accéder au Document</a>
                    </div>
                </div>
                
                <div class='footer'>
                    <p><strong>Commercial Bank Cameroun</strong> - Système de Gestion des Mémos</p>
                    <p>Ceci est un message automatique, merci de ne pas répondre.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    // =========================================================
    // 7. SYSTÈME DE REMPLACEMENT (Fixé pour Blade)
    // =========================================================

    private function resolveUserAvailability($user, $activeReplacements)
    {
        if (!$user) return null;

        $replacement = $activeReplacements->get($user->id);

        if ($replacement) {
            $replacingUser = User::find($replacement->user_id_replace);
            if ($replacingUser) {
                return [
                    'original'    => (object) $user->only(['id', 'first_name', 'last_name', 'poste', 'departement']),
                    'effective'   => (object) $replacingUser->only(['id', 'first_name', 'last_name', 'poste', 'departement']),
                    'is_replaced' => true
                ];
            }
        }

        return [
            'original'    => (object) $user->only(['id', 'first_name', 'last_name', 'poste', 'departement']),
            'effective'   => (object) $user->only(['id', 'first_name', 'last_name', 'poste', 'departement']),
            'is_replaced' => false
        ];
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
    // 8. PDF & FAVORIS
    // =========================================================

    public function downloadMemoPDF()
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($this->memo_id);
        $pdf = Pdf::loadView('pdf.memo-layout', $this->getPdfData($memo));
        
        return response()->streamDownload(fn() => print($pdf->output()), "Memo_{$memo->id}.pdf");
    }


    // =========================================================
    // 5. LOGIQUE D'ÉDITION
    // =========================================================

   public function editMemo($id)
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($id);
        
        $this->memo_id = $memo->id;
        $this->object = $memo->object;
        $this->concern = $memo->concern ?? '';
        $this->content = $memo->content;
        
        // Chargement des pièces jointes existantes
        $pj = $memo->pieces_jointes;
        if (is_string($pj)) { 
            $pj = json_decode($pj, true); 
        }
        $this->existingAttachments = is_array($pj) ? $pj : [];
        
        // Réinitialiser les nouveaux uploads
        $this->attachments = [];

        // Chargement des destinataires
        $this->recipients = $memo->destinataires->map(fn($dest) => [
            'entity_id'   => $dest->entity_id,
            'entity_name' => $dest->entity->name ?? 'Inconnu',
            'action'      => $dest->action
        ])->toArray();

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

        // 1. On garde les anciens fichiers
        $finalAttachments = $this->existingAttachments;

        // 2. On traite les nouveaux fichiers s'il y en a
        if ($this->attachments) {
            foreach ($this->attachments as $file) {
                $finalAttachments[] = $file->store('attachments/memos', 'public');
            }
        }

        // 3. Mise à jour de la DB
        $memo = Memo::updateOrCreate(
            ['id' => $this->memo_id],
            [
                'object'         => $this->object,
                'concern'        => $this->concern,
                'content'        => $this->content,
                'pieces_jointes' => json_encode($finalAttachments),
                'user_id'        => Auth::id(),
            ]
        );

        // 4. Maj Destinataires
        Destinataires::where('memo_id', $memo->id)->delete();
        $recipientData = array_map(fn($r) => [
            'memo_id'   => $memo->id,
            'entity_id' => $r['entity_id'],
            'action'    => $r['action'],
            'created_at' => now(),
            'updated_at' => now()
        ], $this->recipients);
        Destinataires::insert($recipientData);

        $this->isEditing = false;
        $this->attachments = []; // Vider le tampon
        $this->dispatch('notify', message: "Mémo mis à jour avec succès !");
    }

    // =========================================================
    // 7. HELPERS (Fixé pour supporter $data['original']->id)
    // =========================================================


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

    public function render()
    {
        $user = Auth::user();

        $memos = Memo::with(['user.entity', 'destinataires.entity'])
            // 1. Filtrer par la direction du créateur (initiateur) du mémo
            ->whereHas('user', function($query) use ($user) {
                $query->where('dir_id', $user->dir_id);
            })
            // 4. L'utilisateur connecté doit faire partie des détenteurs (circuit de validation)
            ->whereJsonContains('current_holders', $user->id)
                // 5. Recherche textuelle
                ->when($this->search, function($q) {
                $term = '%'.$this->search.'%';
                
                // CORRECTION ICI : Ajouter "use ($term)" pour transmettre la variable
                $q->where(function($sub) use ($term) {
                    $sub->where('object', 'like', $term)
                        ->orWhere('concern', 'like', $term);
                });
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(9);

        return view('livewire.memos.incoming-memos', [
            'memos' => $memos, 
            'entities' => $this->allEntities
        ]); 
    }
}