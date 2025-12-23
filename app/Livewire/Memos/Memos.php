<?php

namespace App\Livewire\Memos;

use App\Models\Memo;
use Livewire\Component;
use App\Models\Destinataires;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Entity;

class Memos extends Component
{
    use WithFileUploads;

    // --- État de la vue ---
    public $isCreating = false;
    public $activeTab = 'incoming';

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
        $this->validate([
            'object'     => 'required|min:5',
            'content'    => 'required',
            'recipients' => 'required|array|min:1',
        ]);

        // OPTIMISATION : On détermine l'action AVANT de modifier le memoId
        $isUpdate = !empty($this->memoId);
        $textAction = $isUpdate ? 'modifié' : 'créé';

        DB::transaction(function () {
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

            $memo = Memo::updateOrCreate(
                ['id' => $this->memoId],
                [
                    'object'         => $this->object,
                    'concern'        => $this->concern,
                    'content'        => $this->content,
                    'pieces_jointes' => $finalAttachments,
                    'user_id'        => Auth::id()
                ]
            );

            // Sync Destinataires
            Destinataires::where('memo_id', $memo->id)->delete();
            
            $dataToInsert = array_map(function($recipient) use ($memo) {
                return [
                    'memo_id'    => $memo->id,
                    'entity_id'  => $recipient['entity_id'],
                    'action'     => $recipient['action'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $this->recipients);

            Destinataires::insert($dataToInsert);
            
            // On met à jour l'ID pour le composant
            $this->memoId = $memo->id;
        });

        $this->isCreating = false;
        $this->dispatch('notify', message: "Mémo $textAction avec succès !");
        
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