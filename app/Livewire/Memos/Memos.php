<?php

namespace App\Livewire\Memos;

use App\Models\Memo;
use Livewire\Component;
use App\Models\Destinataire;
use App\Models\Destinataires;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Models\Entity; // Assurez-vous d'avoir ce modèle

class Memos extends Component
{
    use WithFileUploads;

    
    // État de la vue
    public $isCreating = false;
    public $activeTab = 'incoming';

    // Champs du Mémo
    #[Rule('required|min:5')]
    public string $object = '';

    #[Rule('required|min:3')]
    public string $concern = '';

    #[Rule('required')]
    public string $content = '';

    public $memoId = null;

    // NOUVEAU : Variable pour les fichiers uploadés (temporaire)
    // Validation : Max 10Mo, types classiques
    #[Rule(['attachments.*' => 'nullable|file|max:10240'])] 
    public $attachments = []; 

    // NOUVEAU : Variable pour conserver les fichiers existants si on édite
    public $existingAttachments = []; 

    // --- GESTION DES DESTINATAIRES ---
    public $recipients = []; // Liste temporaire : [['entity_id' => 1, 'name' => 'RH', 'action' => '...']]
    
    // Champs pour le formulaire d'ajout de destinataire
    public $newRecipientEntity = ''; 
    public $newRecipientAction = '';

    // Liste des actions possibles (Constante ou dynamique)
    public $actionsList = [
        'Faire le nécessaire',
        'Prendre connaissance',
        'Prendre position',
        'Décider'
    ];

    // --- NAVIGATION ---

    public function selectTab(string $tab)
    {
        $this->activeTab = $tab;
        $this->isCreating = false;
    }

    public function createMemo()
    {
        $this->reset(['object', 'content', 'concern', 'memoId', 'recipients', 'newRecipientEntity', 'newRecipientAction','attachments', 'existingAttachments']);
        $this->resetValidation();
        $this->isCreating = true;
    }

    public function cancelCreation()
    {
        $this->isCreating = false;
        $this->reset(['object', 'content', 'concern', 'memoId', 'recipients','attachments', 'existingAttachments']);
    }

    

    // --- LOGIQUE DESTINATAIRES ---

    public function addRecipient()
    {
        $this->validate([
            'newRecipientEntity' => 'required|exists:entities,id',
            'newRecipientAction' => 'required|string'
        ], [
            'newRecipientEntity.required' => 'Veuillez sélectionner une entité.',
            'newRecipientAction.required' => 'Veuillez sélectionner une action.'
        ]);

        // Vérifier les doublons
        foreach ($this->recipients as $recipient) {
            if ($recipient['entity_id'] == $this->newRecipientEntity) {
                $this->addError('newRecipientEntity', 'Cette entité est déjà dans la liste.');
                return;
            }
        }

        // Récupérer le nom de l'entité pour l'affichage
        $entityModel = Entity::find($this->newRecipientEntity);

        $this->recipients[] = [
            'entity_id' => $entityModel->id,
            'entity_name' => $entityModel->name, // ou $entityModel->ref selon votre préférence
            'action' => $this->newRecipientAction
        ];

        // Reset des champs d'ajout
        $this->newRecipientEntity = '';
        $this->newRecipientAction = '';
    }

    public function removeRecipient($index)
    {
        unset($this->recipients[$index]);
        $this->recipients = array_values($this->recipients); // Réindexer le tableau
    }

     // NOUVEAU : Supprimer un fichier de la liste d'upload (avant sauvegarde)
    public function removeAttachment($index)
    {
        array_splice($this->attachments, $index, 1);
    }

    // --- SAUVEGARDE ---

    public function save()
    {
        $this->validate();

        // 1. Traitement des Pièces Jointes
        $finalAttachments = $this->existingAttachments; // On commence avec ceux qui existent déjà (si edit)

        if ($this->attachments) {
            foreach ($this->attachments as $file) {
                // Enregistrement physique dans 'storage/app/public/memos_attachments'
                $path = $file->store('memos_attachments', 'public');
                
                // Construction de la donnée JSON
                $finalAttachments[] = [
                    'name' => $file->getClientOriginalName(), // Nom d'origine (ex: contrat.pdf)
                    'path' => $path,                          // Chemin stockage
                    'mime' => $file->getMimeType(),           // Type (pour l'icône)
                    'size' => $file->getSize()                // Taille
                ];
            }
        }

        // 1. Sauvegarde du Mémo
        $ref = 'REF-' . strtoupper(uniqid()); 

        $memo = Memo::updateOrCreate(
            ['id' => $this->memoId],
            [
                'reference' => $this->memoId ? Memo::find($this->memoId)->reference : $ref,
                'object' => $this->object,
                'concern' => $this->concern,
                'content' => $this->content,
                'pieces_jointes' => $finalAttachments,
                'user_id' => Auth::id()
            ]
        );

        // 2. Sauvegarde des Destinataires
        // Si c'est une modification, on supprime les anciens pour remettre les nouveaux (méthode simple)
        if ($this->memoId) {
            Destinataire::where('memo_id', $memo->id)->delete();
        }

        foreach ($this->recipients as $recipient) {
            Destinataires::create([
                'memo_id' => $memo->id,
                'entity_id' => $recipient['entity_id'],
                'action' => $recipient['action']
            ]);
        }

        $action = $this->memoId ? 'modifié' : 'créé';
        $this->isCreating = false;
        
        $this->dispatch('notify', message: "Mémo $action avec succès !");
    }

    public function render()
    {
        // On récupère les entités pour le select
        $entities = Entity::orderBy('name')->get();

        return view('livewire.memos.memos', [
            'entities' => $entities
        ]);
    }
}