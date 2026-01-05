<?php

namespace App\Livewire\Memos;

use App\Models\Destinataires;
use App\Models\Entity;
use App\Models\Historiques;
use App\Models\Memo;
use App\Models\ReplacesUser;
use App\Models\User;
use App\Notifications\MemoActionNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Livewire\WithFileUploads;

class DocsMemos extends Component
{
    use WithPagination;
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
    public $isOpenHistory = false; 
    public $isOpenReject = false;  

    // --- Données du Mémo ---
    public $memo_id = null;
    public $memoHistory = [];

    #[Rule('required|string|max:255')]
    public string $object = '';

    #[Rule('required|string|max:255')]
    public string $concern = '';

    #[Rule('required|string')]
    public string $content = '';

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

    // --- Données pour l'Aperçu ---
  
    public $user_service;
    public $user_first_name;
    public $user_last_name;
    public $user_entity_name;

    public $pdfBase64 = '';
    public $date;


    // --- Workflow & Assignation ---
    public $memo_type = 'standard'; 
    public $managerData = null;     
    public $projectUsersList = [];  
    public $selected_project_users = [];
    public $workflow_comment = '';
    public $selected_visa = ''; 
    public $target_users_ids = []; 
    public $reject_comment = '';

    // --- Options Statiques ---

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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // =========================================================
    // 3. LOGIQUE D'AFFICHAGE ET APERÇU
    // =========================================================

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

    private function fillMemoDataView($memo)
    {
        $this->memo_id          = $memo->id;
        $this->object           = $memo->object;
        $this->concern          = $memo->concern;
        $this->content          = $memo->content;
        $this->date             = $memo->created_at->format('d/m/Y');
        $this->user_entity_name = $memo->user->entity->name ?? 'Entité';
        $this->user_service     = $memo->user->service;
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

    // =========================================================
    // 4. LOGIQUE D'ASSIGNATION ET WORKFLOW
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
        } elseif ($this->memo_type === 'projet') {
            $users = User::whereIn('id', $this->selected_project_users)->get();
            foreach ($users as $u) {
                $avail = $this->resolveUserAvailability($u, $activeReplacements);
                if ($avail) $nextHolders[] = $avail['effective']->id;
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

        // 7. NOTIFICATIONS
        foreach (User::whereIn('id', $nextHolders)->get() as $recipient) {
            try {
                $recipient->notify(new MemoActionNotification($memo, 'envoyer', $user));
            } catch (\Exception $e) {
                // Log error if mail fails
            }
        }

        // 8. SUPPRESSION DU BROUILLON
        $draft->delete();

        // 9. FINALISATION
        $this->closeModalTrois();
        $this->isEditing = false;
        $this->dispatch('notify', message: "Mémo transmis et brouillon supprimé avec succès.");
        
        
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
    // 6. GÉNÉRATION PDF
    // =========================================================

    public function downloadMemoPDF()
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($this->memo_id);
        $pdf = Pdf::loadView('pdf.memo-layout', $this->getPdfData($memo));
        
        return response()->streamDownload(fn() => print($pdf->output()), "Memo_{$memo->id}.pdf");
    }

    // =========================================================
    // 7. HELPERS (Fixé pour supporter $data['original']->id)
    // =========================================================

    private function resolveUserAvailability($user, $activeReplacements)
    {
        if (!$user) return null;

        $replacement = $activeReplacements->get($user->id);

        // STRUCTURE CRUCIALE : Un tableau contenant des objets stdClass
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

    public function closeModal() { 
        $this->isOpen = false; $this->reset(['memo_id', 'content', 'object']);
    }

    public function closeModalDeux() { 
        $this->isOpen2 = false; 
        $this->reset(['object', 'concern', 'content', 'recipients', 'newAttachments', 'existingAttachments']); 
    }

    public function closeModalTrois() { 
        $this->isOpen3 = false; $this->reset(['projectUsersList', 'managerData', 'workflow_comment']);
    }

    public function closeHistoryModal() { 
        $this->isOpenHistory = false; $this->memoHistory = []; 
    }

    // =========================================================
    // 8. RENDU
    // =========================================================

    public function render()
    {
        $memos = Memo::query()
            ->with(['destinataires.entity', 'historiques.user']) 
            ->where('user_id', Auth::id())
            ->whereIn('status', ['envoyer', 'retourner','rejeter', 'transmis', 'coter', 'repondu', 'terminer', 'traiter']) 
            ->when($this->search, function($query) {
                $term = '%'.$this->search.'%';
                $query->where(function($q) use ($term) {
                    $q->where('object', 'like', $term)->orWhere('concern', 'like', $term);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        return view('livewire.memos.docs-memos', ['memos' => $memos, 'entities' => $this->allEntities]);
    }
}