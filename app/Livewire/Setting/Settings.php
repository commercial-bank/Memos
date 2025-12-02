<?php

namespace App\Livewire\Setting;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Settings extends Component
{
    use WithPagination;

   public $search = '';

    // Réinitialiser la pagination quand on cherche
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Basculer le statut Admin
    public function toggleAdmin($userId)
    {
        $user = User::findOrFail($userId);
        // Empêcher de modifier ses propres droits pour ne pas s'exclure
        if ($user->id !== auth()->id()) {
            $user->update(['is_admin' => !$user->is_admin]);
        }
    }

    // Basculer le statut Actif/Inactif
    public function toggleStatus($userId)
    {
        $user = User::findOrFail($userId);
        
        // Sécurité : on ne peut pas désactiver son propre compte
        if ($user->id !== auth()->id()) {
            $user->update(['is_active' => !$user->is_active]);
        }
    }

    public function render()
    {
        $users = User::query()
            ->where('first_name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('livewire.setting.settings', [
            'users' => $users
        ]);
    }
}
