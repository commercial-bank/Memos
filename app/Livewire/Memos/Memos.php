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

    // =================================================================================================
    // 1. PROPRIÉTÉS D'ÉTAT DE L'INTERFACE (UI) & NAVIGATION
    // =================================================================================================
    
    // Onglet actif (conservé dans l'URL)
    #[Url(keep: true)] 
    public $activeTab = 'incoming';

    // Indicateur du mode création (affiche/masque le formulaire)
    public $isCreating = false;

    // Préférence utilisateur (Dark Mode)
    public $darkMode = false;


    // =================================================================================================
    // 2. PROPRIÉTÉS DU FORMULAIRE (CHAMPS PRINCIPAUX)
    // =================================================================================================

    public $memoId = null;

    #[Rule('required|min:5')]
    public string $object = '';

    #[Rule('nullable|min:3')]
    public string $concern = '';

    #[Rule('required')]
    public string $content = '';


    // =================================================================================================
    // 3. PROPRIÉTÉS DE GESTION DES DESTINATAIRES & RECHERCHE
    // =================================================================================================

    // Liste finale des destinataires ajoutés
    public $recipients = []; 

    // Action choisie pour le nouveau destinataire
    public $newRecipientAction = '';

    // Liste statique des actions possibles
    public $actionsList = [
        'Faire le nécessaire',
        'Prendre connaissance',
        'Prendre position',
        'Décider'
    ];

    // --- Variables de recherche dynamique ---
    
    // Champ de saisie pour la recherche
    public $searchRecipient = '';
    
    // ID de l'entité sélectionnée après recherche
    public $newRecipientEntity = null; 
    
    // Drapeau pour empêcher le reset lors du clic de sélection
    protected $isSelection = false; 


    // =================================================================================================
    // 4. PROPRIÉTÉS DE GESTION DES PIÈCES JOINTES
    // =================================================================================================

    #[Rule(['attachments.*' => 'nullable|file|max:10240'])] 
    public $attachments = []; 

    public $existingAttachments = []; 


    // =================================================================================================
    // 5. INITIALISATION (LIFECYCLE)
    // =================================================================================================
    
    public function mount()
    {
         $this->darkMode = Auth::user()->dark_mode ?? false;
    }


    // =================================================================================================
    // 6. GESTION DES ÉVÉNEMENTS UI & NAVIGATION
    // =================================================================================================

    // AJOUTER CETTE MÉTHODE POUR ÉCOUTER L'ÉVÉNEMENT
    #[On('load-model-data')]
    public function loadDraftFromModel($memoId)
    {
        // 1. Récupérer le mémo favori
        $sourceMemo = Memo::with(['destinataires.entity'])->findOrFail($memoId);

        // 2. Réinitialiser le formulaire pour éviter les résidus
        $this->reset([
            'object', 'content', 'concern', 'memoId', 'recipients', 
            'attachments', 'existingAttachments', 'searchRecipient'
        ]);
        $this->resetValidation();

        // 3. Charger les données textuelles
        $this->object = $sourceMemo->object; // (Optionnel: "Copie de " . ...)
        $this->concern = $sourceMemo->concern;
        $this->content = $sourceMemo->content;

        // 4. Charger les destinataires (Conversion du format BD vers format Formulaire)
        // Le formulaire attend : ['entity_id', 'entity_name', 'action']
        if ($sourceMemo->destinataires) {
            foreach ($sourceMemo->destinataires as $dest) {
                if ($dest->entity) {
                    $this->recipients[] = [
                        'entity_id'   => $dest->entity->id,
                        'entity_name' => $dest->entity->name,
                        'action'      => $dest->action
                    ];
                }
            }
        }

        // 5. Ouvrir le formulaire
        $this->isCreating = true;

        // 6. Notification visuelle (Optionnel)
        $this->dispatch('notify', 
            message: "Modèle chargé ! Vous pouvez maintenant le modifier.", 
            type: 'info'
        );
    }

    #[On('dark-mode-toggled')]
    public function updateDarkMode($darkMode)
    {
        $this->darkMode = $darkMode;

        // Sauvegarde immédiate dans la base de données
        $user = Auth::user();
        if ($user) {
            $user->update(['dark_mode' => $darkMode]);
        }
    }

    /**
     * Change l'onglet actif et ferme le mode création
     */
    public function selectTab(string $tab)
    {
        $this->activeTab = $tab;
        $this->isCreating = false;
    }

    /**
     * Ouvre le formulaire de création et réinitialise les données
     */
    public function createMemo()
    {
        $this->reset([
            'object', 'content', 'concern', 'memoId', 'recipients', 
            'newRecipientEntity', 'newRecipientAction', 'attachments', 'existingAttachments', 'searchRecipient'
        ]);
        $this->resetValidation();
        $this->isCreating = true;
    }

    /**
     * Annule la création et ferme le formulaire
     */
    public function cancelCreation()
    {
        $this->isCreating = false;
        $this->reset([
            'object', 'content', 'concern', 'memoId', 'recipients', 
            'attachments', 'existingAttachments', 'searchRecipient'
        ]);
    }


    // =================================================================================================
    // 7. LOGIQUE DE RECHERCHE ET SÉLECTION (AUTOCOMPLETE)
    // =================================================================================================

    /**
     * Détecte quand l'utilisateur tape dans le champ de recherche
     */
    public function updatedSearchRecipient()
    {
        // Si le changement vient d'un clic (flag true), on ne fait rien
        if ($this->isSelection) {
            $this->isSelection = false;
            return;
        }

        // Sinon, c'est que l'utilisateur tape : on invalide l'ID précédent
        $this->newRecipientEntity = null;
    }

    /**
     * Sélectionne une entité depuis la liste déroulante
     */
    public function selectRecipientEntity($id, $name)
    {
        $this->isSelection = true; // On active le drapeau
        $this->newRecipientEntity = $id;
        $this->searchRecipient = $name; // Met à jour le texte affiché
    }

    /**
     * Filtre dynamique des entités (Propriété calculée)
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


    // =================================================================================================
    // 8. MANIPULATION DES LISTES (DESTINATAIRES & FICHIERS)
    // =================================================================================================

    /**
     * Ajoute un destinataire à la liste temporaire
     */
    public function addRecipient()
    {
        // Validation spécifique
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

    /**
     * Retire un destinataire de la liste
     */
    public function removeRecipient($index)
    {
        unset($this->recipients[$index]);
        $this->recipients = array_values($this->recipients);
    }

    /**
     * Retire une pièce jointe de la liste
     */
    public function removeAttachment($index)
    {
        array_splice($this->attachments, $index, 1);
    }


    // =================================================================================================
    // 9. PERSISTANCE DES DONNÉES (SAUVEGARDE)
    // =================================================================================================

    public function save()
    {
        $this->validate([
            'object'     => 'required|min:5',
            'content'    => 'required',
            'recipients' => 'required|array|min:1',
        ]);

        $isUpdate = !empty($this->memoId);

        DB::transaction(function () {
            // Traitement des fichiers
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

            // Préparation des destinataires en JSON
            $destinatairesJson = array_map(function($recipient) {
                return [
                    'entity_id'         => $recipient['entity_id'],
                    'action'            => $recipient['action'],
                    'processing_status' => 'en_cours',
                    'created_at'        => now()->toDateTimeString(),
                ];
            }, $this->recipients);

            // Mise à jour ou Création en base
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


    // =================================================================================================
    // 10. AFFICHAGE (RENDER)
    // =================================================================================================

    public function render()
    {
        return view('livewire.memos.memos');
    }
}