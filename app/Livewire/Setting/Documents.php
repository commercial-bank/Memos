<?php

namespace App\Livewire\Setting;

use Carbon\Carbon;
use App\Models\Memo;
use Livewire\Component;
// use App\Models\Historique; // Attention, vérifiez si votre modèle est singulier ou pluriel
use App\Models\Historiques; // Je garde votre nommage actuel
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Documents extends Component
{
    use WithPagination;

    public $selectedYear;
    public $selectedMonth;
    public $search = '';

    public function mount()
    {
        $this->selectedYear = Carbon::now()->year;
        $this->selectedMonth = Carbon::now()->month;
    }

    public function render()
    {
        $user = Auth::user();

        // 1. REQUÊTE PRINCIPALE (Mise à jour)
        // On veut : Les mémos où j'ai une trace dans l'historique OU les mémos que j'ai créés
        $query = Memo::query()
            ->where(function($q) use ($user) {
                // Cas 1 : J'ai fait une action (Visa/Signature) ce mois-ci
                $q->whereHas('historiques', function ($h) use ($user) {
                    $h->where('user_id', $user->id)
                      ->whereYear('created_at', $this->selectedYear)
                      ->whereMonth('created_at', $this->selectedMonth);
                })
                // Cas 2 : C'est moi qui l'ai créé (Envoyé) ce mois-ci
                ->orWhere(function($c) use ($user) {
                    $c->where('user_id', $user->id)
                      ->whereYear('created_at', $this->selectedYear)
                      ->whereMonth('created_at', $this->selectedMonth);
                });
            })
            // Eager loading
            ->with(['historiques' => function($q) use ($user) {
                $q->where('user_id', $user->id);
            }]);

        if (!empty($this->search)) {
            $query->where(function($q) {
                 $q->where('object', 'like', '%' . $this->search . '%')
                   ->orWhere('reference', 'like', '%' . $this->search . '%');
            });
        }

        $memos = $query->latest('updated_at')->paginate(10);

        // 2. STATISTIQUES
        $stats = [
            // Mémos créés par moi ce mois-ci
            'envoyes' => Memo::where('user_id', $user->id)
                ->whereYear('created_at', $this->selectedYear)
                ->whereMonth('created_at', $this->selectedMonth)
                ->count(),

            // Mémos visés (Actions intermédiaires)
            'vises' => Historiques::where('user_id', $user->id)
                ->whereYear('created_at', $this->selectedYear)
                ->whereMonth('created_at', $this->selectedMonth)
                // J'ai retiré 'envoyer' ici car il est maintenant compté dans 'envoyes' via le user_id
                // Mais vous pouvez le laisser si c'est une action spécifique de workflow
                ->whereIn('visa', ['Vu & Accord', 'Vu & Pas Accord', 'Vu', 'transmis', 'coter']) 
                ->count(),

            // Mémos Signés (Actions finales)
            'signes' => Historiques::where('user_id', $user->id)
                ->whereYear('created_at', $this->selectedYear)
                ->whereMonth('created_at', $this->selectedMonth)
                ->where('visa', 'Signé')
                ->count(),
        ];

        return view('livewire.setting.documents', [
            'memos' => $memos,
            'stats' => $stats,
            'years' => range(Carbon::now()->year, Carbon::now()->year - 2),
            'months' => [
                1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 
                5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 
                9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
            ]
        ]);
    }
}