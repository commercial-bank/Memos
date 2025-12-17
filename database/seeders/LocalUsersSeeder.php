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
        'poste' => 'Stagiaire Pro', // L'image indique Stagiaire Pro (contrairement à "Employer" dans votre code)
        'entity_id' => 1,
        'sous_direction_id' => null,
        'departement' => null,
        'service' => 'Production et Developement',
        'is_admin' => 1,
        'is_active' => 1,
        'manager_id' => null,
        'domain' => null,
    ],
    [
        
        'first_name' => 'Marc Arthur',
        'last_name' => 'Kemayou',
        'user_name' => 'j1',
        'email' => '1@local.test',
        'password' => $password,
        'poste' => 'Employer',
        'entity_id' => 1,
        'sous_direction_id' => 1,
        'departement' => 'DPTD',
        'service' => 'ECONOMAT',
        'is_admin' => 0,
        'is_active' => 1,
        'manager_id' => 4,
        'domain' => null,
    ],
    [
        
        'first_name' => 'Derick',
        'last_name' => 'Monsieur',
        'user_name' => 'j11',
        'email' => '11@local.test',
        'password' => $password,
        'poste' => 'Chef-Service',
        'entity_id' => 1,
        'sous_direction_id' => 1,
        'departement' => 'Departement Developpement',
        'service' => 'Developement',
        'is_admin' => 0,
        'is_active' => 1,
        'manager_id' => 5,
        'domain' => 'local',
    ],
    [
        
        'first_name' => 'Renan Francesco',
        'last_name' => 'Eyokinack',
        'user_name' => 'j2',
        'email' => '2@local.test',
        'password' => $password,
        'poste' => 'Chef-Service',
        'entity_id' => 1,
        'sous_direction_id' => 1,
        'departement' => 'Departement Developpement',
        'service' => 'Production et Developement',
        'is_admin' => 0,
        'is_active' => 1,
        'manager_id' => 5,
        'domain' => null,
    ],
    [
       
        'first_name' => 'Brice',
        'last_name' => 'Soh Nanda',
        'user_name' => 'j3',
        'email' => '3@local.test',
        'password' => $password,
        'poste' => 'Chef-Departement',
        'entity_id' => 1,
        'sous_direction_id' => 1,
        'departement' => 'Departement Developpement',
        'service' => null,
        'is_admin' => 0,
        'is_active' => 1,
        'manager_id' => 6,
        'domain' => null,
    ],
    [
       
        'first_name' => 'yannick',
        'last_name' => 'Mengue',
        'user_name' => 'j4',
        'email' => '4@local.test',
        'password' => $password,
        'poste' => 'Sous-Directeur',
        'entity_id' => 1,
        'sous_direction_id' => 1,
        'departement' => null,
        'service' => null,
        'is_admin' => 0,
        'is_active' => 1,
        'manager_id' => 7,
        'domain' => 'local',
    ],
    [
        
        'first_name' => 'directeur',
        'last_name' => 'monsieur wafo', // Nom coupé dans l'image, ajustez si nécessaire
        'user_name' => 'j5',
        'email' => '5@local.test',
        'password' => $password,
        'poste' => 'Directeur',
        'entity_id' => 1,
        'sous_direction_id' => null,
        'departement' => null,
        'service' => null,
        'is_admin' => 0,
        'is_active' => 1,
        'manager_id' => null,
        'domain' => null,
    ],
    [
        
        'first_name' => 'olivia',
        'last_name' => 'elong esson', // Nom coupé dans l'image
        'user_name' => 'j6',
        'email' => '6@local.test',
        'password' => $password,
        'poste' => 'Secretaire',
        'entity_id' => 1,
        'sous_direction_id' => null,
        'departement' => null,
        'service' => null,
        'is_admin' => 0,
        'is_active' => 1,
        'manager_id' => 7,
        'domain' => null,
    ],
    [
        
        'first_name' => 'secretaire',
        'last_name' => 'DG',
        'user_name' => 'j7',
        'email' => '7@local.test',
        'password' => $password,
        'poste' => 'Secretaire',
        'entity_id' => 2,
        'sous_direction_id' => null,
        'departement' => null,
        'service' => null,
        'is_admin' => 0,
        'is_active' => 1,
        'manager_id' => 11,
        'domain' => null,
    ],
    [
        
        'first_name' => 'secretaire',
        'last_name' => 'RH',
        'user_name' => 'j8',
        'email' => '8@local.test',
        'password' => $password,
        'poste' => 'Secretaire',
        'entity_id' => 3,
        'sous_direction_id' => null,
        'departement' => null,
        'service' => null,
        'is_admin' => 0,
        'is_active' => 1,
        'manager_id' => 12,
        'domain' => null,
    ],
    [
        
        'first_name' => 'directeur',
        'last_name' => 'DG',
        'user_name' => 'j9',
        'email' => '9@local.test',
        'password' => $password,
        'poste' => 'Directeur',
        'entity_id' => 2,
        'sous_direction_id' => null,
        'departement' => null,
        'service' => null,
        'is_admin' => 0,
        'is_active' => 1,
        'manager_id' => null,
        'domain' => null,
    ],
    [
       
        'first_name' => 'directeur',
        'last_name' => 'RH',
        'user_name' => 'j10',
        'email' => '10@local.test',
        'password' => $password,
        'poste' => 'Directeur',
        'entity_id' => 3,
        'sous_direction_id' => null,
        'departement' => null,
        'service' => 'Production et Developement',
        'is_admin' => 0,
        'is_active' => 1,
        'manager_id' => null,
        'domain' => null,
    ],
    [
       
        'first_name' => 'sous directeur 1',
        'last_name' => 'RH',
        'user_name' => 'j12',
        'email' => '12@local.test',
        'password' => $password,
        'poste' => 'Sous-Directeur',
        'entity_id' => 3,
        'sous_direction_id' => null,
        'departement' => null,
        'service' => null,
        'is_admin' => 0,
        'is_active' => 1,
        'manager_id' => 13, // S'auto-manage selon l'image
        'domain' => null,
    ],
    [
        
        'first_name' => 'sous directeur 2',
        'last_name' => 'RH',
        'user_name' => 'j13',
        'email' => '13@local.test',
        'password' => $password,
        'poste' => 'Sous-Directeur',
        'entity_id' => 3,
        'sous_direction_id' => null,
        'departement' => null,
        'service' => null,
        'is_admin' => 0,
        'is_active' => 1,
        'manager_id' => 13,
        'domain' => null,
    ],
    [
       
        'first_name' => 'patrick',
        'last_name' => 'CHOUFFA',
        'user_name' => 'j14',
        'email' => '14@local.test',
        'password' => $password,
        'poste' => 'Sous-Directeur',
        'entity_id' => 1,
        'sous_direction_id' => null,
        'departement' => null,
        'service' => null,
        'is_admin' => 0,
        'is_active' => 1,
        'manager_id' => 7,
        'domain' => null,
    ],
    [
      
        'first_name' => 'employer',
        'last_name' => 'RH',
        'user_name' => 'j15',
        'email' => '15@local.test',
        'password' => $password,
        'poste' => 'Employer',
        'entity_id' => 3,
        'sous_direction_id' => null,
        'departement' => null,
        'service' => null,
        'is_admin' => 0,
        'is_active' => 1,
        'manager_id' => 13,
        'domain' => null,
    ],
    [
      
        'first_name' => 'employer',
        'last_name' => 'RH',
        'user_name' => 'j16',
        'email' => '16@local.test',
        'password' => $password,
        'poste' => 'Employer',
        'entity_id' => 3,
        'sous_direction_id' => null,
        'departement' => null,
        'service' => null,
        'is_admin' => 0,
        'is_active' => 1,
        'manager_id' => 14,
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