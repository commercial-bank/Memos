<?php

namespace App\Livewire\Memos;

use Carbon\Carbon;
use App\Models\Memo;
use App\Models\User;
use App\Models\Entity;
use Livewire\Component;
use App\Models\Historiques;
use Illuminate\Support\Str;
use App\Models\ReplacesUser;
use Livewire\WithPagination;
use App\Models\Destinataires;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Notifications\MemoActionNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DraftedMemos extends Component
{
    use WithPagination;
    use WithFileUploads;

    // --- RECHERCHE & DATATABLE ---
    public $search = '';

     // --- VARIABLES MODAL ASSIGNATION ---
    public $memo_type = 'standard'; // 'standard' ou 'projet'

    // Structure des données pour l'affichage : ['original' => User, 'effective' => User, 'is_replaced' => bool]
    public $managerData = null; 

    // Liste des utilisateurs éligibles pour le mode projet (Excluant Auth et N+1)
    public $projectUsersList = []; 

    // IDs sélectionnés en mode projet
    public $selected_project_users = [];

    // --- MODALS STATES ---
    public $isOpen = false;      // Aperçu
    public $isOpen2 = false;     // Édition (Le gros formulaire)
    public $isOpen3 = false;     // Assignation simple (si conservé)
    public $isOpen4 = false;     // Suppression

    // --- CHAMPS DU MÉMO (SCHEMA DB) ---
    public $memo_id = null;

    // --- VARIABLES POUR L'ENVOI (WORKFLOW) ---
    public $workflow_comment = '';
    public $selected_visa = ''; 
    public $target_users_ids = []; // Pour ajouter d'autres destinataires en plus du N+1
    
    // Pour l'affichage dans le modal
    public $nPlusOneUser = null;
    public $effectiveReceiver = null; // Celui qui reçoit vraiment (N+1 ou Remplaçant)
    public $isReplaced = false;
    public $usersList = []; // Liste de tous les users pour choix multiple

   

    // Options de visa
    public $visaOptions = [
        'Vu' => 'Vu (Simple transmission)',
        'Vu & Accord' => 'Vu & D\'accord',
        'Vu & Pas d\'accord' => 'Vu & Pas d\'accord',
    ];
    
    #[Rule('required|string|max:255')]
    public string $object = '';

    #[Rule('string|max:255')]
    public string $concern = '';

    #[Rule('required|string')]
    public string $content = '';

    // --- GESTION DES DESTINATAIRES (TABLEAU DYNAMIQUE) ---
    public $recipients = []; // Contient: ['entity_id', 'entity_name', 'action']
    public $newRecipientEntity = '';
    public $newRecipientAction = '';
    public $allEntities = []; // Liste pour le select
    public $actionsList = ['Faire le nécessaire', 'Prendre connaissance', 'Prendre position', 'Décider'];

    // --- GESTION DES PIÈCES JOINTES ---
    public $newAttachments = []; // Fichiers temporaires uploadés (Livewire)
    public $existingAttachments = []; // Chemins des fichiers déjà en base (JSON)

    // --- DATA VIEW (Aperçu) ---
    public $date;
    public $user_service;
    public $user_first_name;
    public $user_last_name;
    public $user_entity_name;
    public $ref_number;
    public $selections = []; // Pour modal assignation simple

    public function mount()
    {
        // On charge les entités une seule fois pour les selects
        $this->allEntities = Entity::orderBy('name')->get(); 
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // =================================================================
    // LOGIQUE D'ÉDITION (MODAL 2)
    // =================================================================

    public function editMemo($id)
    {
        $memo = Memo::with(['user', 'destinataires.entity'])->findOrFail($id);
        
        $this->memo_id = $memo->id;
        $this->object = $memo->object;
        $this->concern = $memo->concern ?? '';
        $this->content = $memo->content;
        
        // 1. Charger les pièces jointes existantes (JSON)
        $pj = $memo->pieces_jointes;
        if (is_string($pj)) { $pj = json_decode($pj, true); }
        $this->existingAttachments = is_array($pj) ? $pj : [];
        $this->newAttachments = []; // Reset uploads

        // 2. Charger les destinataires existants dans le tableau local
        $this->recipients = $memo->destinataires->map(function($dest) {
            return [
                'entity_id' => $dest->entity_id,
                'entity_name' => $dest->entity->name ?? 'Inconnu',
                'action' => $dest->action
            ];
        })->toArray();

        // Data pour l'aperçu si besoin
        $this->date = $memo->created_at->format('d/m/Y');   
        $entity = Entity::find($memo->user->entity_id);
        $this->user_entity_name = $entity->name ?? 'Entité';

        $this->resetValidation();
        $this->isOpen2 = true;
    }

    // Ajouter un destinataire dans la liste temporaire
    public function addRecipient()
    {
        $this->validate([
            'newRecipientEntity' => 'required',
            'newRecipientAction' => 'required'
        ]);

        $entity = $this->allEntities->firstWhere('id', $this->newRecipientEntity);

        // Vérifier doublon
        foreach ($this->recipients as $r) {
            if ($r['entity_id'] == $this->newRecipientEntity) {
                $this->addError('newRecipientEntity', 'Ce destinataire est déjà ajouté.');
                return;
            }
        }

        $this->recipients[] = [
            'entity_id' => $entity->id,
            'entity_name' => $entity->name, // ou $entity->title selon votre modèle
            'action' => $this->newRecipientAction
        ];

        // Reset inputs
        $this->newRecipientEntity = '';
        $this->newRecipientAction = '';
    }

    // Retirer un destinataire de la liste temporaire
    public function removeRecipient($index)
    {
        unset($this->recipients[$index]);
        $this->recipients = array_values($this->recipients); // Réindexer
    }

    // Retirer une PJ existante
    public function removeExistingAttachment($index)
    {
        // Optionnel : Supprimer le fichier physiquement si vous voulez
        // Storage::delete($this->existingAttachments[$index]); 
        
        unset($this->existingAttachments[$index]);
        $this->existingAttachments = array_values($this->existingAttachments);
    }

    // Retirer une nouvelle PJ (upload en cours)
    public function removeNewAttachment($index)
    {
        array_splice($this->newAttachments, $index, 1);
    }

    public function save()
    {
        $this->validate();

        // 1. Gestion des fichiers
        $finalAttachments = $this->existingAttachments;

        foreach ($this->newAttachments as $file) {
            // Stocker dans 'attachments/memos' par exemple
            $path = $file->store('attachments/memos', 'public');
            $finalAttachments[] = $path; // On stocke juste le chemin ou un objet
        }

        // 2. Mise à jour ou Création du Mémo
        $memo = Memo::updateOrCreate(
            ['id' => $this->memo_id],
            [
                'object' => $this->object,
                'concern' => $this->concern,
                'content' => $this->content,
                'pieces_jointes' => json_encode($finalAttachments), // Cast array to JSON
                'user_id' => Auth::id(),
                // 'status' => 'brouillon' // reste brouillon
            ]
        );

        // 3. Synchronisation des destinataires (Suppression des anciens -> Création des nouveaux)
        Destinataires::where('memo_id', $memo->id)->delete();

        foreach ($this->recipients as $recipient) {
            Destinataires::create([
                'memo_id' => $memo->id,
                'entity_id' => $recipient['entity_id'],
                'action' => $recipient['action']
            ]);
        }

        $this->closeModalDeux();
        $this->dispatch('notify', message: "Mémo modifié avec succès !");
    }

    // =================================================================
    // AUTRES FONCTIONS (Aperçu, Assign, Delete) - inchangées
    // =================================================================
    
    public function viewMemo($id) {
        $memo = Memo::with('user')->findOrFail($id);
        $this->fillMemoDataView($memo);
        $this->isOpen = true;
    }

    private function fillMemoDataView($memo) {
        // Logique simplifiée pour l'aperçu lecture seule
        $this->memo_id = $memo->id;
        $this->object = $memo->object;
        $this->concern = $memo->concern;
        $this->content = $memo->content;
        $this->date = $memo->created_at->format('d/m/Y');
        $entity = Entity::find($memo->user->entity_id);
        $this->user_entity_name = $entity->name ?? 'Entité';
        $this->user_service = $memo->user->service;
        $this->ref_number = $memo->numero_ref;
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


 

    // GESTION MODALS
    public function closeModal() { $this->isOpen = false; }
    public function closeModalDeux() { 
        $this->isOpen2 = false; 
        $this->reset(['object', 'concern', 'content', 'recipients', 'newAttachments', 'existingAttachments']); 
    }
    public function closeModalTrois() { $this->isOpen3 = false; }
    public function closeModalQuatre() { $this->isOpen4 = false; }

    // =================================================================
    // LOGIQUE D'ASSIGNATION / ENVOI (MODAL 3)
    // =================================================================

    public function assignMemo($id)
    {
        $this->memo_id = $id;
        $this->reset(['workflow_comment', 'selected_visa', 'selected_project_users']);
        $this->memo_type = 'standard'; // Par défaut

        $currentUser = Auth::user();

        // 1. GESTION DU N+1 (MANAGER)
        // On récupère le manager et on vérifie s'il est remplacé
        if ($currentUser->manager_id) {
            $manager = User::find($currentUser->manager_id);
            $this->managerData = $this->resolveUserAvailability($manager);
        } else {
            $this->managerData = null;
        }

        // 2. PRÉPARATION LISTE PROJET
        // Tous les users SAUF : Moi-même ET mon Manager (N+1)
        $excludeIds = [$currentUser->id];
        if ($this->managerData) {
            $excludeIds[] = $this->managerData['original']->id;
        }

        $this->projectUsersList = User::whereNotIn('id', $excludeIds)
                                      ->orderBy('last_name')
                                      ->get()
                                      ->map(function($user) {
                                          // On pré-calcule si ces users sont remplacés pour l'affichage
                                          return $this->resolveUserAvailability($user);
                                      });

        $this->isOpen3 = true;
    }


    /**
     * Helper pour vérifier si un user est remplacé aujourd'hui
     * Retourne un tableau structuré
     */
    private function resolveUserAvailability($user)
    {
        // Sécurité : si aucun utilisateur n'est passé (ex: pas de manager), on renvoie null
        if (!$user) return null;

        // 1. On récupère la date du jour
        // IMPORTANT : Le format doit être identique à ce qu'il y a dans ta DB (Y-m-d)
        $today = Carbon::now()->format('Y-m-d'); 

        // 2. La requête vérifie l'intervalle [Debut ..... AUJOURD'HUI ..... Fin]
        $replacement = ReplacesUser::where('user_id', $user->id) // On cherche si cet user est remplacé
            
            // La date de début doit être passée ou être aujourd'hui
            // (Ex: Si début = 01/12 et on est le 06/12 => "2025-12-01" <= "2025-12-06") -> VRAI
            ->where('date_begin_replace', '<=', $today)
            
            // ET la date de fin doit être dans le futur ou aujourd'hui
            // (Ex: Si fin = 31/12 et on est le 06/12 => "2025-12-31" >= "2025-12-06") -> VRAI
            ->where('date_end_replace', '>=', $today)
            
            ->first();

        // 3. Traitement du résultat
        if ($replacement) {
            // --- CAS : ON EST DANS LA PÉRIODE DE REMPLACEMENT ---
            
            // On cherche le user remplaçant
            $replacingUser = User::find($replacement->user_id_replace);

            // Sécurité supplémentaire : Si le compte du remplaçant a été supprimé entre temps
            if ($replacingUser) {
                return [
                    'original' => $user,           // L'utilisateur de base (Absent)
                    'effective' => $replacingUser, // Le remplaçant (Présent)
                    'is_replaced' => true
                ];
            }
        }

        // --- CAS : PAS DE REMPLACEMENT OU HORS PÉRIODE ---
        return [
            'original' => $user,
            'effective' => $user, // L'utilisateur reste lui-même
            'is_replaced' => false
        ];
    }



    // =================================================================
    // NOUVELLE FONCTION : GÉNÉRATION PDF
    // =================================================================
    public function downloadMemoPDF()
    {
        $memo = Memo::with(['user.entity', 'destinataires.entity'])->findOrFail($this->memo_id);
        
        // Groupement des destinataires
        $recipientsByAction = $memo->destinataires->groupBy('action');

        // 1. Image Logo en Base64
        $pathLogo = public_path('images/logo.jpg');
        $logoBase64 = file_exists($pathLogo) 
            ? 'data:image/jpg;base64,' . base64_encode(file_get_contents($pathLogo)) 
            : null;

        // 2. QR Code en Base64
        $qrCodeBase64 = null;
        if ($memo->qr_code) {
            $qrImage = QrCode::format('svg')->size(100)->generate(route('memo.verify', $memo->qr_code));
            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrImage);
        }

        // 3. Génération du PDF
        $pdf = Pdf::loadView('pdf.memo-layout', [
            'memo' => $memo,
            'recipientsByAction' => $recipientsByAction,
            'logo' => $logoBase64,
            'qrCode' => $qrCodeBase64,
            'date' => $memo->created_at->format('d/m/Y'),
        ]);

        // Configuration A4 Portrait
        $pdf->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Memo_' . $memo->id . '.pdf');
    }


    public function sendMemo()
    {
        $this->validate([
            'selected_visa' => 'required',
            'workflow_comment' => 'nullable|string|max:1000',
            // Si projet, il faut au moins un destinataire
            'selected_project_users' => 'required_if:memo_type,projet|array',
        ], [
            'selected_project_users.required_if' => 'En mode projet, veuillez sélectionner au moins un collaborateur.'
        ]);

        $memo = Memo::find($this->memo_id);
        $currentUser = Auth::user();
        $nextHolders = [];

        // --- SCENARIO A : STANDARD (Envoi au N+1 ou son remplaçant) ---
        if ($this->memo_type === 'standard') {
            if ($this->managerData) {
                // On envoie à l'utilisateur effectif (le remplaçant s'il existe, sinon le manager)
                $nextHolders[] = $this->managerData['effective']->id;
            } else {
                $this->addError('general', "Vous n'avez pas de supérieur hiérarchique défini.");
                return;
            }
        }

        // --- SCENARIO B : PROJET (Envoi aux collaborateur sélectionnés ou leurs remplaçants) ---
        if ($this->memo_type === 'projet') {
            foreach ($this->selected_project_users as $userId) {
                // On revérifie la disponibilité au moment de l'envoi (sécurité)
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

        // Sauvegarde DB
        $memo->previous_holders = [$currentUser->id];
        $memo->current_holders = array_unique($nextHolders); // Éviter doublons
        $memo->status = 'envoyer'; 
        $memo->workflow_comment = $this->workflow_comment; 
        // Optionnel : Sauvegarder le type de mémo si vous avez ajouté la colonne en base
        // $memo->type = $this->memo_type; 
        
        $memo->save();

        Historiques::create([
            'user_id' => $currentUser->id,
            'memo_id' => $memo->id,
            'visa'    => $this->selected_visa,
            'workflow_comment' => $this->workflow_comment ?? 'R.A.S',
        ]);

        // ====================================================================
        // GESTION DES NOTIFICATIONS (Basée sur current_holders / nextHolders)
        // ====================================================================
        
        // On récupère les modèles User correspondant aux IDs trouvés juste au-dessus
        $usersToNotify = User::whereIn('id', $nextHolders)->get();

        foreach ($usersToNotify as $user) {
            // $user : C'est l'utilisateur physique (ex: le Directeur ou son remplaçant)
            // 'sent' : Le type d'action pour afficher le bon message/icône
            $user->notify(new MemoActionNotification($memo, 'envoyer', $currentUser));
        }
        
        // ====================================================================

        $this->closeModalTrois();
        $this->dispatch('notify', message: "Le mémo ($this->memo_type) a été envoyé avec succès.");
    }
   
    

    public function render()
    {
        $memos = Memo::with(['destinataires.entity'])
                   ->where('user_id', Auth::id())
                   ->where('status', 'document') // <--- La condition est ajoutée ici
                   ->where(function($query) {
                       $query->where('object', 'like', '%'.$this->search.'%')
                             ->orWhere('concern', 'like', '%'.$this->search.'%');
                   })
                   ->orderBy('created_at', 'desc')
                   ->paginate(9);

        return view('livewire.memos.drafted-memos', [
            'memos' => $memos,
            'entities' => $this->allEntities // Passe pour le select du modal
        ]);
    }
}