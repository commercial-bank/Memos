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
        // Tableau associatif : 'SIGLE' => 'Nom de la direction'
        $directions = [
            'DTDSI' => 'Direction Transformation Digital Et systeme information',
            'DG'    => 'Direction Générale', // J'ai ajouté le sigle DG qui manquait
            'DRH'   => 'Direction des Ressources Humaines',
            'DAF'   => 'Direction Administrative et Financière',
            'DCM'   => 'Direction Commerciale et Marketing',
            'DL'    => 'Direction de la Logistique',
            'DCOM'  => 'Direction de la Communication', // J'ai mis DCOM pour éviter la confusion avec DC (Commerciale)
            'DJ'    => 'Direction Juridique',
        ];

        foreach ($directions as $ref => $name) {
            
            // On utilise firstOrCreate pour éviter les doublons si on lance le seeder 2 fois
            Entity::firstOrCreate(
                ['ref' => $ref], // On vérifie si ce SIGLE existe déjà
                [
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
