<?php

namespace App\Livewire\Nav;

use App\Models\Memo;
use App\Models\Historiques;
use Livewire\Component;
use Livewire\WithPagination; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Dashboard extends Component
{
    use WithPagination; 

    // --- Propriétés de filtrage ---
    public $chartPeriod = '7_jours'; 

    /**
     * Cette méthode est appelée automatiquement par Livewire 
     * dès que la propriété $chartPeriod est modifiée (via wire:model.live)
     */
    public function updatedChartPeriod()
    {
        // On récupère les données fraîches pour le graphique
        $data = $this->getChartData();
        
        // On informe le JavaScript de mettre à jour le graphique ApexCharts
        $this->dispatch('update-chart', 
            categories: $data['categories'], 
            series: $data['series']
        );
    }

    /**
     * Marque une notification comme lue
     */
    public function markNotificationAsRead($notificationId)
    {
        $notification = Auth::user()->notifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
            if (!empty($notification->data['link']) && $notification->data['link'] !== '#') {
                return redirect($notification->data['link']);
            }
        }
    }

    /**
     * Tout marquer comme lu
     */
    public function markAllNotificationsAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    /**
     * Logique centrale de calcul des données du graphique
     */
    private function getChartData()
    {
        $userId = Auth::id();
        $categories = []; 
        $series = []; 

        // Définition de la date de début
        $startDate = ($this->chartPeriod === '7_jours') 
            ? Carbon::now()->subDays(6)->startOfDay() 
            : Carbon::now()->startOfMonth()->startOfDay();

        // REQUÊTE SQL : On compte tous les mémos CRÉÉS par l'utilisateur (indépendamment du statut)
        $memoStats = Memo::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as aggregate'))
            ->groupBy('date')
            ->pluck('aggregate', 'date');

        // Construction des tableaux Categories (X) et Series (Y)
        if ($this->chartPeriod === '7_jours') {
            for ($i = 6; $i >= 0; $i--) {
                $dateObj = Carbon::now()->subDays($i);
                $dateKey = $dateObj->format('Y-m-d'); // Clé pour matcher le Pluck SQL
                
                $categories[] = $dateObj->translatedFormat('d/m');
                $series[] = $memoStats[$dateKey] ?? 0;
            }
        } else {
            $current = Carbon::now()->startOfMonth();
            $end = Carbon::now();
            while ($current <= $end) {
                $dateKey = $current->format('Y-m-d');
                $categories[] = $current->format('d');
                $series[] = $memoStats[$dateKey] ?? 0;
                $current->addDay();
            }
        }

        return ['categories' => $categories, 'series' => $series];
    }

    public function render()
    {
        $userId = Auth::id();
        $user = Auth::user();

        // 1. Calcul des compteurs pour les cartes KPI
        // Dossiers où l'utilisateur est un détenteur actuel
        $toValidateCount_dir = Memo::whereJsonContains('current_holders', $userId)->whereNull('signature_dir')->count();
        $toValidateCount_sd  = Memo::whereJsonContains('current_holders', $userId)->whereNull('signature_sd')->count();
        
        // Flux entrants / sortants
        $totalMemosSortants  = Memo::whereJsonContains('current_holders', $userId)->where('workflow_direction', "sortant")->count();
        $totalMemosEntrants  = Memo::whereJsonContains('current_holders', $userId)->where('workflow_direction', "entrant")->count();  
        
        // Favoris
        $favoritesCount = $user->favorites ? $user->favorites()->count() : 0;

        // 2. Récupération des données initiales du graphique
        $chartData = $this->getChartData();

        // 3. Mouvements récents (Historique des actions de l'utilisateur)
        $recentMovements = Historiques::with('memo')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('livewire.nav.dashboard', [
            'toValidateCount_dir' => $toValidateCount_dir,
            'toValidateCount_sd'  => $toValidateCount_sd,
            'memosEntrantsCount'  => $totalMemosEntrants,
            'memosSortantsCount'  => $totalMemosSortants,
            'favoritesCount'      => $favoritesCount,
            'chartCategories'     => $chartData['categories'],
            'chartSortants'       => $chartData['series'],
            'recentMovements'     => $recentMovements 
        ]);
    }
}