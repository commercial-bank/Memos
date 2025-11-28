<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DestinatairesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Récupérer les IDs des Mémos et des Entités
        $memos = Memo::all();
        // Attention : on utilise le nom de table 'entity' comme défini dans votre migration précédente
        $entityIds = DB::table('entity')->pluck('id'); 

        if ($memos->isEmpty() || $entityIds->isEmpty()) {
            $this->command->warn("Erreur : Aucune donnée dans 'memos' ou 'entity'. Lancez les autres seeders avant.");
            return;
        }

        // Liste des actions possibles
        $actions = [
            'Pour attribution',
            'Pour information',
            'Pour avis',
            'Pour classement',
            'Pour validation'
        ];

        // 2. Parcourir chaque mémo pour lui assigner des destinataires
        foreach ($memos as $memo) {
            
            // On détermine combien d'entités vont recevoir ce mémo (entre 1 et 3 au hasard)
            $numberOfRecipients = rand(1, 3);
            
            // On prend X entités au hasard dans la liste
            $randomEntities = $entityIds->random($numberOfRecipients);

            foreach ($randomEntities as $index => $entityId) {
                
                // Logique pour rendre les données réalistes :
                // Le premier destinataire est souvent celui qui doit agir ("Pour attribution")
                // Les suivants sont souvent en copie ("Pour information")
                if ($index === 0) {
                    $action = 'Pour attribution';
                } else {
                    $action = 'Pour information';
                }

                // Insertion directe (DB::table est plus sûr si le Modèle Destinataire n'est pas encore configuré)
                DB::table('destinataires')->insert([
                    'action'     => $action,
                    'memo_id'    => $memo->id,
                    'entity_id'  => $entityId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
