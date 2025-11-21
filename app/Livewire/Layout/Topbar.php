<?php
//Ce composant gérera la barre supérieure, y compris les notifications et les informations de l'utilisateur

namespace App\Livewire\Layout;

use Livewire\Component;

class Topbar extends Component
{
    public $notificationCount = 3;
    public $userName = 'Eva Murphy';

    public function render()
    {
        return view('livewire.layout.topbar');
    }
}
