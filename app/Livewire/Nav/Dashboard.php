<?php

namespace App\Livewire\Nav;

use App\Models\Memo;
use Livewire\Component;
use App\Models\Historiques;
use Livewire\Attributes\Rule;
use Livewire\WithPagination; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Dashboard extends Component
{
    use WithPagination; 

    // --- Propriétés de filtrage et UI ---
    public $chartPeriod = '7_jours'; 
    public $isOpen = false; 
    public $memoId = null;

    // --- Règles de validation ---
    #[Rule('required')]
    public string $object = '';

    #[Rule('required')]
    public string $concern = '';

    #[Rule('required')]
    public string $type_memo = '';

    #[Rule('required')]
    public string $content = '';

    /**
     * Ouvre le modal de création/édition
     */
    public function openModal()
    {
        $this->resetValidation();
        $this->isOpen = true;
    }

    /**
     * Ferme le modal et réinitialise les champs pour libérer de la mémoire
     */
    public function closeModal()
    {
        $this->isOpen = false;
        // Reset ciblé pour ne pas perdre l'état du graphique ou de la pagination
        $this->reset(['object', 'content', 'concern', 'memoId', 'type_memo']);
    }

    /**
     * Sauvegarde ou mise à jour d'un brouillon
     */
    public function save()
    {
        $this->validate();

        Memo::updateOrCreate(
            ['id' => $this->memoId],
            [
                'reference' => '123/DGG/GHD/DSHG', // Référence statique selon ton code original
                'object'    => $this->object,
                'concern'   => $this->concern,
                'content'   => $this->content,
                'user_id'   => Auth::id()
            ]
        );

        $action = $this->memoId ? 'modifié' : 'créé';
        $this->closeModal();
        $this->dispatch('notify', message: "Brouillon $action avec succès !");
    }

    // =========================================================
    // GESTION DES NOTIFICATIONS
    // =========================================================

    /**
     * Marque une notification comme lue et redirige l'utilisateur
     */
    public function markNotificationAsRead($notificationId)
    {
        $notification = Auth::user()->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
            
            // Redirection vers le lien contenu dans la notification
            if (!empty($notification->data['link']) && $notification->data['link'] !== '#') {
                return redirect($notification->data['link']);
            }
        }
    }

    /**
     * Marque toutes les notifications de l'utilisateur comme lues
     */
    public function markAllNotificationsAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    // =========================================================
    // RENDU ET LOGIQUE DE PERFORMANCE
    // =========================================================

    public function render()
    {
        $userId = Auth::id();
        $user = Auth::user();

        // --- OPTIMISATION : Compteurs ---
        // On récupère les comptes en minimisant la charge sur les colonnes JSON
        $toValidateCount_dir = Memo::whereJsonContains('current_holders', $userId)->whereNull('signature_dir')->count();
        $toValidateCount_sd  = Memo::whereJsonContains('current_holders', $userId)->whereNull('signature_sd')->count();
        $totalMemosSortants  = Memo::whereJsonContains('current_holders', $userId)->where('workflow_direction', "sortant")->count();
        $totalMemosEntrants  = Memo::whereJsonContains('current_holders', $userId)->where('workflow_direction', "entrant")->count();  
        $favoritesCount      = $user->favorites()->count();

        // --- OPTIMISATION DU GRAPHIQUE (Réduction drastique des délais SQL) ---
        $categories = []; 
        $dataMemo = []; 

        // Définition de la période
        $startDate = ($this->chartPeriod === '7_jours') 
            ? Carbon::now()->subDays(6)->startOfDay() 
            : Carbon::now()->startOfMonth()->startOfDay();

        // UNE SEULE REQUÊTE SQL pour récupérer toutes les données de la période
        // Au lieu de 7 ou 31 requêtes séparées
        $memoStats = Memo::where('user_id', $userId)
            ->where('workflow_direction', 'sortant')
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as aggregate'))
            ->groupBy('date')
            ->pluck('aggregate', 'date');

        // Construction des tableaux pour le JS (ApexCharts)
        if ($this->chartPeriod === '7_jours') {
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                $categories[] = Carbon::parse($date)->format('d/m');
                $dataMemo[] = $memoStats[$date] ?? 0;
            }
        } else {
            $current = Carbon::now()->startOfMonth();
            $end = Carbon::now();
            while ($current <= $end) {
                $dateString = $current->format('Y-m-d');
                $categories[] = $current->format('d');
                $dataMemo[] = $memoStats[$dateString] ?? 0;
                $current->addDay();
            }
        }

        // Notification au frontend pour mettre à jour le graphique
        $this->dispatch('update-chart', categories: $categories, series: $dataMemo);

        // Récupération des mouvements récents (Eager loading pour éviter le N+1 sur 'memo')
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
            'chartCategories'     => $categories,
            'chartSortants'       => $dataMemo,
            'recentMovements'     => $recentMovements 
        ]);
    }
}