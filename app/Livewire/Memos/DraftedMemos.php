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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DraftedMemos extends Component
{
    use WithPagination, WithFileUploads;

    // =========================================================
    // 1. PROPRIÉTÉS DU COMPOSANT
    // =========================================================

    //affichage du formulaire d'edition
    public $isEditing = false; 

    // --- Recherche & UI ---
    public $search = '';

    // --- États des Modals ---
    public $isOpen = false;      // Aperçu
    public $isOpen2 = false;     // Édition (Formulaire complet)
    public $isOpen3 = false;     // Assignation / Envoi
    public $isOpen4 = false;     // Suppression

    // --- Données du Mémo (Formulaire) ---
    public $memo_id = null;

    #[Rule('required|string|max:255')]
    public string $object = '';

    #[Rule('string|max:255')]
    public string $concern = '';

    #[Rule('required|string')]
    public string $content = '';

    // --- Gestion des Destinataires (Tableau Dynamique) ---
    public $recipients = []; // ['entity_id', 'entity_name', 'action']
    public $newRecipientEntity = '';
    public $newRecipientAction = '';
    public $allEntities = []; 

    // --- Gestion des Pièces Jointes ---
    public $newAttachments = [];      // Uploads temporaires
    public $existingAttachments = []; // JSON existant en base

    // --- Workflow & Assignation ---
    public $memo_type = 'standard'; // 'standard' ou 'projet'
    public $workflow_comment = '';
    public $selected_visa = ''; 
    public $managerData = null;     // ['original', 'effective', 'is_replaced']
    public $projectUsersList = [];  // Users éligibles mode projet
    public $selected_project_users = [];
    public $target_users_ids = [];

    // --- Données pour l'Aperçu ---
    public $date;
    public $user_service;
    public $user_first_name;
    public $user_last_name;
    public $user_entity_name;
    public $ref_number;
    public $selections = [];

    // --- Options Statiques ---
    public $actionsList = ['Faire le nécessaire', 'Prendre connaissance', 'Prendre position', 'Décider'];
    public $visaOptions = [
        'Vu' => 'Vu (Simple transmission)',
        'Vu & Accord' => 'Vu & D\'accord',
        'Vu & Pas d\'accord' => 'Vu & Pas d\'accord',
    ];

    // =========================================================
    // 2. CYCLE DE VIE
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
    // 3. LOGIQUE D'ÉDITION ET SAUVEGARDE (MODAL 2)
    // =========================================================

    /**
     * Charge les données d'un mémo pour édition
     */
    public function editMemo($id)
    {
        $memo = Memo::with(['user', 'destinataires.entity'])->findOrFail($id);
        
        $this->memo_id = $memo->id;
        $this->object = $memo->object;
        $this->concern = $memo->concern ?? '';
        $this->content = $memo->content;
        
        // Chargement des pièces jointes
        $pj = $memo->pieces_jointes;
        if (is_string($pj)) { 
            $pj = json_decode($pj, true); 
        }
        $this->existingAttachments = is_array($pj) ? $pj : [];
        $this->newAttachments = [];

        // Chargement des destinataires
        $this->recipients = $memo->destinataires->map(fn($dest) => [
            'entity_id'   => $dest->entity_id,
            'entity_name' => $dest->entity->name ?? 'Inconnu',
            'action'      => $dest->action
        ])->toArray();

        // Data aperçu
        $this->date = $memo->created_at->format('d/m/Y');   
        $entity = Entity::find($memo->user->entity_id);
        $this->user_entity_name = $entity->name ?? 'Entité';

        $this->resetValidation();
        $this->isEditing = true;
        $this->isOpen2 = false;
       
    }

    public function cancelEdit()
    {
        $this->isEditing = false;
        $this->reset(['memo_id', 'object', 'concern', 'content', 'recipients', 'newAttachments', 'existingAttachments']);
    }

    /**
     * Sauvegarde les modifications du mémo
     */
    public function save()
    {
        $this->validate();

        $finalAttachments = $this->existingAttachments;

        // Stockage des nouveaux fichiers
        foreach ($this->newAttachments as $file) {
            $path = $file->store('attachments/memos', 'public');
            $finalAttachments[] = $path;
        }

        // Mise à jour DB
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

        // Sync des destinataires
        Destinataires::where('memo_id', $memo->id)->delete();

        foreach ($this->recipients as $recipient) {
            Destinataires::create([
                'memo_id'   => $memo->id,
                'entity_id' => $recipient['entity_id'],
                'action'    => $recipient['action']
            ]);
        }

        $this->isEditing = false;
        $this->closeModalDeux();
        $this->dispatch('notify', message: "Mémo modifié avec succès !");
    }

    // =========================================================
    // 4. LOGIQUE D'ASSIGNATION ET ENVOI (MODAL 3)
    // =========================================================

    /**
     * Prépare le modal d'envoi (calcul du N+1 et liste projet)
     */
    public function assignMemo($id)
    {
        $this->memo_id = $id;
        $this->reset(['workflow_comment', 'selected_visa', 'selected_project_users']);
        $this->memo_type = 'standard';

        $currentUser = Auth::user();

        // Résolution du Manager (N+1)
        if ($currentUser->manager_id) {
            $manager = User::find($currentUser->manager_id);
            $this->managerData = $this->resolveUserAvailability($manager);
        } else {
            $this->managerData = null;
        }

        // Liste des utilisateurs éligibles mode projet
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

    /**
     * Exécute l'envoi du mémo
     */
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

        // Scénario A : Standard (N+1)
        if ($this->memo_type === 'standard') {
            if ($this->managerData) {
                $nextHolders[] = $this->managerData['effective']->id;
            } else {
                $this->addError('general', "Vous n'avez pas de supérieur hiérarchique défini.");
                return;
            }
        }

        // Scénario B : Projet (Multi-destinataires)
        if ($this->memo_type === 'projet') {
            foreach ($this->selected_project_users as $userId) {
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

        // Sauvegarde DB & Historique
        $memo->previous_holders = [$currentUser->id];
        $memo->current_holders  = array_unique($nextHolders);
        $memo->status           = 'envoyer'; 
        $memo->workflow_comment = $this->workflow_comment; 
        $memo->save();

        Historiques::create([
            'user_id'          => $currentUser->id,
            'memo_id'          => $memo->id,
            'visa'             => $this->selected_visa,
            'workflow_comment' => $this->workflow_comment ?? 'R.A.S',
        ]);

        // Notifications
        $usersToNotify = User::whereIn('id', $nextHolders)->get();
        foreach ($usersToNotify as $user) {
            $user->notify(new MemoActionNotification($memo, 'envoyer', $currentUser));
        }
        
        $this->closeModalTrois();
        $this->dispatch('notify', message: "Le mémo ($this->memo_type) a été envoyé avec succès.");
    }

    // =========================================================
    // 5. GESTION DES DOCUMENTS (PDF, QR, APERÇU)
    // =========================================================

    public function viewMemo($id) 
    {
        $memo = Memo::with('user')->findOrFail($id);
        $this->fillMemoDataView($memo);
        $this->isOpen = true;
    }

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
        $this->ref_number       = $memo->numero_ref;
    }

    public function downloadMemoPDF()
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($this->memo_id);
        $recipientsByAction = $memo->destinataires->groupBy('action');

        // Assets en Base64 pour DomPDF
        $pathLogo = public_path('images/logo.jpg');
        $logoBase64 = file_exists($pathLogo) 
            ? 'data:image/jpg;base64,' . base64_encode(file_get_contents($pathLogo)) 
            : null;

        $qrCodeBase64 = null;
        if ($memo->qr_code) {
            $qrImage = QrCode::format('svg')->size(100)->generate(route('memo.verify', $memo->qr_code));
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
    // 6. HELPERS ET UTILITAIRES
    // =========================================================

    /**
     * Résout la disponibilité d'un utilisateur (Remplacement actif)
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

    public function addRecipient()
    {
        $this->validate(['newRecipientEntity' => 'required', 'newRecipientAction' => 'required']);

        $entity = $this->allEntities->firstWhere('id', $this->newRecipientEntity);

        if (collect($this->recipients)->contains('entity_id', $this->newRecipientEntity)) {
            $this->addError('newRecipientEntity', 'Ce destinataire est déjà ajouté.');
            return;
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

    public function deleteMemo($id) { $this->memo_id = $id; $this->isOpen4 = true; }
    
    public function del() {
        $memo = Memo::find($this->memo_id);
        if ($memo && $memo->user_id === Auth::id()) {
            $memo->delete();
        }
        $this->closeModalQuatre();
        $this->dispatch('notify', message: "Supprimé avec succès !");
    }

    // Gestion de la fermeture des Modals
    public function closeModal() { $this->isOpen = false; }
    public function closeModalDeux() { 
        $this->isOpen2 = false; 
        $this->reset(['object', 'concern', 'content', 'recipients', 'newAttachments', 'existingAttachments']); 
    }
    public function closeModalTrois() { $this->isOpen3 = false; }
    public function closeModalQuatre() { $this->isOpen4 = false; }

    // =========================================================
    // 7. RENDU DE LA VUE
    // =========================================================

    public function render()
    {
        $memos = Memo::with(['destinataires.entity'])
            ->where('user_id', Auth::id())
            ->where('status', 'document')
            ->where(function($query) {
                $query->where('object', 'like', '%'.$this->search.'%')
                      ->orWhere('concern', 'like', '%'.$this->search.'%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        return view('livewire.memos.drafted-memos', [
            'memos'    => $memos,
            'entities' => $this->allEntities
        ]);
    }
}