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
use App\Models\Destinataires;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Notifications\MemoActionNotification;

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