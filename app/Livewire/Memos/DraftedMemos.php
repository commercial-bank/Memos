<?php

namespace App\Livewire\Memos;

use App\Models\Memo;
use App\Models\Entity;
use Livewire\Component;
use App\Models\WrittenMemo;
use Livewire\Attributes\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;

class DraftedMemos extends Component
{
    public $writtenMemo;

    #[Rule('required')]
    public string $object = '';

    #[Rule('required')]
    public string $type_memo = '';

    #[Rule('required')]
    public string $content = '';

    public $writtenMemoId = null;
    public $dest_status = false;
    public $isOpen = false; // Pour gérer l'ouverture du Modal
    public $isOpen2 = false; // Pour gérer l'ouverture du Modal2
    public $isOpen3 = false; // Pour gérer l'ouverture du Modal3

    public $date;
    public $user_service;
    public $user_first_name;
    public $user_last_name;
    public $user_entity;

     // NOUVELLES PROPRIÉTÉS POUR LE MODAL 3
    public $allEntities = []; 
    public $selections = []; // Va contenir les choix de l'utilisateur


    public function mount()
    {
         $this->writtenMemo = WrittenMemo::where('user_id', Auth::id())->get();
    }

    public function save()
    {
        

        WrittenMemo::updateOrCreate(
            ['id' => $this->writtenMemoId],
            [
                'object' => $this->object,
                'type_memo' => $this->type_memo,
                'content' => $this->content,
                'dest_status' => $this->dest_status,
                'user_id' => Auth::id()
            ]
        );

        $action = $this->writtenMemoId ? 'modifié' : 'créé';
        
        $this->closeModalDeux();
        
        // Envoi de l'événement pour le Toast
        $this->dispatch('notify', message: "Brouillon $action avec succès !");
    }

    // --- NOUVELLE MÉTHODE : Enregistrer les attributions ---
    public function saveAssignments()
    {
        // On nettoie d'abord les anciennes attributions pour ce mémo (optionnel, selon ta logique métier)
        // Ici je supprime tout et je recrée pour gérer les décochages facilement
        Memo::where('written_memo_id', $this->writtenMemoId)->delete();

        // On parcourt les sélections
        foreach ($this->selections as $entityId => $data) {
            // Si la case est cochée
            if (isset($data['checked']) && $data['checked'] == true) {
                Memo::create([
                    'written_memo_id' => $this->writtenMemoId,
                    'entity_id' => $entityId,
                    'action' => $data['action'] ?? 'Pour attribution', // Sécurité si vide
                ]);
            }
        }

        $this->dispatch('notify', message: "Memo creer avec succès !");
        $this->closeModalTrois();
    }

    
    // Ouvrir le modal
    public function openModal()
    {
        $this->resetValidation();
        $this->isOpen = true;
    }

    // Ouvrir le modal
    public function openModalDeux()
    {
        $this->isOpen2 = true;
    }

     // --- NOUVELLE MÉTHODE : Ouvrir le Modal d'attribution ---
    public function assignWritten($id)
    {
        $this->writtenMemoId = $id;
        
        // 1. Récupérer toutes les entités (Directions/Services)
        $this->allEntities = Entity::all();

        // 2. Initialiser le tableau des sélections
        // On vérifie si des attributions existent déjà pour ne pas les écraser visuellement
        $existingAssignments = Memo::where('written_memo_id', $id)->get()->keyBy('entity_id');

        $this->selections = [];
        
        foreach($this->allEntities as $entity) {
            $exists = $existingAssignments->has($entity->id);
            
            $this->selections[$entity->id] = [
                'checked' => $exists, // Coché si déjà attribué
                'action' => $exists ? $existingAssignments[$entity->id]->action : 'Pour attribution' // Valeur par défaut
            ];
        }

        $this->isOpen3 = true;
    }

    // Fermer le modal et reset les champs
    public function closeModal()
    {
        $this->isOpen = false;
         $this->reset(['object', 'content', 'type_memo', 'dest_status', 'writtenMemoId']);
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

    

    


    public function viewWritten($id)
    {
        $written = WrittenMemo::findOrFail($id);
        $this->object = $written->object;
        $this->type_memo = $written->type_memo;
        $this->content = $written->content;
        $this->writtenMemoId = $written->id;
        $this->date = $written->created_at->format('d/m/Y à H:i');   
        $this->user_first_name = $written->user->first_name;
        $this->user_last_name = $written->user->last_name;
        $this->user_service = $written->user->service;
        $this->user_entity = $written->user->entity;
        $this->openModal();
    }

    public function editWritten($id)
    {
        $written = WrittenMemo::findOrFail($id);
        $this->object = $written->object;
        $this->type_memo = $written->type_memo;
        $this->content = $written->content;
        $this->writtenMemoId = $written->id;
        $this->date = $written->created_at->format('d/m/Y à H:i');   
        $this->user_first_name = $written->user->first_name;
        $this->user_last_name = $written->user->last_name;
        $this->user_service = $written->user->service;
        $this->user_entity = $written->user->entity;
        $this->openModalDeux();
    }


    public function render()
    {
        $this->writtenMemo = WrittenMemo::where('user_id', Auth::id())->get();
        return view('livewire.memos.drafted-memos');
    }
}
