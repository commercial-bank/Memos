<?php

namespace App\Livewire\Nav;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Memo;
use Carbon\Carbon; // N'oublie pas d'importer Carbon

class Dashboard extends Component
{
     // Valeur par défaut du menu déroulant
    public $chartPeriod = '7_jours'; 

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

    public function save()
    {
        

        Memo::updateOrCreate(
            ['id' => $this->memoId],
            [
                'reference' => '123/DGG/GHD/DSHG',
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



    public function render()
    {
        $userId = Auth::id();
        
        // --- PARTIE COMPTEURS (Vos logiques existantes) ---
        // 1. À valider (je suis holder + pas de signature DIR)
        $toValidateCount = Memo::whereJsonContains('current_holders', $userId)
                        ->whereNull('signature_dir')
                        ->count();

        // 2. À valider (je suis holder + pas de signature SD)
        $toValidateCount2 = Memo::whereJsonContains('current_holders', $userId)
                        ->whereNull('signature_sd')
                        ->count();

        // 3. Total Sortants (je suis holder + direction sortant)
        $totalMemos = Memo::whereJsonContains('current_holders', $userId)
                        ->where('workflow_direction', "sortant")
                        ->count();

        // 4. Total Entrants (je suis holder + direction entrant)
        $totalMemos2 = Memo::whereJsonContains('current_holders', $userId)
                        ->where('workflow_direction', "entrant")
                        ->count();  
                        

        // --- PARTIE GRAPHIQUE ---
        $categories = []; 
        $dataMemo = []; // Correction : on initialise bien $dataMemo

        if ($this->chartPeriod === '7_jours') {
            // Boucle sur les 7 derniers jours
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $categories[] = $date->format('d/m');
                
                // Correction ici : $dataMemo et non $dataMemos
                $dataMemo[] = Memo::where('user_id', $userId)
                    ->where('workflow_direction', 'sortant')
                    ->whereDate('created_at', $date)
                    ->count();
            }
        } elseif ($this->chartPeriod === 'ce_mois') {
            // Boucle du 1er au jour actuel
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now();
            
            while ($start <= $end) {
                $categories[] = $start->format('d');
                
                // Variable correcte
                $dataMemo[] = Memo::where('user_id', $userId)
                    ->where('workflow_direction', 'sortant')
                    ->whereDate('created_at', $start)
                    ->count();

                $start->addDay();
            }
        }

        // --- IMPORTANT : ENVOI DES DONNÉES AU JS ---
        // On envoie un événement 'update-chart' avec les nouvelles données
        $this->dispatch('update-chart', 
            categories: $categories, 
            series: $dataMemo
        );

        return view('livewire.nav.dashboard', [
            'toValidateCount_dir' => $toValidateCount,
            'toValidateCount_sd' =>  $toValidateCount2,
            'memosEntrantsCount' =>  $totalMemos2,
            'memosSortantsCount' =>  $totalMemos,
            // On passe aussi les données initiales pour le premier chargement
            'chartCategories' => $categories,
            'chartSortants' => $dataMemo,
        ]);
    }
}