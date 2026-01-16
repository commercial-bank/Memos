<?php

namespace App\Console\Commands;

use App\Models\DraftedMemo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanOldDrafts extends Command
{
    /**
     * Le nom et la signature de la commande console.
     */
    protected $signature = 'drafts:clean';

    /**
     * La description de la commande.
     */
    protected $description = 'Supprime les brouillons non modifiés depuis plus d\'un mois';

    /**
     * Exécution de la commande.
     */
    public function handle()
    {
        // 1. Définir la date limite (TEST : Maintenant - 1 minute)
        // Au lieu de subMonth(), on utilise subMinute()
        $limitDate = now()->subMinute();

        $this->info("Recherche des brouillons modifiés avant : " . $limitDate->toDateTimeString());

        // 2. Récupérer les brouillons concernés
        $oldDrafts = DraftedMemo::where('updated_at', '<', $limitDate)->get();

        $count = 0;

        foreach ($oldDrafts as $draft) {
            // A. Nettoyage des fichiers physiques
            if (!empty($draft->pieces_jointes) && is_array($draft->pieces_jointes)) {
                foreach ($draft->pieces_jointes as $pj) {
                    $path = is_array($pj) ? ($pj['path'] ?? null) : ($pj->path ?? null);
                    
                    if ($path && Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }
            }

            // B. Suppression de l'enregistrement
            $draft->delete();
            $count++;
        }

        $this->info("Nettoyage terminé : {$count} brouillon(s) supprimé(s).");
    }
}