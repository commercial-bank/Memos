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

class DocsMemos extends Component
{
    use WithPagination;

    // =========================================================
    // 1. PROPRIÉTÉS DU COMPOSANT
    // =========================================================

    public $search = '';
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
    public $newAttachments = [];      
    public $existingAttachments = []; 

    // --- Données pour l'Aperçu ---
    public $date;
    public $user_service;
    public $user_first_name;
    public $user_last_name;
    public $user_entity_name;

    // --- Workflow & Assignation ---
    public $memo_type = 'standard'; 
    public $managerData = null;     
    public $projectUsersList = [];  
    public $selected_project_users = [];
    public $workflow_comment = '';
    public $selected_visa = ''; 
    public $target_users_ids = []; 
    public $reject_comment = '';

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

    public function viewMemo($id)
    {
        $memo = Memo::with(['user.entity'])->findOrFail($id);
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
        $this->reset(['workflow_comment', 'selected_visa', 'selected_project_users']);
        $this->memo_type = 'standard';

        $currentUser = Auth::user();
        $today = Carbon::now()->format('Y-m-d');

        // Optimisation : une seule requête pour tous les remplacements
        $activeReplacements = ReplacesUser::where('date_begin_replace', '<=', $today)
            ->where('date_end_replace', '>=', $today)
            ->get()
            ->keyBy('user_id');

        if ($currentUser->manager_id) {
            $manager = User::find($currentUser->manager_id);
            // On garde la structure attendue par le Blade : $managerData['effective']->...
            $this->managerData = $this->resolveUserAvailability($manager, $activeReplacements);
        } else {
            $this->managerData = null;
        }

        $excludeIds = array_filter([$currentUser->id, $currentUser->manager_id]);

        // IMPORTANT : projectUsersList doit rester une Collection pour le ->first() du Blade
        $this->projectUsersList = User::whereNotIn('id', $excludeIds)
            ->orderBy('last_name')
            ->get()
            ->map(fn($user) => $this->resolveUserAvailability($user, $activeReplacements));

        $this->isOpen3 = true;
    }

    public function sendMemo()
    {
        $this->validate([
            'selected_visa'          => 'required',
            'workflow_comment'       => 'nullable|string|max:1000',
            'selected_project_users' => 'required_if:memo_type,projet|array',
        ]);

        $memo = Memo::findOrFail($this->memo_id);
        $currentUser = Auth::user();
        $nextHolders = [];
        $today = Carbon::now()->format('Y-m-d');
        
        $activeReplacements = ReplacesUser::where('date_begin_replace', '<=', $today)
            ->where('date_end_replace', '>=', $today)
            ->get()
            ->keyBy('user_id');

        if ($this->memo_type === 'standard' && $this->managerData) {
            $nextHolders[] = $this->managerData['effective']->id;
        }

        if ($this->memo_type === 'projet') {
            $users = User::whereIn('id', $this->selected_project_users)->get();
            foreach ($users as $u) {
                $avail = $this->resolveUserAvailability($u, $activeReplacements);
                $nextHolders[] = $avail['effective']->id;
            }
        }

        if (empty($nextHolders)) {
            $this->addError('general', 'Aucun destinataire valide trouvé.');
            return;
        }

        $memo->update([
            'previous_holders' => [$currentUser->id],
            'current_holders'  => array_unique($nextHolders),
            'status'           => 'envoyer',
            'workflow_comment' => $this->workflow_comment
        ]);

        Historiques::create([
            'user_id'          => $currentUser->id,
            'memo_id'          => $memo->id,
            'visa'             => $this->selected_visa,
            'workflow_comment' => $this->workflow_comment ?? 'R.A.S',
        ]);

        foreach (User::whereIn('id', $nextHolders)->get() as $u) {
            try { $u->notify(new MemoActionNotification($memo, 'envoyer', $currentUser)); } catch (\Exception $e) {}
        }

        $this->closeModalTrois();
        $this->dispatch('notify', message: "Mémo envoyé avec succès.");
    }

    // =========================================================
    // 5. LOGIQUE D'ÉDITION
    // =========================================================

    public function editMemo($id)
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($id);
        
        $this->memo_id = $memo->id;
        $this->object  = $memo->object;
        $this->concern = $memo->concern ?? '';
        $this->content = $memo->content;
        
        $pj = $memo->pieces_jointes;
        if (is_string($pj)) { $pj = json_decode($pj, true); }
        $this->existingAttachments = is_array($pj) ? $pj : [];
        $this->newAttachments      = [];

        $this->recipients = $memo->destinataires->map(fn($dest) => [
            'entity_id'   => $dest->entity_id,
            'entity_name' => $dest->entity->name ?? 'Inconnu',
            'action'      => $dest->action
        ])->toArray();

        $this->date = $memo->created_at->format('d/m/Y');   
        $this->user_entity_name = $memo->user->entity->name ?? 'Entité';

        $this->resetValidation();
        $this->isEditing = true; 
    }

    public function cancelEdit()
    {
        $this->isEditing = false;
        $this->reset(['memo_id', 'object', 'concern', 'content', 'recipients', 'newAttachments', 'existingAttachments']);
    }   

    public function addRecipient()
    {
        $this->validate(['newRecipientEntity' => 'required', 'newRecipientAction' => 'required']);

        if (collect($this->recipients)->contains('entity_id', $this->newRecipientEntity)) {
            $this->addError('newRecipientEntity', 'Déjà ajouté.'); return;
        }

        $entity = $this->allEntities->firstWhere('id', $this->newRecipientEntity);
        $this->recipients[] = [
            'entity_id'   => $entity->id,
            'entity_name' => $entity->name,
            'action'      => $this->newRecipientAction
        ];
        $this->reset(['newRecipientEntity', 'newRecipientAction']);
    }

    public function removeRecipient($index) {
        unset($this->recipients[$index]); $this->recipients = array_values($this->recipients);
    }

    public function removeExistingAttachment($index) {
        unset($this->existingAttachments[$index]); $this->existingAttachments = array_values($this->existingAttachments);
    }

    public function removeNewAttachment($index) {
        array_splice($this->newAttachments, $index, 1);
    }

    public function save()
    {
        $this->validate();

        $finalAttachments = $this->existingAttachments;
        foreach ($this->newAttachments as $file) {
            $finalAttachments[] = $file->store('attachments/memos', 'public');
        }

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

        Destinataires::where('memo_id', $memo->id)->delete();
        $recipientData = array_map(fn($r) => [
            'memo_id'   => $memo->id,
            'entity_id' => $r['entity_id'],
            'action'    => $r['action'],
            'created_at' => now(),
            'updated_at' => now()
        ], $this->recipients);
        Destinataires::insert($recipientData);

        $this->closeModalDeux();
        $this->isEditing = false;
        $this->dispatch('notify', message: "Mémo modifié avec succès !");
    }

    // =========================================================
    // 6. GÉNÉRATION PDF
    // =========================================================

    public function downloadMemoPDF()
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($this->memo_id);
        $recipientsByAction = $memo->destinataires->groupBy('action');

        $pathLogo = public_path('images/logo.jpg');
        $logoBase64 = file_exists($pathLogo) ? 'data:image/jpg;base64,' . base64_encode(file_get_contents($pathLogo)) : null;

        $qrCodeBase64 = null;
        if ($memo->qr_code) {
            $qrImage = QrCode::format('png')->size(100)->margin(1)->generate(route('memo.verify', $memo->qr_code));
            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrImage);
        }

        $pdf = Pdf::loadView('pdf.memo-layout', [
            'memo' => $memo, 'recipientsByAction' => $recipientsByAction,
            'logo' => $logoBase64, 'qrCode' => $qrCodeBase64, 'date' => $memo->created_at->format('d/m/Y'),
        ])->setPaper('a4', 'portrait');

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
            ->with(['destinataires.entity'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['envoyer', 'rejeter', 'transmis', 'coter', 'repondu', 'terminer', 'traiter']) 
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