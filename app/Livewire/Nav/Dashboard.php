<?php

namespace App\Livewire\Nav;

use App\Models\Memo;
use App\Models\Historiques;
use Livewire\Component;
use Livewire\WithPagination; 
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Dashboard extends Component
{
    use WithPagination; 

    public $chartPeriod = '7_jours'; 
    public $darkMode = false;

    public function mount()
    {
        $this->darkMode = session()->get('dark_mode', false);
    }

    /**
     * Écoute le changement de mode sombre depuis la Sidebar
     */
    #[On('dark-mode-toggled')]
    public function updateDarkMode($darkMode)
    {
        $this->darkMode = $darkMode;
    }

    public function updatedChartPeriod()
    {
        $data = $this->getChartData();
        $this->dispatch('update-chart', 
            categories: $data['categories'], 
            series: $data['series']
        );
    }

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

    public function markAllNotificationsAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    private function getChartData()
    {
        $userId = Auth::id();
        $categories = []; 
        $series = []; 

        $startDate = ($this->chartPeriod === '7_jours') 
            ? Carbon::now()->subDays(6)->startOfDay() 
            : Carbon::now()->startOfMonth()->startOfDay();

        $memoStats = Memo::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as aggregate'))
            ->groupBy('date')
            ->pluck('aggregate', 'date');

        if ($this->chartPeriod === '7_jours') {
            for ($i = 6; $i >= 0; $i--) {
                $dateObj = Carbon::now()->subDays($i);
                $dateKey = $dateObj->format('Y-m-d');
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
        
        $totalMemosSortants  = Memo::whereJsonContains('current_holders', $userId)->where('workflow_direction', "sortant")->count();
        $totalMemosEntrants  = Memo::whereJsonContains('current_holders', $userId)->where('workflow_direction', "entrant")->count();  
        $favoritesCount = $user->favorites ? $user->favorites()->count() : 0;
        $chartData = $this->getChartData();

        $recentMovements = Historiques::with('memo')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('livewire.nav.dashboard', [
            'memosEntrantsCount'  => $totalMemosEntrants,
            'memosSortantsCount'  => $totalMemosSortants,
            'favoritesCount'      => $favoritesCount,
            'chartCategories'     => $chartData['categories'],
            'chartSortants'       => $chartData['series'],
            'recentMovements'     => $recentMovements 
        ]);
    }
}