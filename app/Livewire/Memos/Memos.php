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
        $countincong=0;
        $userId = Auth::id(); 

        $groupedMemos = WrittenMemo::where('current_holder_id', Auth::id())
            ->has('memos')
            ->with(['memos.entity', 'user'])
            ->latest()
            ->get();

        // B. Mémos reçus (Circuit de diffusion - Je suis secrétaire d'entité ou directeur destinataire)
        // On récupère les WrittenMemos via la table pivot où je suis le détenteur local
        $distributedMemos = WrittenMemo::whereHas('memos', function($q) use ($userId) {
                $q->where('local_holder_id', $userId);
            })
            ->with(['user', 'memos' => function($q) use ($userId) {
                // On charge uniquement mon attribution pour savoir mon statut local
                $q->where('local_holder_id', $userId);
            }, 'memos.entity'])
            ->get();

       
        $countincong = $groupedMemos->merge($distributedMemos)->count();
        // Rafraichir la liste à chaque rendu
        $this->writtenMemo = WrittenMemo::latest()->get(); 

        $this->countB = auth()->user()->writtenMemos()->count();

        return view('livewire.memos.memos', [
            'notif' => $countincong
        ]);
    }
}
