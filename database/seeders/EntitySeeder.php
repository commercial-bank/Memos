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
    public function run(): void
    {
        $entities = [
            // 1. Celle utilisée par vos utilisateurs (Important pour la cohérence)
            'Direction Transformation Digital Et systeme information',
            
            // 2. Autres Directions classiques pour peupler la liste
            'Direction Générale',
            'Direction des Ressources Humaines',
            'Direction Administrative et Financière',
            'Direction Commerciale et Marketing',
            'Direction de la Logistique',
            'Direction de la Communication',
            'Direction Juridique',
        ];

        foreach ($entities as $title) {
            // Méthode avec Modèle (Recommandé si vous avez le modèle Entity)
            // Entity::create(['title' => $title]);

            // Méthode générique (Fonctionne même sans modèle, juste avec le nom de la table)
            Entity::create([
                'title' => $title,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
