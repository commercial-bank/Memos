<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema; // Important pour gérer les clés étrangères

class LocalUsersSeeder extends Seeder
{
    public function run(): void
    {
        

        // 1. Définir un mot de passe commun
$password = Hash::make('password'); // Ou votre variable $password existante

// 2. Désactiver les contraintes de clé étrangère temporairement
// C'est CRUCIAL car l'utilisateur ID 2 a besoin du manager ID 4 qui n'existe pas encore lors de l'insertion séquentielle.
Schema::disableForeignKeyConstraints();

$users = [
    [
     
        'first_name' => 'Admin',
        'last_name' => 'Admin',
        'user_name' => 'admin',
        'email' => 'admin@local.test',
        'password' => $password,
        'poste' => 'Stagiaire Professionnel', // L'image indique Stagiaire Pro (contrairement à "Employer" dans votre code)
        'dir_id' => null,
        'sd_id' => null,
        'dep_id' => null,
        'serv_id' => null,
        'is_admin' => 1,
        'is_active' => 1,
        'manager_id' => null,
        'domain' => null,
    ],
    
];

// 3. Boucler pour créer les utilisateurs
// On utilise updateOrCreate pour éviter les doublons si le seeder est relancé
foreach ($users as $userData) {
    User::updateOrCreate(
        $userData
    );
}

// 4. Réactiver les contraintes
Schema::enableForeignKeyConstraints();

  

        



        
    }
}