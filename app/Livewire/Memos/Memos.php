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

class Memos extends Component
{
    use WithFileUploads;

    // --- État de la vue ---
    public $isCreating = false;
    public $activeTab = 'incoming';
    public $darkMode = false; // Ajout pour le Dark Mode

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

    // --- Gestion des Destinataires ---
    public $recipients = []; 
    public $newRecipientEntity = ''; 
    public $newRecipientAction = '';

    // Liste des actions possibles
    public $actionsList = [
        'Faire le nécessaire',
        'Prendre connaissance',
        'Prendre position',
        'Décider'
    ];

    // =========================================================
    // 0. GESTION DARK MODE
    // =========================================================
    
    public function mount()
    {
        $this->darkMode = session()->get('dark_mode', false);
    }

    #[On('dark-mode-toggled')]
    public function updateDarkMode($darkMode)
    {
        $this->darkMode = $darkMode;
    }

    // =========================================================
    // 1. NAVIGATION
    // =========================================================

    public function selectTab(string $tab)
    {
        $this->activeTab = $tab;
        $this->isCreating = false;
    }

    /**
     * Initialise le formulaire de création
     */
    public function createMemo()
    {
        $this->reset([
            'object', 'content', 'concern', 'memoId', 'recipients', 
            'newRecipientEntity', 'newRecipientAction', 'attachments', 'existingAttachments'
        ]);
        $this->resetValidation();
        $this->isCreating = true;
    }

    /**
     * Annule et réinitialise le formulaire
     */
    public function cancelCreation()
    {
        $this->isCreating = false;
        $this->reset([
            'object', 'content', 'concern', 'memoId', 'recipients', 
            'attachments', 'existingAttachments'
        ]);
    }

    // =========================================================
    // 2. LOGIQUE DESTINATAIRES
    // =========================================================

    /**
     * Ajoute un destinataire à la liste temporaire
     */
    public function addRecipient()
    {
        $this->validate([
            'newRecipientEntity' => 'required|exists:entities,id',
            'newRecipientAction' => 'required|string'
        ], [
            'newRecipientEntity.required' => 'Veuillez sélectionner une entité.',
            'newRecipientAction.required' => 'Veuillez sélectionner une action.'
        ]);

        // Vérification des doublons de manière performante
        if (collect($this->recipients)->contains('entity_id', $this->newRecipientEntity)) {
            $this->addError('newRecipientEntity', 'Cette entité est déjà dans la liste.');
            return;
        }

        // Récupération de l'entité
        $entityModel = Entity::find($this->newRecipientEntity);

        if ($entityModel) {
            $this->recipients[] = [
                'entity_id'   => $entityModel->id,
                'entity_name' => $entityModel->name,
                'action'      => $this->newRecipientAction
            ];
        }

        // Reset des champs de saisie uniquement
        $this->reset(['newRecipientEntity', 'newRecipientAction']);
    }

    /**
     * Retire un destinataire de la liste temporaire
     */
    public function removeRecipient($index)
    {
        unset($this->recipients[$index]);
        $this->recipients = array_values($this->recipients); // Réindexation
    }

    /**
     * Supprime un fichier de la liste d'upload
     */
    public function removeAttachment($index)
    {
        array_splice($this->attachments, $index, 1);
    }

    // =========================================================
    // 3. SAUVEGARDE ET PERSISTENCE
    // =========================================================

    public function save()
    {
        // 1. Validation
        $this->validate([
            'object'     => 'required|min:5',
            'content'    => 'required',
            'recipients' => 'required|array|min:1',
        ]);

        $isUpdate = !empty($this->memoId);

        DB::transaction(function () {
            // 2. Gestion des pièces jointes (JSON)
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

            // 3. Transformation des destinataires pour le champ JSON
            // On prépare le tableau selon la structure de votre table 'destinataires'
            $destinatairesJson = array_map(function($recipient) {
                return [
                    'entity_id'         => $recipient['entity_id'],
                    'action'            => $recipient['action'],
                    'processing_status' => 'en_cours', // Statut par défaut demandé
                    'created_at'        => now()->toDateTimeString(),
                ];
            }, $this->recipients);

            // 4. Persistence dans drafted_memos
            $memo = DraftedMemo::updateOrCreate(
                ['id' => $this->memoId],
                [
                    'object'            => $this->object,
                    'concern'           => $this->concern,
                    'content'           => $this->content,
                    'status'            => 'brouillon',
                    'workflow_direction'=> 'sortant',
                    'pieces_jointes'    => $finalAttachments,
                    'destinataires'     => $destinatairesJson, // Stockage JSON ici
                    'user_id'           => Auth::id(),
                    // Initialisation du détenteur actuel (l'auteur)
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
        
        $this->reset(['object', 'content', 'concern', 'memoId', 'recipients', 'attachments']);
    }

    // =========================================================
    // 4. RENDU
    // =========================================================

    public function render()
    {
        // On récupère les entités triées pour le formulaire
        $entities = Entity::orderBy('name', 'asc')->get();

        return view('livewire.memos.memos', [
            'entities' => $entities
        ]);
    }
}