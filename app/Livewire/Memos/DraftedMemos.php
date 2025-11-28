<?php

namespace App\Livewire\Memos;

use App\Models\Memo;
use App\Models\Entity;
use Livewire\Component;
use Illuminate\Http\Request;
use App\Models\Destinataires;
use Livewire\Attributes\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;

class DraftedMemos extends Component
{
    public $memos;

    #[Rule('required')]
    public string $object = '';

    #[Rule('required')]
    public string $concern = '';

    #[Rule('required')]
    public string $content = '';

    public $memo_id = null;
    
    public $isOpen = false; // Pour gérer l'ouverture du Modal
    public $isOpen2 = false; // Pour gérer l'ouverture du Modal2
    public $isOpen3 = false; // Pour gérer l'ouverture du Modal3
    public $isOpen4 = false; // Pour gérer l'ouverture du Modal4

    public $date;
    public $user_service;
    public $user_first_name;
    public $user_last_name;
    public $user_entity_name;

     // NOUVELLES PROPRIÉTÉS POUR LE MODAL 3
    public $allEntities = []; 
    public $selections = []; // Va contenir les choix de l'utilisateur


    public function mount()
    {
         $this->memos = Memo::where('user_id', Auth::id())
                   ->orderBy('id', 'desc') // Trie par ID décroissant
                   ->get();;
    }

    public function viewMemo($id)
    {
        $memo = Memo::findOrFail($id);
        $this->object = $memo->object;
        $this->concern = $memo->concern;
        $this->content = $memo->content;
        $this->memo_id = $memo->id;
        $this->date = $memo->created_at->format('d/m/Y');   
        $this->user_first_name = $memo->user->first_name;
        $this->user_last_name = $memo->user->last_name;
        $this->user_service = $memo->user->service;
        $this->user_entity_name = $memo->user->entity_name;
        $this->openModal();
    }

    public function editMemo($id)
    {
        $memo = Memo::findOrFail($id);
         $this->object = $memo->object;
        $this->concern = $memo->concern;
        $this->content = $memo->content;
        $this->memo_id = $memo->id;
        $this->date = $memo->created_at->format('d/m/Y');   
        $this->user_first_name = $memo->user->first_name;
        $this->user_last_name = $memo->user->last_name;
        $this->user_service = $memo->user->service;
        $this->user_entity_name = $memo->user->entity_name;
        $this->openModalDeux();
    }

    public function save()
    {
        

        Memo::updateOrCreate(
            ['id' => $this->memo_id],
            [
                'object' => $this->object,
                'concern' => $this->concern,
                'content' => $this->content,
                'user_id' => Auth::id()
            ]
        );

        $action = $this->memo_id ? 'modifié' : 'créé';
        
        $this->closeModalDeux();
        
        // Envoi de l'événement pour le Toast
        $this->dispatch('notify', message: "Brouillon $action avec succès !");
    }

    public function assignMemo($id)
    {
        $this->memo_id = $id;
        
        // 1. Récupérer toutes les entités (Directions/Services)
        $this->allEntities = Entity::all();

        // 2. Initialiser le tableau des sélections
        // On vérifie si des attributions existent déjà pour ne pas les écraser visuellement
        $existingAssignments = Destinataires::where('memo_id', $id)->get()->keyBy('entity_id');

        $this->selections = [];
        
        foreach($this->allEntities as $entity) {
            $exists = $existingAssignments->has($entity->id);
            
            $this->selections[$entity->id] = [
                'checked' => $exists, // Coché si déjà attribué
                'action' => $exists ? $existingAssignments[$entity->id]->action : 'Faire le nécessaire' // Valeur par défaut
            ];
        }

        $this->isOpen3 = true;
    }

    public function saveAssignments()
    {
       
        // On nettoie d'abord les anciennes attributions pour ce mémo (optionnel, selon ta logique métier)
        // Ici je supprime tout et je recrée pour gérer les décochages facilement
        Destinataires::where('memo_id', $this->memo_id)->delete();

        // On parcourt les sélections
        foreach ($this->selections as $entityId => $data) {
            // Si la case est cochée
            if (isset($data['checked']) && $data['checked'] == true) {
                Destinataires::create([
                    'memo_id' => $this->memo_id,
                    'entity_id' => $entityId,
                    'action' => $data['action'] ?? 'Faire le nécessaire', // Sécurité si vide
                ]);
            }
        }
        
        Memo::updateOrCreate(
            ['id' => $this->memo_id],
            [
                'status' => "document",
            ]
        );

        $this->dispatch('notify', message: "Memo creer avec succès !");
        $this->closeModalTrois();
    }

    public function deleteMemo($id)
    {
         $this->memo_id = $id;
         $this->openModalQuatre();
    }

    public function del()
    {
        
         $memo = Memo::find($this->memo_id);
        // 2. Sécurité : On vérifie s'il existe et si c'est bien celui de l'utilisateur connecté
        if ($memo && $memo->user_id === Auth::id()) {
            
            // 3. Suppression (Grâce au 'onDelete cascade' dans ta migration, 
            // les liaisons dans la table pivot 'memos' seront supprimées automatiquement)
            $memo->delete();
        }

        $this->closeModalQuatre();

        // Envoi de l'événement pour le Toast
        $this->dispatch('notify', message: "Brouillon supprimer avec succès !");
    }

     // Ouvrir le modal
    public function openModal()
    {
        $this->resetValidation();
        $this->isOpen = true;
    }

    // Fermer le modal et reset les champs
    public function closeModal()
    {
        $this->isOpen = false;
         $this->reset(['object','concern', 'content',  'memo_id']);
    }

    // Ouvrir le modal
    public function openModalDeux()
    {
        $this->isOpen2 = true;
    }

    // Fermer le modal et reset les champs
    public function closeModalDeux()
    {
        $this->isOpen2 = false;
    }

     // Fermer le modal et reset les champs
    public function closeModalTrois()
    {
        $this->isOpen3 = false;
        $this->selections = []; // Reset
    }

    // Ouvrir le modal
    public function openModalQuatre()
    {
        $this->isOpen4 = true;
    }

    // Fermer le modal et reset les champs
    public function closeModalQuatre()
    {
        $this->isOpen4 = false;
    }

    

    public function render()
    {
       $this->memos = Memo::where('user_id', Auth::id())
                   ->orderBy('id', 'desc') // Trie par ID décroissant
                   ->get();
        
        return view('livewire.memos.drafted-memos');
    }
}
