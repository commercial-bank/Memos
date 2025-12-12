<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // Import crucial

class MemoController extends Controller
{
    public function print($id)
    {
        // On charge le mémo avec ses relations utiles
        $memo = Memo::with(['user', 'destinataires', 'historiques.user'])->findOrFail($id);

        // On prépare le PDF
        // 'pdf.memo_template' sera le nom du fichier blade qu'on va créer juste après
        $pdf = Pdf::loadView('pdf.memo_template', compact('memo'));

        // On configure le papier (A4 Portrait)
        $pdf->setPaper('a4', 'portrait');

        // On l'affiche dans le navigateur (stream) au lieu de le télécharger direct
        return $pdf->stream('Memo_'.$memo->reference.'.pdf');
    }
}