<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LocalUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password'); // On hache le mot de passe une seule fois pour optimiser

        // ==========================================
        // USER 1 : LE DIRECTEUR (Top niveau)
        // ==========================================
        $directeur = User::create([
            'first_name' => 'Jean',
            'last_name'  => 'DIRECTEUR',
            'user_name'  => 'jed',
            'email'      => 'jeddd@local.test',
            'password'   => $password,
            'poste'      => 'employer', 
            'entity_name'    => 'Direction Transformation Digital Et systeme information',
            'sous_direction' => 'Production et si',
            'departement'    => 'Transformation Digital Et System Information',
            'service'        => 'Direction',
            'role'           => 'simple_user',
            'manager_id'         => null,
            'manager_replace_id' => null,
            'guid'   => null,
            'domain' => 'local',
        ]);

        // ==========================================
        // USER 2 : LE MANAGER (Rapporte au Directeur)
        // ==========================================
        $manager = User::create([
            'first_name' => 'Paul',
            'last_name'  => 'MANAGER',
            'user_name'  => 'pam',
            'email'      => 'manager@local.test',
            'password'   => $password,
            'poste'      => 'employer',
            'entity_name'    => 'Direction Transformation Digital Et systeme information',
            'sous_direction' => 'Production et si',
            'departement'    => 'Dev',
            'service'        => 'Management',
            'role'           => 'simple_user',
            'manager_id'         => $directeur->id, // Lien hiérarchique
            'manager_replace_id' => null,
            'guid'   => null,
            'domain' => 'local',
        ]);

        // ==========================================
        // USER 3 à 10 : LES EMPLOYÉS (Rapportent au Manager)
        // ==========================================
        for ($i = 1; $i <= 8; $i++) {
            
            $firstName = fake()->firstName();
            $lastName  = fake()->lastName();
            // Création d'un username simple (ex: jean.dupont -> jeand)
            $userName  = strtolower(substr($firstName, 0, 4) . substr($lastName, 0, 1) . $i);

            User::create([
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'user_name'  => $userName,
                'email'      => $userName . '@local.test',
                'password'   => $password,
                'poste'      => 'employer',
                'entity_name'    => 'Direction Transformation Digital Et systeme information',
                'sous_direction' => 'Production et si',
                'departement'    => 'Transformation Digital Et System Information',
                'service'        => 'Developpement',
                'role'           => 'simple_user',
                'manager_id'         => $manager->id, // Ils sont sous le chef de service
                'manager_replace_id' => null,
                'guid'   => null,
                'domain' => 'local',
            ]);
        }
    }
}