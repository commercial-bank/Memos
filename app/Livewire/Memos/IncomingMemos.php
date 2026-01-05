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


    // =========================================================
    // 2. INITIALISATION
    // =========================================================

    public function mount()
    {
        $this->allEntities = Entity::orderBy('name')->get();
    }

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
            if ($this->isSecretary) {
                $selectedUsers = User::whereIn('id', $this->selected_standard_users)->get();
                foreach ($selectedUsers as $u) {
                    $avail = $this->resolveUserAvailability($u, $activeReplacements);
                    $nextHolders[] = $avail['effective']->id;
                }
            } else {
                // Ici, on prend le manager de l'utilisateur connecté
                if ($user->manager_id) {
                    $manager = User::find($user->manager_id);
                    $avail = $this->resolveUserAvailability($manager, $activeReplacements);
                    $nextHolders[] = $avail['effective']->id;
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

        // 6. NOTIFICATIONS
        foreach (User::whereIn('id', $nextHolders)->get() as $recipient) {
            try {
                $recipient->notify(new MemoActionNotification($memo, 'envoyer', $user));
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
        $memo = Memo::with('destinataires')->findOrFail($id);
        $today = Carbon::now()->format('Y-m-d');
    
        $targetEntityIds = $memo->destinataires->pluck('entity_id')->toArray();
        $activeReplacements = ReplacesUser::where('date_begin_replace', '<=', $today)
            ->where('date_end_replace', '>=', $today)
            ->get()
            ->keyBy('user_id');

        $this->transRecipients = User::whereIn('entity_id', $targetEntityIds)
            ->where('poste', 'Secretaire')
            ->get()
            ->map(fn($u) => $this->resolveUserAvailability($u, $activeReplacements));

        if ($this->transRecipients->isEmpty()) {
            $this->dispatch('notify', message: "Aucun secrétariat cible trouvé."); return; 
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
        $nextHoldersIds = collect($this->transRecipients)->pluck('effective.id')->unique()->toArray();

        DB::transaction(function () use ($memo, $user, $nextHoldersIds) {
            BlocEnregistrements::create([
                'nature_memo' => 'Memo Sortant',
                'date_enreg' => now()->format('d/m/Y'),
                'reference' => $this->generatedReference,
                'memo_id' => $memo->id,
                'user_id' => $user->id,
            ]);

            $memo->update([
                'workflow_direction' => 'entrant', 
                'current_holders' => $nextHoldersIds,
                'status' => 'transmis',
                'reference' => $this->generatedReference,
            ]);

            Historiques::create([
                'user_id' => $user->id, 'memo_id' => $memo->id, 'visa' => 'Enregistré', 'workflow_comment' => "Réf: " . $this->generatedReference
            ]);
        });

        $this->closeTransModal();
        $this->dispatch('notify', message: "Enregistré et transmis !");
    }

    // 3. LOGIQUE INTELLIGENTE DE GÉNÉRATION DE RÉFÉRENCE
    private function generateSmartReference($memo)
    {
        

        $currentYear = now()->year;
    
        $count = BlocEnregistrements::where('nature_memo', 'Memo Sortant')
            ->where('user_id', Auth::id())
            ->whereYear('created_at', $currentYear)
            ->count() + 1;
        
        // 2. Données de base du créateur du mémo
        $creator = User::with(['entity', 'sousDirection'])->find($memo->user_id);
        
        // Préparation des segments
        $refEntity = $creator->entity->ref ?? 'ENT';
        $refSD = $creator->sousDirection->ref ?? 'SD'; // Assure-toi d'avoir la relation dans User
        $refDept = $creator->departement;
        $refService = $creator->service;
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
            return sprintf("%04d/%s/%s/%s/%s/%s", $count, $refEntity, $refSD, $refDept, $refService, $userInitials);
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

    public function askReject($id, $mode = 'archive')
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

        if ($this->reject_mode === 'archive') {
            // CAS 1 : REJETER (ARCHIVAGE DÉFINITIF)
            $memo->update([
                'status' => 'rejeter',
                'workflow_direction' => 'terminer', // On termine le circuit
                'treatment_holders' => [],          // Plus personne ne peut le traiter
            ]);
            $actionLabel = "Rejet définitif (Archivé)";
            $notifType = "rejeter";
        } else {
            // CAS 2 : RETOURNER (POUR CORRECTION)
            $memo->update([
                'status' => 'retourner',
                'workflow_direction' => 'sortant',   // Revient dans le flux de départ
                'current_holders' => [], // Retour à l'auteur initial
                'treatment_holders' => [],
            ]);
            $actionLabel = "Retourné pour correction";
            $notifType = "retourner";
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
            } catch (\Exception $e) {}
        }

        $this->closeRejectModal();
        $this->dispatch('notify', message: "Action effectuée : $actionLabel");
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
        $memos = Memo::with(['user.entity', 'destinataires.entity'])
            ->where('workflow_direction', 'sortant')
            ->whereNotIn('status', ['transmis', 'rejeter']) 
            ->whereJsonContains('current_holders', Auth::id())
            ->when($this->search, function($q) {
                $term = '%'.$this->search.'%';
                $q->where(fn($sub) => $sub->where('object', 'like', $term)->orWhere('concern', 'like', $term));
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(9);

        return view('livewire.memos.incoming-memos', ['memos' => $memos, 'entities' => $this->allEntities]); 
    }
}