<?php

namespace App\Livewire\Nav;

use App\Models\Memo;
use Livewire\Component;
use App\Models\Historiques;
use Livewire\Attributes\Rule;
use Livewire\WithPagination; 
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // N'oublie pas d'importer Carbon

class Dashboard extends Component
{
     use WithPagination; 
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

    // --- GESTION DES NOTIFICATIONS ---

    public function markNotificationAsRead($notificationId)
    {
        $notification = Auth::user()->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
            
            // --- CORRECTION : ON REDIRIGE VERS LE MÉMO ---
            // On vérifie si un lien existe dans la notif avant de rediriger
            if (!empty($notification->data['link']) && $notification->data['link'] !== '#') {
                return redirect($notification->data['link']);
            }
        }
    }

    public function markAllNotificationsAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        // On rafraichit juste le composant, pas besoin de message toast ici souvent
    }


public function render()
{
    $userId = Auth::id();
    
    // ... Vos compteurs existants ...
    $toValidateCount = Memo::whereJsonContains('current_holders', $userId)->whereNull('signature_dir')->count();
    $toValidateCount2 = Memo::whereJsonContains('current_holders', $userId)->whereNull('signature_sd')->count();
    $totalMemos = Memo::whereJsonContains('current_holders', $userId)->where('workflow_direction', "sortant")->count();
    $totalMemos2 = Memo::whereJsonContains('current_holders', $userId)->where('workflow_direction', "entrant")->count();  
    
    // --- NOUVEAU : COMPTEUR FAVORIS ---
    // On utilise la relation favorites() définie dans le modèle User
    $favoritesCount = Auth::user()->favorites()->count();

    // ... (Le reste de votre logique graphique reste inchangée) ...
    $categories = []; 
    $dataMemo = []; 

    if ($this->chartPeriod === '7_jours') {
        // ... (votre boucle)
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $categories[] = $date->format('d/m');
            $dataMemo[] = Memo::where('user_id', $userId)
                ->where('workflow_direction', 'sortant')
                ->whereDate('created_at', $date)
                ->count();
        }
    } elseif ($this->chartPeriod === 'ce_mois') {
        // ... (votre boucle)
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now();
        while ($start <= $end) {
            $categories[] = $start->format('d');
            $dataMemo[] = Memo::where('user_id', $userId)
                ->where('workflow_direction', 'sortant')
                ->whereDate('created_at', $start)
                ->count();
            $start->addDay();
        }
    }

    $this->dispatch('update-chart', categories: $categories, series: $dataMemo);

    $recentMovements = Historiques::with('memo')
        ->where('user_id', $userId)
        ->orderBy('created_at', 'desc')
        ->paginate(5);

    return view('livewire.nav.dashboard', [
        'toValidateCount_dir' => $toValidateCount,
        'toValidateCount_sd' =>  $toValidateCount2,
        'memosEntrantsCount' =>  $totalMemos2,
        'memosSortantsCount' =>  $totalMemos,
        'favoritesCount'     =>  $favoritesCount, // <--- AJOUTEZ CETTE LIGNE
        'chartCategories'    => $categories,
        'chartSortants'      => $dataMemo,
        'recentMovements'    => $recentMovements 
    ]);
}
    


}