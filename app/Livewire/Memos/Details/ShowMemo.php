<?php

namespace App\Livewire\Memos\Details;

use Livewire\Component;
use Livewire\Attributes\On; // N'oubliez pas d'importer l'attribut On

class ShowMemo extends Component
{
    public $memo;
    public $showModal = false; // C'est cette propriété qui doit contrôler l'affichage

    // La méthode mount n'a plus besoin d'un paramètre showOption
    public function mount()
    {
        // Vous pouvez initialiser d'autres choses ici si nécessaire
    }

    // L'écouteur d'événement est parfait pour ouvrir le modal
    #[On('show-memo-modal')] // Renommez l'événement pour être plus spécifique
    public function show($memoId)
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
        $this->showModal = true; // Ouvre le modal
    }

    public function closeModal()
    {
        $this->showModal = false; // Ferme le modal
        $this->memo = null; // Nettoie le mémo affiché
    }

    // Cette fonction simule une base de données
    private function getMemosData()
    {
        return [
            (object)[
                'id' => 1,
                'from' => 'Jean Dupont',
                'author' => 'Alice Martin',
                'subject' => 'Rapport Mensuel Q3',
                'date' => '2024-07-26',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam scelerisque leo consequat, auctor elit ac, tristique felis. In hac habitasse platea dictumst.'
            ],
            (object)[
                'id' => 2,
                'from' => 'Service RH',
                'author' => 'Bob Lebrun',
                'subject' => 'Nouvelles directives de télétravail',
                'date' => '2024-07-25',
                'content' => 'Veuillez prendre connaissance des dernières mises à jour concernant la politique de télétravail. Disponible sur l\'intranet.'
            ],
            (object)[
                'id' => 3,
                'from' => 'Direction Générale',
                'author' => 'Carole Dubois',
                'subject' => 'Assemblée Générale Annuelle',
                'date' => '2024-07-24',
                'content' => 'L\'Assemblée Générale Annuelle se tiendra le 15 août. Tous les actionnaires sont invités à participer.'
            ],
            // Ajoutez d'autres mémos si nécessaire
        ];
    }

    public function render()
    {
        return view('livewire.memos.details.show-memo');
    }
}