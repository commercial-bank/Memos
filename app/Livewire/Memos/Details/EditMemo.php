<?php

namespace App\Livewire\Memos\Details;

use Livewire\Component;

class EditMemo extends Component
{
    public $memo;
    public $showModal = false;

    #[On('editMemo')]
    public function editMemo($memoId)
    {
        // Récupérer tous les mémos simulés
        $allMemos = $this->getMemosData();

        // Trouver le mémo correspondant à l'ID
        $foundMemo = null;
        foreach ($allMemos as $m) {
            if ($m->id == $memoId) {
                $foundMemo = $m;
                break;
            }
        }

        $this->memo = $foundMemo;
        $this->showModal = true;
    }

    public function render()
    {
        return view('livewire.memos.details.edit-memo');
    }
}
