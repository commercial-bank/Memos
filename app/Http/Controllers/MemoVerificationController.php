<?php

namespace App\Http\Controllers;

use App\Models\WrittenMemo;
use Illuminate\Http\Request;

class MemoVerificationController extends Controller
{
    public function verify($token)
    {
            $memo = WrittenMemo::where('signature_sd', $token)
                    ->orWhere('signature_dir', $token)
                    ->first();

        // 2. SÉCURITÉ : Si aucun mémo n'est trouvé, le QR est faux ou obsolète
        if (!$memo) {
            // On renvoie une page 404 ou une vue personnalisée "Document Invalide"
            abort(404, 'Document introuvable ou signature invalide.');
        }

        // 3. IDENTIFICATION : Qui a signé avec ce token précis ?
        // On compare le token reçu avec ceux en base
        $signerRole = '';
        $signerDate = null;

        if ($memo->signature_sd === $token) {
            $signerRole = 'Sous-Directeur';
            // Idéalement, vous auriez une colonne 'signed_at_sd', sinon on prend updated_at
            $signerDate = $memo->updated_at; 
        } elseif ($memo->signature_dir === $token) {
            $signerRole = 'Directeur';
            $signerDate = $memo->updated_at;
        }

        // 4. AFFICHAGE : On retourne la vue publique avec les infos du mémo
        return view('public.memo-verification', [
            'memo' => $memo,
            'signerRole' => $signerRole,
            'signerDate' => $signerDate
        ]);
    }
}
