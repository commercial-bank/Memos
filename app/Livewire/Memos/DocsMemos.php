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
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DocsMemos extends Component
{
    // =========================================================
    // 1. PROPRIÉTÉS DU COMPOSANT
    // =========================================================

    // --- Recherche & UI ---
    public $search = '';

    //afficher le formulaire d'edition
    public $isEditing = false;

    // --- États des Modals (Booleans) ---
    public $isOpen = false;        // Aperçu
    public $isOpen2 = false;       // Édition
    public $isOpen3 = false;       // Assignation / Envoi
    public $isOpenHistory = false; // Historique
    public $isOpenReject = false;  // Rejet

    // --- Données du Mémo (Formulaire & DB) ---
    public $memo_id = null;
    public $memoHistory = [];

    #[Rule('required|string|max:255')]
    public string $object = '';

    #[Rule('required|string|max:255')]
    public string $concern = '';

    #[Rule('required|string')]
    public string $content = '';

    // --- Gestion des Destinataires (Tableau Dynamique) ---
    public $recipients = []; // ['entity_id', 'entity_name', 'action']
    public $newRecipientEntity = '';
    public $newRecipientAction = '';
    public $allEntities = []; // Liste pour les selects
    public $actionsList = ['Faire le nécessaire', 'Prendre connaissance', 'Prendre position', 'Décider'];

    // --- Gestion des Pièces Jointes ---
    public $newAttachments = [];      // Fichiers temporaires (Livewire)
    public $existingAttachments = []; // JSON existant en base

    // --- Données pour l'Aperçu ---
    public $date;
    public $user_service;
    public $user_first_name;
    public $user_last_name;
    public $user_entity_name;

    // --- Workflow & Assignation ---
    public $memo_type = 'standard'; // 'standard' ou 'projet'
    public $managerData = null;     // ['original', 'effective', 'is_replaced']
    public $projectUsersList = [];  // Users éligibles mode projet
    public $selected_project_users = [];
    public $workflow_comment = '';
    public $selected_visa = ''; 
    public $target_users_ids = []; 
    public $reject_comment = '';

    // =========================================================
    // 2. INITIALISATION ET CYCLE DE VIE
    // =========================================================

    public function mount()
    {
        $this->allEntities = Entity::orderBy('name')->get();
    }

    // =========================================================
    // 3. LOGIQUE D'AFFICHAGE ET APERÇU
    // =========================================================

    public function viewMemo($id)
    {
        $memo = Memo::with('user')->findOrFail($id);
        $this->fillMemoDataView($memo);
        $this->isOpen = true;
    }

    /**
     * Remplit les données nécessaires à la vue de lecture seule
     */
    private function fillMemoDataView($memo)
    {
        $this->memo_id          = $memo->id;
        $this->object           = $memo->object;
        $this->concern          = $memo->concern;
        $this->content          = $memo->content;
        $this->date             = $memo->created_at->format('d/m/Y');
        $entity                 = Entity::find($memo->user->entity_id);
        $this->user_entity_name = $entity->name ?? 'Entité';
        $this->user_service     = $memo->user->service;
    }

    /**
     * Charge l'historique complet d'un mémo (incluant les réponses)
     */
    public function viewHistory($id)
    {
        $this->memo_id = $id;
        
        // Récupération des IDs liés (parent + enfants/réponses)
        $childrenIds = Memo::where('parent_id', $id)->pluck('id')->toArray();
        $allRelatedMemoIds = array_merge([$id], $childrenIds);

        $this->memoHistory = Historiques::with(['user', 'memo'])
            ->whereIn('memo_id', $allRelatedMemoIds)
            ->orderBy('created_at', 'desc') 
            ->get();

        $this->isOpenHistory = true;
    }

    // =========================================================
    // 4. LOGIQUE D'ASSIGNATION ET WORKFLOW (ENVOI)
    // =========================================================

    public function assignMemo($id)
    {
        $this->memo_id = $id;
        $this->reset(['workflow_comment', 'selected_visa', 'selected_project_users']);
        $this->memo_type = 'standard';

        $currentUser = Auth::user();

        // Gestion du N+1 (Manager)
        if ($currentUser->manager_id) {
            $manager = User::find($currentUser->manager_id);
            $this->managerData = $this->resolveUserAvailability($manager);
        } else {
            $this->managerData = null;
        }

        // Préparation de la liste projet (Excluant Soi-même et le Manager)
        $excludeIds = [$currentUser->id];
        if ($this->managerData) {
            $excludeIds[] = $this->managerData['original']->id;
        }

        $this->projectUsersList = User::whereNotIn('id', $excludeIds)
            ->orderBy('last_name')
            ->get()
            ->map(fn($user) => $this->resolveUserAvailability($user));

        $this->isOpen3 = true;
    }

    public function sendMemo()
    {
        $this->validate([
            'selected_visa'          => 'required',
            'workflow_comment'       => 'nullable|string|max:1000',
            'selected_project_users' => 'required_if:memo_type,projet|array',
        ], [
            'selected_project_users.required_if' => 'En mode projet, veuillez sélectionner au moins un collaborateur.'
        ]);

        $memo = Memo::find($this->memo_id);
        $currentUser = Auth::user();
        $nextHolders = [];

        // Scénario A : Standard (Vers N+1 ou remplaçant)
        if ($this->memo_type === 'standard') {
            if ($this->managerData) {
                $nextHolders[] = $this->managerData['effective']->id;
            } else {
                $this->addError('general', "Vous n'avez pas de supérieur hiérarchique défini.");
                return;
            }
        }

        // Scénario B : Projet (Vers liste collaborateurs)
        if ($this->memo_type === 'projet') {
            foreach ($this->selected_project_users as $userId) {
                $targetUser   = User::find($userId);
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

        // Sauvegarde de l'état du workflow
        $memo->previous_holders = [$currentUser->id];
        $memo->current_holders  = array_unique($nextHolders);
        $memo->status           = 'envoyer'; 
        $memo->workflow_comment = $this->workflow_comment; 
        $memo->save();

        // Enregistrement de l'historique
        Historiques::create([
            'user_id'          => $currentUser->id,
            'memo_id'          => $memo->id,
            'visa'             => $this->selected_visa,
            'workflow_comment' => $this->workflow_comment ?? 'R.A.S',
        ]);

        // Notifications aux nouveaux détenteurs
        $usersToNotify = User::whereIn('id', $nextHolders)->get();
        foreach ($usersToNotify as $user) {
            try {
                $user->notify(new MemoActionNotification($memo, 'envoyer', $currentUser));
            } catch (\Exception $e) { /* Log error if necessary */ }
        }

        $this->closeModalTrois();
        $this->dispatch('notify', message: "Le mémo ($this->memo_type) a été envoyé avec succès.");
    }

    // =========================================================
    // 5. LOGIQUE D'ÉDITION (MODAL 2)
    // =========================================================

    public function editMemo($id)
    {
        $memo = Memo::with(['user', 'destinataires.entity'])->findOrFail($id);
        
        $this->memo_id = $memo->id;
        $this->object  = $memo->object;
        $this->concern = $memo->concern ?? '';
        $this->content = $memo->content;
        
        // Chargement des pièces jointes
        $pj = $memo->pieces_jointes;
        if (is_string($pj)) { $pj = json_decode($pj, true); }
        $this->existingAttachments = is_array($pj) ? $pj : [];
        $this->newAttachments      = [];

        // Chargement des destinataires
        $this->recipients = $memo->destinataires->map(fn($dest) => [
            'entity_id'   => $dest->entity_id,
            'entity_name' => $dest->entity->name ?? 'Inconnu',
            'action'      => $dest->action
        ])->toArray();

        // Data pour l'aperçu
        $this->date = $memo->created_at->format('d/m/Y');   
        $entity = Entity::find($memo->user->entity_id);
        $this->user_entity_name = $entity->name ?? 'Entité';

        $this->resetValidation();
        $this->isEditing = true; // On active la vue édition
        $this->isOpen2 = false;  // Sécurité
    }

    public function cancelEdit()
    {
        $this->isEditing = false;
        $this->reset(['memo_id', 'object', 'concern', 'content', 'recipients', 'newAttachments', 'existingAttachments']);
    }   

    public function addRecipient()
    {
        $this->validate(['newRecipientEntity' => 'required', 'newRecipientAction' => 'required']);

        $entity = $this->allEntities->firstWhere('id', $this->newRecipientEntity);

        // Vérifier doublon
        foreach ($this->recipients as $r) {
            if ($r['entity_id'] == $this->newRecipientEntity) {
                $this->addError('newRecipientEntity', 'Ce destinataire est déjà ajouté.');
                return;
            }
        }

        $this->recipients[] = [
            'entity_id'   => $entity->id,
            'entity_name' => $entity->name,
            'action'      => $this->newRecipientAction
        ];

        $this->reset(['newRecipientEntity', 'newRecipientAction']);
    }

    public function removeRecipient($index)
    {
        unset($this->recipients[$index]);
        $this->recipients = array_values($this->recipients);
    }

    public function removeExistingAttachment($index)
    {
        unset($this->existingAttachments[$index]);
        $this->existingAttachments = array_values($this->existingAttachments);
    }

    public function removeNewAttachment($index)
    {
        array_splice($this->newAttachments, $index, 1);
    }

    public function save()
    {
        $this->validate();

        $finalAttachments = $this->existingAttachments;

        // Stockage des nouveaux fichiers
        foreach ($this->newAttachments as $file) {
            $path = $file->store('attachments/memos', 'public');
            $finalAttachments[] = $path;
        }

        // Mise à jour du mémo
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

        // Synchro des destinataires
        Destinataires::where('memo_id', $memo->id)->delete();
        foreach ($this->recipients as $recipient) {
            Destinataires::create([
                'memo_id'   => $memo->id,
                'entity_id' => $recipient['entity_id'],
                'action'    => $recipient['action']
            ]);
        }

        $this->closeModalDeux();
        $this->isEditing = false;
        $this->dispatch('notify', message: "Mémo modifié avec succès !");
    }

    // =========================================================
    // 6. GÉNÉRATION PDF ET EXPORT
    // =========================================================

    public function downloadMemoPDF()
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($this->memo_id);
        
        $recipientsByAction = $memo->destinataires->groupBy('action');

        // Assets en Base64 pour DomPDF
        $pathLogo   = public_path('images/logo.jpg');
        $logoBase64 = file_exists($pathLogo) 
            ? 'data:image/jpg;base64,' . base64_encode(file_get_contents($pathLogo)) 
            : null;

        $qrCodeBase64 = null;
        if ($memo->qr_code) {
            $qrImage      = QrCode::format('svg')->size(100)->generate(route('memo.verify', $memo->qr_code));
            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrImage);
        }

        $pdf = Pdf::loadView('pdf.memo-layout', [
            'memo'               => $memo,
            'recipientsByAction' => $recipientsByAction,
            'logo'               => $logoBase64,
            'qrCode'             => $qrCodeBase64,
            'date'               => $memo->created_at->format('d/m/Y'),
        ]);

        $pdf->setPaper('a4', 'portrait');

        return response()->streamDownload(fn() => print($pdf->output()), 'Memo_' . $memo->id . '.pdf');
    }

    // =========================================================
    // 7. HELPERS ET MÉTHODES PRIVÉES
    // =========================================================

    /**
     * Vérifie si un utilisateur est actuellement remplacé
     */
    private function resolveUserAvailability($user)
    {
        if (!$user) return null;

        $today = Carbon::now()->format('Y-m-d'); 
        $replacement = ReplacesUser::where('user_id', $user->id)
            ->where('date_begin_replace', '<=', $today)
            ->where('date_end_replace', '>=', $today)
            ->first();

        if ($replacement) {
            $replacingUser = User::find($replacement->user_id_replace);
            if ($replacingUser) {
                return [
                    'original'    => $user,
                    'effective'   => $replacingUser,
                    'is_replaced' => true
                ];
            }
        }

        return [
            'original'    => $user,
            'effective'   => $user,
            'is_replaced' => false
        ];
    }

    // --- Gestion de la fermeture des Modals ---
    public function closeModal() { $this->isOpen = false; }
    public function closeModalDeux() { 
        $this->isOpen2 = false; 
        $this->reset(['object', 'concern', 'content', 'recipients', 'newAttachments', 'existingAttachments']); 
    }
    public function closeModalTrois() { $this->isOpen3 = false; }
    public function closeHistoryModal() { $this->isOpenHistory = false; $this->memoHistory = []; }

    // =========================================================
    // 8. RENDU DE LA VUE
    // =========================================================

    public function render()
    {
        $memos = Memo::with(['destinataires.entity'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['envoyer', 'rejeter', 'transmis', 'coter', 'repondu', 'terminer', 'traiter']) 
            ->where(function($query) {
                $query->where('object', 'like', '%'.$this->search.'%')
                      ->orWhere('concern', 'like', '%'.$this->search.'%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        return view('livewire.memos.docs-memos', [
            'memos'    => $memos,
            'entities' => $this->allEntities
        ]);
    }
}