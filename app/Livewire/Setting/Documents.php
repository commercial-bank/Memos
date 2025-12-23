<?php

namespace App\Livewire\Setting;

use Carbon\Carbon;
use App\Models\Memo;
use Livewire\Component;
use App\Models\Historiques; 
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Documents extends Component
{
    use WithPagination;

    // --- Propriétés de filtrage ---
    public $selectedYear;
    public $selectedMonth;
    public $search = '';
    public $activeTab = 'created'; // 'created' ou 'signed'

    /**
     * Initialisation avec les dates actuelles
     */
    public function mount()
    {
        $this->selectedYear = Carbon::now()->year;
        $this->selectedMonth = Carbon::now()->month;
    }

    /**
     * Réinitialise la pagination lors d'une recherche (Optimisation Livewire)
     */
    public function updatingSearch() 
    { 
        $this->resetPage(); 
    }

    /**
     * Réinitialise la pagination lors du changement d'onglet
     */
    public function updatingActiveTab() 
    { 
        $this->resetPage(); 
    }

    /**
     * Rendu du composant avec logique de requête optimisée
     */
    public function render()
    {
        $user = Auth::user();

        // 1. Construction de la requête de base
        $query = Memo::query()
            ->whereYear('created_at', $this->selectedYear)
            ->whereMonth('created_at', $this->selectedMonth)
            // Eager loading pour éviter les requêtes N+1
            ->with(['replies.user.entity', 'historiques', 'destinataires.entity'])
            ->withCount('replies')
            // Recherche conditionnelle optimisée
            ->when($this->search, function($q) {
                $searchTerm = '%' . $this->search . '%';
                $q->where(function($sub) use ($searchTerm) {
                    $sub->where('object', 'like', $searchTerm)
                        ->orWhere('reference', 'like', $searchTerm);
                });
            });

        // 2. Logique d'onglet
        if ($this->activeTab === 'created') {
            // Mémos créés par l'utilisateur
            $query->where('user_id', $user->id);
        } else {
            // Mémos visés/signés (historique existant) sans être l'auteur
            $query->where('user_id', '!=', $user->id)
                  ->whereHas('historiques', function($q) use ($user) {
                      $q->where('user_id', $user->id);
                  });
        }

        // Exécution de la requête avec tri décroissant
        $memos = $query->latest()->paginate(10);

        // 3. Calcul des Statistiques (Optimisées par index)
        $stats = [
            'envoyes' => Memo::where('user_id', $user->id)
                ->whereYear('created_at', $this->selectedYear)
                ->whereMonth('created_at', $this->selectedMonth)
                ->count(),
                
            'reponses_recues' => Memo::whereHas('parent', function($p) use ($user) {
                    $p->where('user_id', $user->id);
                })->count(),
                
            'vises' => Historiques::where('user_id', $user->id)
                ->whereYear('created_at', $this->selectedYear)
                ->whereMonth('created_at', $this->selectedMonth)
                ->count(),
        ];

        return view('livewire.setting.documents', [
            'memos' => $memos,
            'stats' => $stats,
            // Génération de la plage d'années dynamique
            'years' => range(Carbon::now()->year, Carbon::now()->year - 2),
            'months' => [
                1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin',
                7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
            ]
        ]);
    }
}