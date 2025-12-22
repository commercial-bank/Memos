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

    $memos = Memo::query()
        ->where('user_id', $user->id)
        ->whereYear('created_at', $this->selectedYear)
        ->whereMonth('created_at', $this->selectedMonth)
        ->with(['replies.user.entity', 'historiques', 'destinataires.entity'])
        ->withCount('replies')
        ->where(function($q) {
            $q->where('object', 'like', '%' . $this->search . '%')
              ->orWhere('reference', 'like', '%' . $this->search . '%');
        })
        ->latest()
        ->paginate(10);

    // Stats
    $stats = [
        'envoyes' => Memo::where('user_id', $user->id)->whereYear('created_at', $this->selectedYear)->whereMonth('created_at', $this->selectedMonth)->count(),
        'reponses_recues' => Memo::whereHas('parent', fn($p) => $p->where('user_id', $user->id))->count(),
        'vises' => Historiques::where('user_id', $user->id)->whereYear('created_at', $this->selectedYear)->whereMonth('created_at', $this->selectedMonth)->count(),
    ];

    return view('livewire.setting.documents', [
        'memos' => $memos,
        'stats' => $stats,
        'years' => range(Carbon::now()->year, Carbon::now()->year - 2),
        'months' => [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin',
            7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ]
    ]);
}

}