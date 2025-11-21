<?php

namespace App\Livewire\Memos;

use auth;
use Livewire\Component;
use App\Models\WrittenMemo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;

class Memos extends Component
{
    public string $activeTab = 'incoming'; // tabs par defaut selectionner
    public bool   $showCreateFormModal = false; // Nouvelle propriété pour le modal
    public string $object = '';
    public string $type_memo = '';
    public string $content = '';

    public int $countB;

    public function mount()
    {
         $this->countB = auth()->user()->writtenMemos()->count();
    }

    public function selectTab(string $tab)
    {
        $this->activeTab = $tab;
    }

   public function store(Request $request)
    {
        
        // 1. Correction Syntaxe : On utilise $request->validate()
        $validated = $request->validate([
            'object' => ['required', 'string', 'max:255'],
            'type_memo' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        

        // 2. Correction Logique : On ajoute l'ID de l'utilisateur connecté
        // On fusionne les données validées avec l'ID user
        $data = array_merge($validated, [
            'user_id' => auth()->id() // ou $request->user()->id
        ]);

        WrittenMemo::create($data);

        // 3. Correction UX : On redirige l'utilisateur avec un message
        return redirect()->back()->with('success', 'Le mémo a été enregistré avec succès !');
    }

  

    // Méthodes pour ouvrir/fermer le modal
    public function openCreateForm()
    {
        $this->showCreateFormModal = true;
    }

    

    public function closeCreateForm()
    {
        $this->showCreateFormModal = false;
    }

    
    public function render()
    {
        return view('livewire.memos.memos');
    }
}
