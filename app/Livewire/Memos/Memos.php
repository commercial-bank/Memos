<?php

namespace App\Livewire\Memos;


use Livewire\Component;
use App\Models\WrittenMemo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;

class Memos extends Component
{
    public $writtenMemo;
    public string $activeTab = 'incoming'; // tabs par defaut selectionner

    #[Rule('required')]
    public string $object = '';

    #[Rule('required')]
    public string $type_memo = '';

    #[Rule('required')]
    public string $content = '';

    public $writtenMemoId = null;
    public $dest_status = false;
    public $isOpen = false; // Pour gérer l'ouverture du Modal

    public int $countB;

    public function mount()
    {
         $this->countB = auth()->user()->writtenMemos()->count();
         $this->writtenMemo =  WrittenMemo::all();
    }

    public function selectTab(string $tab)
    {
        $this->activeTab = $tab;
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
        
        $this->closeModal();
        
        // Envoi de l'événement pour le Toast
        $this->dispatch('notify', message: "Brouillon $action avec succès !");
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
        $this->reset(['object', 'content', 'type_memo', 'dest_status', 'writtenMemoId']);
    }


    public function render()
    {
        // Rafraichir la liste à chaque rendu
        $this->writtenMemo = WrittenMemo::latest()->get(); 
        return view('livewire.memos.memos');
    }
}
