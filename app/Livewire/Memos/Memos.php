<?php

namespace App\Livewire\Memos;

use App\Models\Memo;
use App\Models\Entity;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\DraftedMemo;
use App\Models\Destinataires;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;

class Memos extends Component
{
    use WithFileUploads;

    // --- État de la vue ---
    public $isCreating = false;

     // 2. AJOUTER L'ATTRIBUT #[Url]
    // 'keep: true' permet de garder le paramètre dans l'URL même après des actions Ajax
    #[Url(keep: true)] 
    public $activeTab = 'incoming';

    
    public $darkMode = false;

    // --- Recherche Destinataire ---
    public $searchRecipient = '';
    public $newRecipientEntity = null; // ID de l'entité sélectionnée
    
    // DRAPEAU CRUCIAL : Empêche le reset lors de la sélection
    protected $isSelection = false; 

    // --- Champs du Mémo ---
    #[Rule('required|min:5')]
    public string $object = '';

    #[Rule('nullable|min:3')]
    public string $concern = '';

    #[Rule('required')]
    public string $content = '';

    public $memoId = null;

    // --- Gestion des Pièces Jointes ---
    #[Rule(['attachments.*' => 'nullable|file|max:10240'])] 
    public $attachments = []; 

    public $existingAttachments = []; 

    // --- Gestion des Destinataires (Liste finale) ---
    public $recipients = []; 
    public $newRecipientAction = '';

    public $actionsList = [
        'Faire le nécessaire',
        'Prendre connaissance',
        'Prendre position',
        'Décider'
    ];

    // =========================================================
    // INITIALISATION
    // =========================================================
    
    public function mount()
    {
         $this->darkMode = Auth::user()->dark_mode ?? false;
    }

    #[On('dark-mode-toggled')]
    public function updateDarkMode($darkMode)
    {
        $this->darkMode = $darkMode;

        // AJOUT : Sauvegarde immédiate dans la base de données
        $user = Auth::user();
        if ($user) {
            $user->update(['dark_mode' => $darkMode]);
        }
    }

    public function selectTab(string $tab)
    {
        $this->activeTab = $tab;
        $this->isCreating = false;
    }

    public function createMemo()
    {
        $this->reset([
            'object', 'content', 'concern', 'memoId', 'recipients', 
            'newRecipientEntity', 'newRecipientAction', 'attachments', 'existingAttachments', 'searchRecipient'
        ]);
        $this->resetValidation();
        $this->isCreating = true;
    }

    public function cancelCreation()
    {
        $this->isCreating = false;
        $this->reset([
            'object', 'content', 'concern', 'memoId', 'recipients', 
            'attachments', 'existingAttachments', 'searchRecipient'
        ]);
    }

    // =========================================================
    // LOGIQUE DE RECHERCHE ET SELECTION (CORRIGÉE)
    // =========================================================

    /**
     * 1. Détecte quand l'utilisateur tape dans le champ
     */
    public function updatedSearchRecipient()
    {
        // Si le changement vient d'un clic (flag true), on ne fait rien
        // et on remet le flag à false pour la prochaine frappe.
        if ($this->isSelection) {
            $this->isSelection = false;
            return;
        }

        // Sinon, c'est que l'utilisateur tape : on invalide l'ID précédent
        $this->newRecipientEntity = null;
    }

    /**
     * 2. Sélectionne une entité depuis la liste
     */
    public function selectRecipientEntity($id, $name)
    {
        $this->isSelection = true; // On active le drapeau
        $this->newRecipientEntity = $id;
        $this->searchRecipient = $name; // Met à jour le texte affiché
    }

    /**
     * 3. Filtre dynamique (Propriété calculée)
     */
    public function getFilteredEntitiesProperty()
    {
        if (empty($this->searchRecipient)) {
            return [];
        }

        $term = '%' . $this->searchRecipient . '%';

        return Entity::whereIn('type', ['Direction', 'Sous-Direction'])
            ->where(function($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('ref', 'like', $term);
            })
            ->orderBy('name', 'asc')
            ->limit(10)
            ->get();
    }

    /**
     * 4. Ajoute à la liste temporaire
     */
    public function addRecipient()
    {
        // Validation
        $this->validate([
            'newRecipientEntity' => 'required|integer|exists:entities,id',
            'newRecipientAction' => 'required|string'
        ], [
            'newRecipientEntity.required' => 'Veuillez sélectionner une entité valide dans la liste.', 
            'newRecipientAction.required' => 'Veuillez choisir une action.'
        ]);

        // Vérification doublon
        if (collect($this->recipients)->contains('entity_id', $this->newRecipientEntity)) {
            $this->addError('newRecipientEntity', 'Cette entité est déjà dans la liste.');
            return;
        }

        $entity = Entity::find($this->newRecipientEntity);

        if ($entity) {
            $this->recipients[] = [
                'entity_id'   => $entity->id,
                'entity_name' => $entity->name,
                'action'      => $this->newRecipientAction
            ];

            // Reset complet pour la prochaine entrée
            $this->reset(['newRecipientEntity', 'newRecipientAction', 'searchRecipient']);
        }
    }

    public function removeRecipient($index)
    {
        unset($this->recipients[$index]);
        $this->recipients = array_values($this->recipients);
    }

    public function removeAttachment($index)
    {
        array_splice($this->attachments, $index, 1);
    }

    // =========================================================
    // SAUVEGARDE
    // =========================================================

    public function save()
    {
        $this->validate([
            'object'     => 'required|min:5',
            'content'    => 'required',
            'recipients' => 'required|array|min:1',
        ]);

        $isUpdate = !empty($this->memoId);

        DB::transaction(function () {
            // Fichiers
            $finalAttachments = $this->existingAttachments;
            if (!empty($this->attachments)) {
                foreach ($this->attachments as $file) {
                    $path = $file->store('memos_attachments', 'public');
                    $finalAttachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'mime' => $file->getMimeType(),
                        'size' => $file->getSize()
                    ];
                }
            }

            // Destinataires JSON
            $destinatairesJson = array_map(function($recipient) {
                return [
                    'entity_id'         => $recipient['entity_id'],
                    'action'            => $recipient['action'],
                    'processing_status' => 'en_cours',
                    'created_at'        => now()->toDateTimeString(),
                ];
            }, $this->recipients);

            // DB Update/Create
            $memo = DraftedMemo::updateOrCreate(
                ['id' => $this->memoId],
                [
                    'object'            => $this->object,
                    'concern'           => $this->concern,
                    'content'           => $this->content,
                    'status'            => 'brouillon',
                    'workflow_direction'=> 'sortant',
                    'pieces_jointes'    => $finalAttachments,
                    'destinataires'     => $destinatairesJson,
                    'user_id'           => Auth::id(),
                    'current_holders'   => [Auth::id()], 
                ]
            );
            
            $this->memoId = $memo->id;
        });

        $this->isCreating = false;
        
        $this->dispatch('notify', 
            message: $isUpdate ? "Brouillon mis à jour !" : "Mémo enregistré en brouillon !",
            type: 'success'
        );
        
        $this->reset(['object', 'content', 'concern', 'memoId', 'recipients', 'attachments', 'searchRecipient']);
    }

    public function render()
    {
        return view('livewire.memos.memos');
    }
}