<?php

namespace App\Livewire\Setting;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Profil extends Component
{
    // On déclare une variable pour l'utilisateur complet (pour l'affichage en lecture seule)
    public $user;

    // On déclare des variables spécifiques pour les champs modifiables
    public $poste;
    public $entity;
    public $entity_sigle;
    public $service;
    public $n1;

    public function mount()
    {
        // On récupère l'utilisateur connecté
        $this->user = Auth::user();

        // On initialise les champs modifiables avec les valeurs actuelles
        $this->poste = $this->user->poste;
        $this->entity = $this->user->entity;
        $this->entity_sigle = $this->user->entity_sigle;
        $this->service = $this->user->service;
        $this->n1 = $this->user->n1;
    }

    public function save()
    {
        // Validation des données
        $this->validate([
            'poste' => 'nullable|string|max:255',
            'entity' => 'nullable|string|max:255',
            'entity_sigle' => 'nullable|string|max:255', // J'ai laissé modifiable ici, mais tu peux le bloquer si besoin
            'service' => 'nullable|string|max:255',
            'n1' => 'nullable|string|max:255',
        ]);

        // Mise à jour dans la base de données
        $this->user->update([
            'poste' => $this->poste,
            'entity' => $this->entity,
            'entity_sigle' => $this->entity_sigle,
            'service' => $this->service,
            'n1' => $this->n1,
        ]);

        // Message de succès (Flash message)
         $this->dispatch('notify', message: "Profil mise a jour avec succès !");
    
    }

    public function render()
    {
        return view('livewire.setting.profil');
    }
}