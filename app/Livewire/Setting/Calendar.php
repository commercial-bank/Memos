<?php

namespace App\Livewire\Setting;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Memo;
use App\Models\User;
use Illuminate\Support\Str;


class Calendar extends Component
{
    public $currentYear;
    public $currentMonth;
    
    // Filtres adaptés à votre métier
    public $filters = [
        'interims' => true,      // Table replaces_users
        'memos' => true,         // Table memos (création)
        'courriers' => true,     // Table blocs_enregistrements
    ];

    public function mount()
    {
        $this->currentYear = now()->year;
        $this->currentMonth = now()->month;
    }

    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentYear = $date->year;
        $this->currentMonth = $date->month;
    }

    public function prevMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentYear = $date->year;
        $this->currentMonth = $date->month;
    }

    public function goToToday()
    {
        $this->currentYear = now()->year;
        $this->currentMonth = now()->month;
    }

    public function getCalendarDataProperty()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1);
        $startOfCalendar = $date->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $endOfCalendar = $date->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        // 1. Récupération des données réelles
        $realEvents = $this->fetchDatabaseEvents($startOfCalendar, $endOfCalendar);

        $days = [];
        $curr = $startOfCalendar->copy();

        while ($curr <= $endOfCalendar) {
            $dayKey = $curr->format('Y-m-d');
            
            // Filtrage des événements pour ce jour
            $dayEvents = $realEvents->filter(function($event) use ($dayKey) {
                // Gestion spécifique pour les périodes (Intérims)
                if ($event['is_period']) {
                    return $dayKey >= $event['start'] && $dayKey <= $event['end'];
                }
                // Gestion pour les dates ponctuelles
                return $event['date'] === $dayKey;
            })->filter(function($event) {
                // Application des filtres checkbox
                if ($event['type'] === 'interim' && !$this->filters['interims']) return false;
                if ($event['type'] === 'memo' && !$this->filters['memos']) return false;
                if ($event['type'] === 'courrier' && !$this->filters['courriers']) return false;
                return true;
            });

            $days[] = [
                'date' => $curr->format('Y-m-d'),
                'day' => $curr->day,
                'isCurrentMonth' => $curr->month === $this->currentMonth,
                'isToday' => $curr->isToday(),
                'events' => $dayEvents->values()->all() // Reset keys
            ];

            $curr->addDay();
        }

        return [
            'monthName' => $date->translatedFormat('F'),
            'year' => $date->year,
            'days' => $days
        ];
    }

    /**
     * C'est ici que la magie opère : connexion aux schémas DB
     */
    private function fetchDatabaseEvents($start, $end)
    {
        $events = collect();
        
        $startStr = $start->format('Y-m-d');
        $endStr = $end->format('Y-m-d');

        // A. RÉCUPÉRER LES INTÉRIMES (Table replaces_users)
        // Note: Vos dates sont en string dans le schéma, on assume le format Y-m-d
        $replacements = DB::table('replaces_users')
            ->join('users as titulaire', 'replaces_users.user_id', '=', 'titulaire.id')
            ->join('users as remplacant', 'replaces_users.user_id_replace', '=', 'remplacant.id')
            ->where(function($query) use ($startStr, $endStr) {
                 $query->where('date_end_replace', '>=', $startStr)
                       ->where('date_begin_replace', '<=', $endStr);
            })
            ->select(
                'replaces_users.*', 
                'titulaire.last_name as t_name', 
                'remplacant.last_name as r_name'
            )
            ->get();

        foreach($replacements as $rep) {
            $events->push([
                'id' => 'rep_'.$rep->id,
                'title' => "Intérim: {$rep->r_name} remplace {$rep->t_name}",
                'type' => 'interim',
                'is_period' => true,
                'start' => substr($rep->date_begin_replace, 0, 10), // Sécurité si format datetime
                'end' => substr($rep->date_end_replace, 0, 10),
                'date' => null, // Non utilisé pour une période
                'time' => 'Tout le jour'
            ]);
        }

        // B. RÉCUPÉRER LES MÉMOS CRÉÉS (Table memos)
        $memos = DB::table('memos')
            ->join('users', 'memos.user_id', '=', 'users.id')
            ->whereBetween('memos.created_at', [$start, $end])
            ->select('memos.id', 'memos.object', 'memos.created_at', 'users.last_name')
            ->get();

        foreach($memos as $memo) {
            $date = Carbon::parse($memo->created_at);
            $events->push([
                'id' => 'memo_'.$memo->id,
                'title' => "Mémo: " .  Str::limit($memo->object, 20),
                'type' => 'memo',
                'is_period' => false,
                'date' => $date->format('Y-m-d'),
                'time' => $date->format('H:i'),
                'author' => $memo->last_name
            ]);
        }

        // C. RÉCUPÉRER LES COURRIERS ENREGISTRÉS (Table blocs_enregistrements)
        // Attention : votre schéma a date_enreg en string
        $courriers = DB::table('blocs_enregistrements')
             ->select('reference', 'nature_memo', 'date_enreg')
             ->get()
             ->filter(function($c) use ($startStr, $endStr) {
                 // Filtrage PHP car date en string peut être risqué en SQL direct selon format
                 $d = substr($c->date_enreg, 0, 10); 
                 return $d >= $startStr && $d <= $endStr;
             });

        foreach($courriers as $courrier) {
            $events->push([
                'id' => 'courrier_'.$courrier->reference,
                'title' => "Enreg: {$courrier->reference}",
                'type' => 'courrier',
                'is_period' => false,
                'date' => substr($courrier->date_enreg, 0, 10),
                'time' => 'N/A'
            ]);
        }

        return $events;
    }

    public function render()
    {
        return view('livewire.setting.calendar', [
            'data' => $this->calendarData
        ]);
    }
}