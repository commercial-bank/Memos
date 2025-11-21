<?php

namespace App\Livewire\Memos\Details;

use Livewire\Component;

class RejectMemo extends Component
{

    public $memo;
    public $showModal = false;

    #[On('rejectMemo')]
    public function rejectMemo($memoId)
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
        return view('livewire.memos.details.reject-memo');
    }
}
