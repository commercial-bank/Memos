<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
     public function verify($token)
    {
        // 1. On cherche le mémo qui possède ce token dans la colonne 'qr_code'
        // On charge aussi l'historique et les infos user
        $memo = Memo::where('qr_code', $token);

        // 2. Si le token n'existe pas ou est invalide
        if (!$memo) {
            abort(404, 'Document introuvable ou lien expiré.');
        }

        // 3. On retourne la vue de vérification
        return view('public.verification', compact('memo'));
    }
}
