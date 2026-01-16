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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Notifications\MemoActionNotification;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class IncomingMemos extends Component
{
    use ManageFavorites, WithPagination;
    use WithFileUploads;

    // =================================================================================================
    // 1. PROPRIÉTÉS D'ÉTAT DE L'INTERFACE (UI) & NAVIGATION
    // =================================================================================================

    #[Url(keep: true)] 
    public $activeTab = 'incoming';

    public $search = '';
    public $darkMode = false;

    // --- Indicateurs de mode ---
    public $isViewingPdf = false;
    public $isEditing = false;
    public $isSecretary = false;

    // --- États d'ouverture des Modals ---
    public $isOpen = false;        // Modal générique
    public $isOpen2 = false;       
    public $isOpen3 = false;       // Modal Assignation/Workflow
    public $isOpen4 = false;       
    public $isOpenReject = false;  // Modal Rejet
    public $isOpenTrans = false;   // Modal Transmission (Secrétariat)
    public $isOpenHistory = false; // Modal Historique


    // =================================================================================================
    // 2. PROPRIÉTÉS DE DONNÉES DU MÉMO (FORMULAIRE & AFFICHAGE)
    // =================================================================================================

    public $memo_id = null;
    public $selectedMemo = null;

    #[Rule('required|string|max:255')]
    public string $object = '';

    #[Rule('required|string|max:255')]
    public string $concern = '';

    #[Rule('required|string')]
    public string $content = '';

    // --- Données d'affichage (Vue PDF/Aperçu) ---
    public $date;
    public $user_service;
    public $user_first_name;
    public $user_last_name;
    public $user_entity_name;
    public $pdfBase64 = '';
    public $memoHistory = [];

    
    // =================================================================================================
    // 3. PROPRIÉTÉS DE WORKFLOW & ASSIGNATION
    // =================================================================================================

    // --- Configuration du circuit ---
    public $memo_type = 'standard'; 
    public $isCircuitLocked = false; 
    public $workflow_comment = '';
    public $selected_visa = '';
    
    // --- Listes d'utilisateurs & Sélection ---
    public $managerData = null;     
    public $projectUsersList = [];  
    public $standardRecipientsList = []; 
    
    public $selected_project_users = [];
    public $selected_standard_users = []; 
    public $target_users_ids = [];
    public $suggestedNextUser = null; 

    // --- Options Statiques ---
    public $visaOptions = [
        'Vu' => 'Vu (Simple transmission)',
        'Vu & Accord' => 'Vu & D\'accord',
        'Vu & Pas d\'accord' => 'Vu & Pas d\'accord',
    ];

    
    // =================================================================================================
    // 4. PROPRIÉTÉS DE GESTION DES DESTINATAIRES & ACTIONS SPÉCIFIQUES
    // =================================================================================================

    // --- Gestion des Destinataires ---
    public $recipients = []; 
    public $newRecipientEntity = '';
    public $newRecipientAction = '';
    
    // --- Listes de référence ---
    public $allEntities = []; 
    public $actionsList = ['Faire le nécessaire', 'Prendre connaissance', 'Prendre position', 'Décider'];

    // --- Variables de Transmission (Secrétariat) ---
    public $transRecipients = [];
    public $generatedReference = '';

    // --- Variables de Rejet ---
    public $reject_comment = '';
    public $reject_mode = '';


    // =================================================================================================
    // 5. PROPRIÉTÉS DE GESTION DES PIÈCES JOINTES
    // =================================================================================================

    public $attachments = [];       
    public $newAttachments = [];    
    public $existingAttachments = []; 


    // =================================================================================================
    // 6. INITIALISATION (LIFECYCLE)
    // =================================================================================================

    public function mount()
    {
        $this->allEntities = Entity::orderBy('name')->get();
        $this->darkMode = Auth::user()->dark_mode ?? false;
    }

    #[On('dark-mode-toggled')]
    public function updateDarkMode($darkMode)
    {
        $this->darkMode = $darkMode;
        $user = Auth::user();
        if ($user) {
            $user->update(['dark_mode' => $darkMode]);
        }
    }


    // =================================================================================================
    // 7. GESTION DE L'INTERFACE & NAVIGATION
    // =================================================================================================

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // --- Fermeture des Modals ---

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

    public function closeHistoryModal() 
    { 
        $this->isOpenHistory = false; 
        $this->memoHistory = []; 
    }

    public function closePdfView()
    {
        $this->isViewingPdf = false;
        $this->pdfBase64 = '';
    }

    
    // =================================================================================================
    // 8. LOGIQUE D'AFFICHAGE & PDF
    // =================================================================================================

    public function viewMemo($id)
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($id);
        $this->memo_id = $memo->id;

        $pdf = Pdf::loadView('pdf.memo-layout', $this->getPdfData($memo))
              ->setPaper('a4', 'portrait');

        $this->pdfBase64 = base64_encode($pdf->output());
        $this->isViewingPdf = true;
        $this->isEditing = false;
    }

    public function downloadMemoPDF()
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($this->memo_id);
        $pdf = Pdf::loadView('pdf.memo-layout', $this->getPdfData($memo));
        
        return response()->streamDownload(fn() => print($pdf->output()), "Memo_{$memo->id}.pdf");
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

    private function getPdfData($memo)
    {
        $director = User::where('dir_id', $memo->user->dir_id)
                        ->where('poste', 'Directeur')
                        ->whereNull('manager_id') 
                        ->first();

        if (!$director) {
            $director = User::where('dir_id', $memo->user->dir_id)
                            ->where('poste', 'Directeur')
                            ->first();
        }

        return [
            'memo'               => $memo,
            'recipientsByAction' => $memo->destinataires->groupBy('action'),
            'date'               => $memo->created_at->format('d/m/Y'),
            'logo'               => $this->getLogoBase64(),
            'director'           => $director, 
        ];
    }

    private function getLogoBase64() {
        $path = public_path('images/logo.jpg');
        return file_exists($path) ? 'data:image/jpg;base64,' . base64_encode(file_get_contents($path)) : null;
    }


    // =================================================================================================
    // 9. LOGIQUE DE WORKFLOW : ASSIGNATION & ENVOI
    // =================================================================================================

     public function assignMemo($id)
    {
        $this->memo_id = $id;
        $this->reset(['workflow_comment', 'selected_project_users', 'selected_standard_users', 'managerData', 'suggestedNextUser']);
        
        $memo = Memo::findOrFail($id);
        $currentUser = Auth::user();
        $today = Carbon::now()->format('Y-m-d');

        $activeReplacements = ReplacesUser::where('date_begin_replace', '<=', $today)
            ->where('date_end_replace', '>=', $today)
            ->get()
            ->keyBy('user_id');

        $this->memo_type = $memo->circuit_type ?? 'standard'; 

        if ($this->memo_type === 'projet') {
                $this->isCircuitLocked = true;
                $path = is_array($memo->circuit_path) ? $memo->circuit_path : json_decode($memo->circuit_path, true);
                
            if (!empty($path)) 
            {
                $currentIndex = array_search($currentUser->id, $path);
                
                if ($currentIndex !== false && isset($path[$currentIndex + 1])) {
                    $nextUserId = $path[$currentIndex + 1];
                    $nextUser = User::find($nextUserId);
                    
                    if ($nextUser) {
                        $this->selected_project_users = [$nextUser->id];
                        $this->suggestedNextUser = $nextUser;
                        $this->projectUsersList = collect([
                            $this->resolveUserAvailability($nextUser, $activeReplacements)
                        ]);
                    }
                } else {
                    $this->addError('general', 'Fin du circuit défini ou utilisateur non trouvé dans le chemin.');
                }
            }

        } else {
            $this->memo_type = 'standard';
            $this->suggestedNextUser = null;
            
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
        $this->validate([
            'workflow_comment' => 'nullable|string|max:1000',
            'selected_project_users' => 'required_if:memo_type,projet|array',
            'selected_standard_users' => 'required_if:isSecretary,true|array', 
        ]);

        $memo = Memo::findOrFail($this->memo_id);
        $user = Auth::user();
        $today = Carbon::now()->format('Y-m-d');
        
        $activeReplacements = ReplacesUser::where('date_begin_replace', '<=', $today)
            ->where('date_end_replace', '>=', $today)
            ->get()
            ->keyBy('user_id');

        $nextHolders = [];
        if ($this->memo_type === 'standard') {

          if (!empty($this->selected_standard_users)) {
                $targets = User::whereIn('id', $this->selected_standard_users)->get();
                foreach ($targets as $target) {
                    $avail = $this->resolveUserAvailability($target, $activeReplacements);
                    if ($avail) $nextHolders[] = $avail['effective']->id;
                }
            }  
            else {
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

        $oldCurrentHolders = is_array($memo->current_holders) ? $memo->current_holders : [];
        $oldPreviousHolders = is_array($memo->previous_holders) ? $memo->previous_holders : [];

        $newCurrentHolders = array_unique(array_merge($oldCurrentHolders, $nextHolders));
        $newPreviousHolders = array_unique(array_merge($oldPreviousHolders, [$user->id]));

        $memo->update([
            'current_holders'   => $newCurrentHolders,   
            'previous_holders'  => $newPreviousHolders,  
            'treatment_holders' => array_unique($nextHolders), 
            'status'            => 'envoyer',
        ]);

        Historiques::create([
            'user_id'          => $user->id,
            'memo_id'          => $memo->id,
            'visa'             => 'valider', 
            'workflow_comment' => $this->workflow_comment ?? 'Transmis au niveau supérieur',
        ]);

        foreach (User::whereIn('id', $nextHolders)->get() as $recipient) {
            try {
                $recipient->notify(new MemoActionNotification($memo, 'envoyer', $user));
                $this->sendEmailNotification($memo, $nextHolders, $user);
            } catch (\Exception $e) {}
        }

        $this->closeModalTrois();
        $this->dispatch('notify', message: "Mémo envoyer avec succès.");
    }


    // =================================================================================================
    // 10. LOGIQUE DE SIGNATURE
    // =================================================================================================

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


    // =================================================================================================
    // 11. LOGIQUE DE TRANSMISSION (SECRÉTARIAT)
    // =================================================================================================

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

        $this->transRecipients = User::where('is_active', true) 
            ->where(function($q) {
                $q->where('poste', 'Secretaire')
                ->orWhere('poste', 'like', '%Secretaire%');
            })
            ->where(function($query) use ($targetEntityIds) {
                $query->whereIn('dir_id', $targetEntityIds)
                    ->orWhereIn('sd_id', $targetEntityIds);
            })
            ->get()
            ->map(fn($u) => $this->resolveUserAvailability($u, $activeReplacements));

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

        $nextHoldersIds = collect($this->transRecipients)
            ->pluck('effective.id')
            ->unique()
            ->toArray();


        DB::transaction(function () use ($memo, $user, $nextHoldersIds) {
            
            $qrToken = (string) \Illuminate\Support\Str::uuid();

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
                'current_holders'    => $updatedCurrentHolders, 
                'treatment_holders'  => $nextHoldersIds,       
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

        $recipients = \App\Models\User::whereIn('id', $nextHoldersIds)->get();

        foreach ($recipients as $recipient) {
            try {
                $recipient->notify(new \App\Notifications\MemoActionNotification($memo, 'transmis', $user));
                $this->sendEmailNotification($memo, $recipient, $user);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Échec notification secrétaire ID {$recipient->id} : " . $e->getMessage());
            }
        }

        $this->closeTransModal();
        $this->dispatch('notify', message: "Enregistré et transmis !");
    }

    private function generateSmartReference($memo)
    {
        $currentYear = now()->year;

        $count = \App\Models\BlocEnregistrements::where('nature_memo', 'Memo Sortant')
            ->where('user_id', Auth::id())
            ->whereYear('created_at', $currentYear)
            ->count() + 1;

        $chrono = sprintf("%04d", $count);

        $initiator = \App\Models\User::find($memo->user_id);
        
        if (!$initiator) {
            return $chrono . "/S-GEN/INITIATEUR-INCONNU";
        }

        $dirRef = \App\Models\Entity::find($initiator->dir_id)?->ref ?? 'DIR';
        $sdRef  = \App\Models\Entity::find($initiator->sd_id)?->ref ?? 'SD';
        $depRef = $initiator->dep_id ? \App\Models\Entity::find($initiator->dep_id)?->ref : null;
        $serRef = $initiator->serv_id ? \App\Models\Entity::find($initiator->serv_id)?->ref : null;

        $initials = \Illuminate\Support\Str::upper(
            substr($initiator->first_name, 0, 1) . substr($initiator->last_name, 0, 1)
        );

        $poste = is_object($initiator->poste) ? $initiator->poste->value : (string)$initiator->poste;

        $segments = [$chrono, $dirRef, $sdRef];

        switch ($poste) {
            case 'Directeur':
            case 'Sous-Directeur':
                break;
            case 'Chef-Departement':
                if ($depRef) $segments[] = $depRef;
                break;
            case 'Chef-Service':
                if ($depRef) $segments[] = $depRef;
                if ($serRef) $segments[] = $serRef;
                break;
            default:
                if ($depRef) $segments[] = $depRef;
                if ($serRef) $segments[] = $serRef;
                $segments[] = $initials;
                break;
        }

        return implode('/', array_filter($segments));
    }


    // =================================================================================================
    // 12. LOGIQUE DE REJET
    // =================================================================================================

    public function askReject($id, $mode)
    {
        $this->memo_id = $id;
        $this->reject_mode = $mode; 
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
            $memo->update([
                'status' => 'rejeter',
                'workflow_direction' => 'terminer', 
                'treatment_holders' => [],          
            ]);
            $actionLabel = "Rejet définitif (Archivé)";
            $notifType = "rejeter";
            $emailColor = '#ef4444'; 
            $emailTitle = "⛔ Mémo Rejeté";

        } else {
            $memo->update([
                'status' => 'retourner',
                'workflow_direction' => 'sortant',   
                'treatment_holders' => $authorId,
            ]);
            $actionLabel = "Retourné pour correction";
            $notifType = "retourner";
            $emailColor = '#f59e0b'; 
            $emailTitle = "↩️ Mémo Retourné";
        }

        Historiques::create([
            'user_id' => $user->id, 
            'memo_id' => $memo->id, 
            'visa' => $actionLabel, 
            'workflow_comment' => $this->reject_comment,
        ]);

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


    // =================================================================================================
    // 13. LOGIQUE D'ÉDITION & CRUD
    // =================================================================================================

   public function editMemo($id)
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($id);
        
        $this->memo_id = $memo->id;
        $this->object = $memo->object;
        $this->concern = $memo->concern ?? '';
        $this->content = $memo->content;
        
        $pj = $memo->pieces_jointes;
        if (is_string($pj)) { 
            $pj = json_decode($pj, true); 
        }
        $this->existingAttachments = is_array($pj) ? $pj : [];
        
        $this->attachments = [];

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

    public function save()
    {
        $this->validate();

        $finalAttachments = $this->existingAttachments;

        if ($this->attachments) {
            foreach ($this->attachments as $file) {
                $finalAttachments[] = $file->store('attachments/memos', 'public');
            }
        }

        $memo = Memo::updateOrCreate(
            ['id' => $this->memo_id],
            [
                'object'         => $this->object,
                'concern'        => $this->concern,
                'content'        => $this->content,
                'pieces_jointes' => json_encode($finalAttachments),
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

        $this->isEditing = false;
        $this->attachments = []; 
        $this->dispatch('notify', message: "Mémo mis à jour avec succès !");
    }

    // --- Helpers CRUD ---

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

    public function removeExistingAttachment($index)
    {
        if (isset($this->existingAttachments[$index])) {
            unset($this->existingAttachments[$index]);
            $this->existingAttachments = array_values($this->existingAttachments);
        }
    }

    public function removeAttachment($index)
    {
        if (isset($this->attachments[$index])) {
            unset($this->attachments[$index]);
            $this->attachments = array_values($this->attachments);
        }
    }


    // =================================================================================================
    // 14. UTILITAIRES & HELPERS
    // =================================================================================================

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

    private function abbreviate($string)
    {
        if (empty($string)) return '';
        return Str::upper(substr($string, 0, 3)); 
    }


    // =================================================================================================
    // 15. GESTION DES EMAILS
    // =================================================================================================

    private function sendEmailNotification($memo, $nextHolders, $sender)
    {
        $recipients = User::whereIn('id', $nextHolders)
            ->whereNotNull('email')
            ->get();

        if ($recipients->isEmpty()) {
            Log::warning("Aucun destinataire avec email valide pour le mémo #{$memo->id}");
            return;
        }

        $recipientsData = is_array($memo->destinataires) 
            ? $memo->destinataires 
            : json_decode($memo->destinataires, true) ?? [];
        
        $entitiesNames = collect($recipientsData)->map(function($dest) {
            $entity = Entity::find($dest['entity_id']);
            return $entity ? $entity->name : 'Inconnu';
        })->implode(', ');

        $memoType = $this->memo_type === 'projet' ? 'Circuit Particulier' : 'Circuit Standard';
        $actionRequired = $this->memo_type === 'projet' ? 'Validation requise dans le circuit' : 'Action requise';

        foreach ($recipients as $recipient) {
            try {
                $mail = new PHPMailer(true);

                $mail->isSMTP();
                $mail->Host = env('MAIL_HOST', 'smtp.gie.local');
                $mail->SMTPAuth = false;
                $mail->Port = env('MAIL_PORT', 25);
                $mail->SMTPSecure = false;
                $mail->SMTPAutoTLS = false;
                $mail->CharSet = 'UTF-8';
                $mail->SMTPOptions = [
                    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
                ];

                $mail->setFrom(
                    env('MAIL_FROM_ADDRESS', 'cbc_infos@groupecommercialbank.com'),
                    env('MAIL_FROM_NAME', 'CBC MEMOS')
                );

                $mail->addAddress($recipient->email, $recipient->first_name . ' ' . $recipient->last_name);

                $mail->isHTML(true);
                $mail->Subject = "Nouveau Mémorandum : {$memo->object}";
                $mail->Body = $this->buildEmailBody($memo, $recipient, $sender, $memoType, $actionRequired, $entitiesNames);
                $mail->AltBody = $this->buildEmailAltBody($memo, $recipient, $sender, $memoType);

                $mail->send();
                Log::info("Email envoyé avec succès à {$recipient->email} pour le mémo #{$memo->id}");

            } catch (Exception $e) {
                Log::error("Erreur lors de l'envoi d'email à {$recipient->email} pour le mémo #{$memo->id}: {$mail->ErrorInfo}");
            }
        }
    }

    private function sendRejectEmail($memo, $recipient, $actor, $title, $color, $actionLabel)
    {
        if (empty($recipient->email)) return;

        try {
            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST', 'smtp.gie.local');
            $mail->SMTPAuth = false;
            $mail->Port = env('MAIL_PORT', 25);
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
            $mail->CharSet = 'UTF-8';
            $mail->SMTPOptions = [
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
            ];

            $mail->setFrom(env('MAIL_FROM_ADDRESS', 'cbc_infos@groupecommercialbank.com'), env('MAIL_FROM_NAME', 'CBC MEMOS'));
            $mail->addAddress($recipient->email, $recipient->first_name . ' ' . $recipient->last_name);

            $mail->isHTML(true);
            $mail->Subject = "$title : " . $memo->object;
            $mail->Body = $this->buildRejectEmailBody($memo, $recipient, $actor, $title, $color, $actionLabel);
            $mail->AltBody = "Bonjour, votre mémo '{$memo->object}' a été : $actionLabel par {$actor->first_name} {$actor->last_name}.\nMotif : {$this->reject_comment}";

            $mail->send();

        } catch (Exception $e) {
            Log::error("Erreur envoi email rejet mémo #{$memo->id}: " . $mail->ErrorInfo);
        }
    }

    private function sendMemoEmailNotification($memo, $recipient, $actor, $title, $color, $actionLabel)
    {
        if (empty($recipient->email)) return;

        try {
            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST', 'smtp.gie.local');
            $mail->SMTPAuth = false;
            $mail->Port = env('MAIL_PORT', 25);
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
            $mail->CharSet = 'UTF-8';
            $mail->SMTPOptions = [
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
            ];

            $mail->setFrom(env('MAIL_FROM_ADDRESS', 'cbc_infos@groupecommercialbank.com'), env('MAIL_FROM_NAME', 'CBC MEMOS'));
            $mail->addAddress($recipient->email, $recipient->first_name . ' ' . $recipient->last_name);

            $mail->isHTML(true);
            $mail->Subject = "$title : " . $memo->object;
            $mail->Body = $this->buildSendMemoEmailBody($memo, $recipient, $actor, $title, $color, $actionLabel);
            $mail->AltBody = "Bonjour, votre mémo '{$memo->object}' a été : $actionLabel par {$actor->first_name} {$actor->last_name}.\nMotif : {$this->reject_comment}";

            $mail->send();

        } catch (Exception $e) {
            Log::error("Erreur envoi email send mémo #{$memo->id}: " . $mail->ErrorInfo);
        }
    }

    // --- HTML Builders ---

    private function buildValidationEmail($memo, $recipient, $sender, $validatorName)
    {
        $memoUrl = route('dashboard', ['view' => 'memos-content', 'tab' => 'archives']); // Ou 'document'
        $dateValidation = now()->format('d/m/Y à H:i');

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
                /* Garder le même header bleu/dégradé */
                .header { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); padding: 30px; text-align: center; }
                .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; }
                .header p { color: #dbeafe; margin: 5px 0 0 0; font-size: 14px; }
                .content { padding: 30px; background: #f8fafc; }
                
                .memo-box { background: white; border-left: 4px solid #10b981; /* VERT pour validation */ padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                
                .memo-title { font-size: 18px; font-weight: bold; color: #1e3a8a; margin-bottom: 15px; }
                .info-row { margin: 10px 0; padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
                .info-label { font-weight: 600; color: #6b7280; font-size: 13px; text-transform: uppercase; }
                .info-value { color: #111827; margin-top: 3px; }
                
                /* Boîte Action VERTE */
                .action-box { background: #ecfdf5; border: 2px solid #10b981; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center; }
                .action-box strong { color: #047857; font-size: 16px; }
                
                .btn { display: inline-block; padding: 12px 30px; background: #1e3a8a; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 20px 0; }
                .btn:hover { background: #1e40af; }
                .footer { background: #1f2937; color: #9ca3af; padding: 20px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📋 CBC MEMOS</h1>
                    <p>Système de Gestion des Mémorandums</p>
                </div>
                
                <div class='content'>
                    <p>Bonjour <strong>{$recipient->first_name}</strong>,</p>
                    <p>Bonne nouvelle ! Votre mémorandum a été validé par <strong>{$validatorName}</strong>.</p>
                    
                    <div class='memo-box'>
                        <div class='memo-title'>✅ {$memo->object}</div>
                        <div class='info-row'><div class='info-label'>Concerne</div><div class='info-value'>{$memo->concern}</div></div>
                        <div class='info-row'><div class='info-label'>Date de validation</div><div class='info-value'>{$dateValidation}</div></div>
                    </div>
                    
                    <div class='action-box'>
                        <strong>VALIDATION EFFECTUÉE</strong>
                    </div>
                    
                    <div style='text-align: center;'>
                        <a href='{$memoUrl}' class='btn'>Voir le document final</a>
                    </div>
                </div>
                
                <div class='footer'>
                    <p><strong>Commercial Bank Cameroun</strong></p>
                    <p>Notification automatique CBC MEMOS.</p>
                </div>
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

    private function buildRejectEmailBody($memo, $recipient, $actor, $title, $color, $actionLabel)
    {
        // Préparation des données
        $recipientName = $recipient->first_name . ' ' . $recipient->last_name;
        $actorName = $actor->first_name . ' ' . $actor->last_name;
        $actorPoste = is_object($actor->poste) ? $actor->poste->value : $actor->poste;
        
        // URL de redirection
        $memoUrl = route('dashboard', [
            'view' => 'memos-content', 
            'tab'  => 'drafted' // Généralement un rejet renvoie dans les brouillons/à corriger
        ]);

        // On s'assure que le commentaire n'est pas vide
        $comment = !empty($this->reject_comment) ? $this->reject_comment : "Aucun motif précisé.";

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                /* Base */
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background-color: #f3f4f6; }
                .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
                
                /* Header dynamique basé sur la couleur passée ($color) */
                /* Note: Comme on ne peut pas faire de dégradé CSS facile avec une seule variable couleur hex, 
                on utilise la couleur unie passée en paramètre pour le fond */
                .header { background-color: {$color}; padding: 30px; text-align: center; }
                .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 1px; }
                .header p { color: rgba(255,255,255,0.9); margin: 5px 0 0 0; font-size: 14px; }
                
                /* Content */
                .content { padding: 30px; background: #fff; }
                
                /* Memo Box */
                .memo-box { background: #f9fafb; border-left: 4px solid {$color}; padding: 20px; margin: 25px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; border-left-width: 4px; }
                .memo-title { font-size: 18px; font-weight: bold; color: #111827; margin-bottom: 15px; }
                
                /* Lignes d'info */
                .info-row { margin: 8px 0; display: flex; align-items: baseline; }
                .info-icon { margin-right: 10px; font-size: 16px; }
                .info-text { font-size: 14px; color: #4b5563; }
                .info-text strong { color: #111827; }
                
                /* Commentaire / Motif (Style Citation) */
                .reason-box { background-color: #fff1f2; border: 1px dashed {$color}; padding: 20px; border-radius: 8px; margin: 25px 0; position: relative; }
                .reason-label { font-size: 12px; font-weight: 700; color: {$color}; text-transform: uppercase; margin-bottom: 8px; display: block; }
                .reason-text { font-style: italic; color: #4b5563; font-size: 15px; line-height: 1.5; }
                
                /* Bouton */
                .btn { display: inline-block; padding: 12px 30px; background-color: {$color}; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 10px 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .btn:hover { opacity: 0.9; }
                
                /* Footer */
                .footer { background: #1f2937; color: #9ca3af; padding: 20px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <!-- Header avec Titre Dynamique (ex: REJET, RETOUR) -->
                <div class='header'>
                    <h1>{$title}</h1>
                    <p>Mise à jour du statut Workflow</p>
                </div>
                
                <div class='content'>
                    <p>Bonjour <strong>{$recipientName}</strong>,</p>
                    <p>Votre mémorandum a fait l'objet d'une décision par <strong>{$actorName}</strong> ({$actorPoste}).</p>
                    
                    <!-- Carte Détails -->
                    <div class='memo-box'>
                        <div class='memo-title'>📄 {$memo->object}</div>
                        
                        <div class='info-row'>
                            <span class='info-icon'>🛑</span>
                            <span class='info-text'>Action : <strong style='color: {$color};'>{$actionLabel}</strong></span>
                        </div>
                        <div class='info-row'>
                            <span class='info-icon'>📅</span>
                            <span class='info-text'>Date : <strong>" . now()->format('d/m/Y à H:i') . "</strong></span>
                        </div>
                    </div>
                    
                    <!-- Zone Motif / Commentaire -->
                    <div class='reason-box'>
                        <span class='reason-label'>Motif de la décision</span>
                        <div class='reason-text'>« {$comment} »</div>
                    </div>
                    
                    <div style='text-align: center;'>
                        <a href='{$memoUrl}' class='btn'>Accéder pour correction</a>
                    </div>
                    
                    <p style='margin-top: 30px; font-size: 13px; color: #6b7280; text-align: center;'>
                        Ce document sera archiver.
                    </p>
                </div>
                
                <div class='footer'>
                    <p><strong>Commercial Bank Cameroun</strong></p>
                    <p>Système de Gestion des Mémos - Notification Automatique</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    private function buildSendMemoEmailBody($memo, $recipient, $actor, $title, $color, $actionLabel)
    {
        // Réutilise la même structure que le rejet, adapté
        return $this->buildRejectEmailBody($memo, $recipient, $actor, $title, $color, $actionLabel);
    }


    // =================================================================================================
    // 16. AFFICHAGE (RENDER)
    // =================================================================================================

    public function render()
    {
        $user = Auth::user();

        $memos = Memo::with(['user.entity', 'destinataires.entity'])
            ->whereHas('user', function($query) use ($user) {
                $query->where('dir_id', $user->dir_id);
            })
            ->whereJsonContains('current_holders', $user->id)
                ->when($this->search, function($q) {
                $term = '%'.$this->search.'%';
                
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