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

        // 1. Gérer les fichiers : fusionner les anciens restants et les nouveaux
        $finalPaths = $this->existingAttachments;
        
        if ($this->attachments) {
            foreach ($this->attachments as $file) {
                $finalPaths[] = $file->store('attachments/memos', 'public');
            }
        }

        // 2. Mise à jour ou Création du Mémo
        $memo = Memo::updateOrCreate(
            ['id' => $this->memo_id],
            [
                'object'         => $this->object,
                'concern'        => $this->concern,
                'content'        => $this->content,
                'pieces_jointes' => json_encode($finalPaths),
                'user_id'        => Auth::id()
            ]
        );

        // 3. Mise à jour des destinataires
        Destinataires::where('memo_id', $memo->id)->delete();
        foreach ($this->recipients as $r) {
            Destinataires::create([
                'memo_id'    => $memo->id,
                'entity_id'  => $r['entity_id'],
                'action'     => $r['action'],
            ]);
        }

        $this->isEditing = false;
        $this->dispatch('notify', message: "Mémo mis à jour avec succès !");
    }

    // =========================================================
    // LOGIQUE D'ENVOI (WORKFLOW)
    // =========================================================

    public function assignMemo($id)
    {
        $this->memo_id = $id;
        $this->reset(['workflow_comment', 'selected_visa', 'selected_project_users']);
        $this->memo_type = 'standard';

        $currentUser = Auth::user();
        $today = Carbon::now()->format('Y-m-d');
        
        $activeReplacements = ReplacesUser::where('date_begin_replace', '<=', $today)
            ->where('date_end_replace', '>=', $today)
            ->get()
            ->keyBy('user_id');

        if ($currentUser->manager_id) {
            $manager = User::find($currentUser->manager_id);
            $this->managerData = $this->resolveUserAvailability($manager, $activeReplacements);
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
        $this->validate([
            'selected_visa' => 'required',
            'workflow_comment' => 'nullable|string|max:1000',
            'selected_project_users' => 'required_if:memo_type,projet|array',
        ]);

        $memo = Memo::findOrFail($this->memo_id);
        $user = Auth::user();
        $today = Carbon::now()->format('Y-m-d');
        
        $activeReplacements = ReplacesUser::where('date_begin_replace', '<=', $today)
            ->where('date_end_replace', '>=', $today)
            ->get()
            ->keyBy('user_id');

        $replacementContext = $this->getReplacementRights($memo);
        $finalComment = $this->workflow_comment;

        if ($replacementContext && in_array('viser', $replacementContext['actions_allowed'])) {
            $titulaire = $replacementContext['original_user'];
            $finalComment = "[P/O " . $titulaire->poste . "] " . $this->workflow_comment;
        }

        $nextHolders = [];

        if ($this->memo_type === 'standard' && $this->managerData) {
            $nextHolders[] = $this->managerData['effective']->id;
        }

        if ($this->memo_type === 'projet') {
            $users = User::whereIn('id', $this->selected_project_users)->get();
            foreach ($users as $u) {
                $avail = $this->resolveUserAvailability($u, $activeReplacements);
                if ($avail) $nextHolders[] = $avail['effective']->id;
            }
        }

        if (empty($nextHolders)) {
            $this->addError('general', 'Destinataire invalide.'); return;
        }

        $memo->update([
            'previous_holders' => [$user->id],
            'current_holders' => array_unique($nextHolders),
            'status' => 'envoyer',
            'workflow_direction' => 'sortant',
            'workflow_comment' => $finalComment
        ]);

        Historiques::create([
            'user_id' => $user->id,
            'memo_id' => $memo->id,
            'visa'    => $this->selected_visa,
            'workflow_comment' => $finalComment ?? 'R.A.S',
        ]);

        foreach (User::whereIn('id', $nextHolders)->get() as $recipient) {
            try { $recipient->notify(new MemoActionNotification($memo, 'envoyer', $user)); } catch (\Exception $e) {}
        }

        $this->closeModalTrois();
        $this->dispatch('notify', message: "Transmis avec succès.");
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

    public function viewMemo($id)
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($id);
        $this->memo_id = $memo->id;

        $pdf = Pdf::loadView('pdf.memo-layout', [
            'memo'               => $memo,
            'recipientsByAction' => $memo->destinataires->groupBy('action'),
            'date'               => $memo->created_at->format('d/m/Y'),
            'logo'               => $this->getLogoBase64(),
        ])->setPaper('a4', 'portrait');

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
        $memo = Memo::findOrFail($this->memo_id);
        $pdf = Pdf::loadView('pdf.memo-layout', [
            'memo' => $memo,
            'recipientsByAction' => $memo->destinataires->groupBy('action'),
            'date' => $memo->created_at->format('d/m/Y'),
            'logo' => $this->getLogoBase64(),
        ]);
        return response()->streamDownload(fn() => print($pdf->output()), "Memo_{$memo->id}.pdf");
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
        Memo::where('id', $this->memo_id)->where('user_id', Auth::id())->delete();
        $this->isOpen4 = false;
        $this->dispatch('notify', message: "Mémo supprimé.");
    }

    public function closeModalTrois() { $this->isOpen3 = false; }
    public function closeModalQuatre() { $this->isOpen4 = false; }

    public function render()
    {
        $memos = Memo::query()
            ->where('user_id', Auth::id())
            ->where('status', 'document')
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