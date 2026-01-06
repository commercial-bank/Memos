<?php

namespace Database\Seeders;

use App\Models\Entity;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EntitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   

    public function run()
{
    // Structure : 'SIGLE_DIR' => ['Nom complet', 'Sous-directions' => ['SIGLE_SD' => 'Nom SD']]
    $structure = [
        'DTDSI' => [
            'name' => 'Direction Transformation Digital Et systeme information',
            'subs' => [
                'SDTDSI'   => 'Sous Direction Transformation Digital Et systeme information',
            ]
        ],
        'DRH' => [
            'name' => 'Direction des Ressources Humaines',
            'subs' => [
                'SD-GRH'  => 'Sous-Direction Gestion des Ressources Humaines',
                'SD-FPS'  => 'Sous-Direction Formation et Paie',
            ]
        ],
        'DAF' => [
            'name' => 'Direction Administrative et Financière',
            'subs' => [
                'SD-COMPTA' => 'Sous-Direction Comptabilité',
                'SD-BUDGET' => 'Sous-Direction Budget et Finances',
            ]
        ],
        'DG' => [
            'name' => 'Direction Générale',
            'subs' => [
                'SD-AUDIT' => 'Sous-Direction Audit Interne',
            ]
        ],
        // Vous pouvez ajouter les autres directions ici...
    ];

    foreach ($structure as $dirRef => $info) {
        
        // 1. Création ou récupération de la Direction
        $direction = Entity::firstOrCreate(
            ['ref' => $dirRef],
            [
                'name' => $info['name'],
                'type' => 'Direction',
                'upper_id' => null, // Niveau racine
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 2. Création des Sous-Directions rattachées
        if (isset($info['subs'])) {
            foreach ($info['subs'] as $subRef => $subName) {
                Entity::firstOrCreate(
                    ['ref' => $subRef],
                    [
                        'name' => $subName,
                        'type' => 'Sous-Direction',
                        'upper_id' => $direction->id, // On lie la SD à la Direction via l'ID
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
}
