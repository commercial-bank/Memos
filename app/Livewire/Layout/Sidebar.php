<?php
//Ce composant gérera la navigation latérale. Il peut contenir l'état de l'onglet actif si nous voulions le mettre en évidence.
namespace App\Livewire\Layout;

use Livewire\Component;

class Sidebar extends Component
{

   //ici nous allons gerer les onglets du sidebar grace a la variable $activeTab 
   public $activeTab = 'memoriums'; // Pour gérer l'onglet actif dans la sidebar

    public function render()
    {
        return view('livewire.layout.sidebar');
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }
}
