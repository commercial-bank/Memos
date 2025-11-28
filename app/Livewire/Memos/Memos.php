<?php

namespace App\Livewire\Memos;


use App\Models\Memo;
use Livewire\Component;
use App\Models\WrittenMemo;
use Illuminate\Http\Request;
use Livewire\Attributes\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;

class Memos extends Component
{
    public $memos;
    public string $activeTab = 'incoming'; // tabs par defaut selectionner

    #[Rule('required')]
    public string $object = '';

    #[Rule('required')]
    public string $concern = '';

    #[Rule('required')]
    public string $type_memo = '';

    #[Rule('required')]
    public string $content = '';

    public $memoId = null;
    public $isOpen = false; // Pour gérer l'ouverture du Modal

    
   

    public function selectTab(string $tab)
    {
        $this->activeTab = $tab;
    }

   public function save()
    {
        

        Memo::updateOrCreate(
            ['id' => $this->memoId],
            [
                'object' => $this->object,
                'concern' => $this->concern,
                'content' => $this->content,
                'user_id' => Auth::id()
            ]
        );

        $action = $this->memoId ? 'modifié' : 'créé';
        
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
        $this->reset(['object', 'content', 'concern',  'memoId']);
    }


    public function render()
    {
       


        // Rafraichir la liste à chaque rendu
        $this->memos = Memo::latest()->get(); 


        return view('livewire.memos.memos');
    }
}
