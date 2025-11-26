<?php

namespace App\Livewire\Memos;

use Livewire\Component;
use App\Models\WrittenMemo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class MemoValidation extends Component
{

    public WrittenMemo $memo;

    // Fonction pour signer en tant que Sous-Directeur
    public function signAsSD()
    {
        // Vérification des droits (à adapter selon ta logique de rôles)
        if (Auth::user()->poste !== 'Sous-Directeur') {
            abort(403);
        }

        // 1. Générer un token unique sécurisé
        $token = Str::random(64); 

        // 2. Mettre à jour la table written_memos
        $this->memo->update([
            'signature_sd' => $token,
        ]);
        
        $this->dispatch('notify',message:'Document Signer par Vous'. Auth::user()->last_name ); // Notification front
    }

    // Fonction pour signer en tant que Directeur
    public function signAsDir()
    {
        if (Auth::user()->poste !== 'Directeur') {
            abort(403);
        }

        $token = Str::random(64);

        $this->memo->update([
            'signature_dir' => $token,
        ]);
        
         $this->dispatch('notify',message:'Document Signer par Vous'. Auth::user()->last_name ); // Notification front
    }

    public function render()
    {
        return view('livewire.memos.memo-validation');
    }
}
