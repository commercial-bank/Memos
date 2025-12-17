<?php

namespace App\Http\Controllers;

use App\Models\WrittenMemo;
use Illuminate\Http\Request;

class MemoVerificationController extends Controller
{
    public function verify($token)
    {
        // 1. Récupération du mémo avec les historiques et les utilisateurs associés
        $memo = WrittenMemo::with(['historiques.user'])
                ->where('signature_sd', $token)
                ->orWhere('signature_dir', $token)
                ->first();

        // 2. SÉCURITÉ
        if (!$memo) {
            abort(404, 'Document introuvable ou signature invalide.');
        }

        // 3. IDENTIFICATION DU SIGNATAIRE PRINCIPAL (celui du QR Code)
        $signerRole = '';
        $signerDate = null;

        if ($memo->signature_sd === $token) {
            $signerRole = 'Sous-Directeur';
            $signerDate = $memo->updated_at; 
        } elseif ($memo->signature_dir === $token) {
            $signerRole = 'Directeur';
            $signerDate = $memo->updated_at;
        }

        // 4. PRÉPARATION DES LISTES (FILTRAGE)
        // On récupère tout l'historique
        $allHistory = $memo->historiques;

        // A. Les Signatures (finales)
        $signatures = $allHistory->filter(function ($item) {
            return str_contains(strtoupper($item->visa), 'SIGN');
        });

        // B. Les Visas (UNIQUEMENT CEUX CONTENANT "ACCORD")
        $visas = $allHistory->filter(function ($item) {
            // On ne garde que si le mot "ACCORD" est présent
            return str_contains(strtoupper($item->visa), 'ACCORD');
        });

        // 5. ENVOI A LA VUE
        return view('public.memo-verification', [
            'memo' => $memo,
            'signerRole' => $signerRole,
            'signerDate' => $signerDate,
            'signatures' => $signatures, // Variable requise par la vue
            'visas' => $visas            // Variable filtrée (seulement ACCORD)
        ]);
    }
}