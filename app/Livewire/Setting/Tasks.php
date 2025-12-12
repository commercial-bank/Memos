<?php

namespace App\Livewire\Setting;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Task; // IMPORTANT : Ton modèle Tâche
use App\Models\Memo; // IMPORTANT : Ton modèle Mémo

class Tasks extends Component
{
    // Variables pour le formulaire de création
    public $title = '';
    public $priority = 'normal';

    // Règles de validation
    protected $rules = [
        'title' => 'required|min:3',
        'priority' => 'in:low,normal,urgent',
    ];

    /**
     * Cette fonction calcule les données du tableau à chaque rafraîchissement.
     * C'est ici qu'on mélange Tâches Perso et Mémos.
     */
    public function getBoardProperty()
    {
        $userId = Auth::id();

        // 1. Récupérer les tâches manuelles de l'utilisateur
        $myTasks = Task::where('user_id', $userId)->get();

        // 2. Récupérer les mémos où l'utilisateur est "current_holder"
        $myMemos = Memo::where('status', '!=', 'archived')->get()->filter(function($memo) use ($userId) {
            // Gestion du JSON (parfois stocké en string, parfois en array selon la DB)
            $holders = is_string($memo->current_holders) 
                ? json_decode($memo->current_holders, true) 
                : $memo->current_holders;
            
            return is_array($holders) && in_array($userId, $holders);
        });

        // 3. Organiser les colonnes
        return [
            // Colonne "À faire" : Juste les tâches perso
            'todo' => $myTasks->where('status', 'todo'),

            // Colonne "En cours" : Tâches perso + Les Mémos (transformés en tâches virtuelles)
            'in_progress' => $myTasks->where('status', 'in_progress')->concat(
                $myMemos->map(function($memo) {
                    return (object) [
                        'id' => 'memo_' . $memo->id, // ID spécial pour les reconnaître
                        'title' => $memo->object,
                        'priority' => 'urgent', // Un mémo est toujours prioritaire
                        'status' => 'in_progress',
                        'is_memo' => true, // Flag pour l'affichage
                        'created_at' => $memo->created_at,
                        'reference' => $memo->reference
                    ];
                })
            ),

            // Colonne "Terminé"
            'done' => $myTasks->where('status', 'done'),
        ];
    }

    // Créer une tâche
    public function saveTask()
    {
        $this->validate();

        Task::create([
            'user_id' => Auth::id(),
            'title' => $this->title,
            'priority' => $this->priority,
            'status' => 'todo'
        ]);

        $this->reset(['title', 'priority']); // Vider le formulaire
    }

    // Changer le statut (Drag & Drop)
    public function updateStatus($id, $newStatus)
    {
        // On interdit de bouger un mémo via le Kanban (il a son propre workflow)
        if (str_starts_with($id, 'memo_')) {
            return; 
        }

        $task = Task::find($id);
        if ($task && $task->user_id == Auth::id()) {
            $task->update(['status' => $newStatus]);
        }
    }

    // Supprimer une tâche
    public function deleteTask($id)
    {
        Task::find($id)?->delete();
    }

    public function render()
    {
        // On passe la variable calculée $this->board à la vue
        return view('livewire.setting.tasks', [
            'board' => $this->board
        ]);
    }
}