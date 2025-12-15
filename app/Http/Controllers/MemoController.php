<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class MemoController extends Controller
{
    public function print($id)
    {
        // 1. Charger les données
        $memo = Memo::with(['user', 'destinataires', 'historiques.user'])->findOrFail($id);

        // 2. Préparer le PDF
        $pdf = Pdf::loadView('pdf.memo_template', compact('memo'));

        // 3. Configuration (A4)
        $pdf->setPaper('a4', 'portrait');

        // 4. NETTOYAGE DU NOM DE FICHIER (Le correctif est ici)
        // On remplace les '/' et '\' par des tirets '-'
        // On gère aussi le cas où la référence serait vide (?? 'No-Ref')
        $safeReference = str_replace(['/', '\\'], '-', $memo->reference ?? 'No-Ref');
        
        $filename = 'Memo_' . $safeReference . '.pdf';

        // 5. Affichage
        return $pdf->stream($filename);
    }
}