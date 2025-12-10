<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use App\Models\Historiques;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VerificationController extends Controller
{
    public function verify($token)
    {
        // 1. Récupération du mémo
        $memo = Memo::where('qr_code', $token)->firstOrFail();

        // 2. Récupération de l'historique avec les users
        $historiques = Historiques::with('user')
            ->where('memo_id', $memo->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. FILTRE SIGNATURES (Les décideurs finaux)
        $signatures = $historiques->filter(function ($h) {
            // On cherche les mots clés de signature hiérarchique
            return Str::contains(strtoupper($h->visa), ['SIGN', 'VALIDER', 'DIRECTEUR', 'SD', 'APPROUVÉ']);
        });

        // 4. FILTRE VISAS (Liste stricte demandée)
        // Mots-clés autorisés pour l'affichage
        $allowedVisas = [
            'VU & ACCORD', 
            'REJETER', 
            'REJET', 
            'ENREGISTRÉ', 
            'ENREGISTRE', // Au cas où sans accent
            'TRANSMIT', 
            'TRANSMIS'
        ];

        $visas = $historiques->filter(function ($h) use ($allowedVisas, $signatures) {
            $action = strtoupper($h->visa);
            
            // Vérifie si l'action contient un des mots autorisés
            $isAllowed = Str::contains($action, $allowedVisas);

            // S'assure que ce n'est pas déjà affiché dans les signatures (pour éviter les doublons)
            $isNotSignature = !$signatures->contains('id', $h->id);

            return $isAllowed && $isNotSignature;
        });

        return view('public.verification', compact('memo', 'signatures', 'visas'));
    }
}