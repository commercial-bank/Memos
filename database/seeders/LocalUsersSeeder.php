<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class LocalUsersSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Désactiver les contraintes de clé étrangère
        Schema::disableForeignKeyConstraints();

        // 2. Définir un mot de passe commun (haché une seule fois pour la performance)
        $password = Hash::make('password');

        $users = [
            [
                'first_name' => 'Admin',
                'last_name'  => 'Admin',
                'user_name'  => 'admin',
                'email'      => 'admin@local.test',
                'password'   => $password,
                'poste'      => 'Stagiaire Professionnel',
                'dir_id'     => 1,
                'sd_id'      => 2,
                'dep_id'     => null,
                'serv_id'    => null,
                'is_admin'   => 1,
                'is_active'  => 1,
                'manager_id' => null,
                'domain'     => null,
            ],
            [
                'first_name' => 'AMEL',
                'last_name'  => 'NGAHAMI',
                'user_name'  => 'j1',
                'email'      => 'angahami@local.test',
                'password'   => $password,
                'poste'      => 'Stagiaire Professionnel',
                'dir_id'     => 1,
                'sd_id'      => 2,
                'dep_id'     => null,
                'serv_id'    => null,
                'is_admin'   => 0,
                'is_active'  => 1,
                'manager_id' => 3,
                'domain'     => null,
            ],
            [
                'first_name' => 'RENAN',
                'last_name'  => 'FRANCOIS',
                'user_name'  => 'j2',
                'email'      => '2@local.test',
                'password'   => $password,
                'poste'      => 'Chef-Service',
                'dir_id'     => 1,
                'sd_id'      => 2,
                'dep_id'     => null,
                'serv_id'    => null,
                'is_admin'   => 0,
                'is_active'  => 1,
                'manager_id' => 4,
                'domain'     => null,
            ],

            [
                'first_name' => 'Brice',
                'last_name'  => 'SOH',
                'user_name'  => 'j3',
                'email'      => '3@local.test',
                'password'   => $password,
                'poste'      => 'Chef-Departement',
                'dir_id'     => 1,
                'sd_id'      => 2,
                'dep_id'     => null,
                'serv_id'    => null,
                'is_admin'   => 0,
                'is_active'  => 1,
                'manager_id' => 5,
                'domain'     => null,
            ],

            [
                'first_name' => 'Yannick',
                'last_name'  => 'Mengue',
                'user_name'  => 'j4',
                'email'      => '4@local.test',
                'password'   => $password,
                'poste'      => 'Sous-Directeur',
                'dir_id'     => 1,
                'sd_id'      => 2,
                'dep_id'     => null,
                'serv_id'    => null,
                'is_admin'   => 0,
                'is_active'  => 1,
                'manager_id' => 6,
                'domain'     => null,
            ],
            [
                'first_name' => 'olivier',
                'last_name'  => 'ouafo',
                'user_name'  => 'j55',
                'email'      => '55@local.test',
                'password'   => $password,
                'poste'      => 'Directeur',
                'dir_id'     => 1,
                'sd_id'      => 2,
                'dep_id'     => null,
                'serv_id'    => null,
                'is_admin'   => 0,
                'is_active'  => 1,
                'manager_id' => null,
                'domain'     => null,
            ],
            [
                'first_name' => 'olivia',
                'last_name'  => 'elong',
                'user_name'  => 'j5',
                'email'      => '5@local.test',
                'password'   => $password,
                'poste'      => 'Secretaire',
                'dir_id'     => 1,
                'sd_id'      => 2,
                'dep_id'     => null,
                'serv_id'    => null,
                'is_admin'   => 0,
                'is_active'  => 1,
                'manager_id' => 6,
                'domain'     => null,
            ],
            [
                'first_name' => 'sdg',
                'last_name'  => 'sdg',
                'user_name'  => 'j6',
                'email'      => '6@local.test',
                'password'   => $password,
                'poste'      => 'Secretaire',
                'dir_id'     => 9,
                'sd_id'      => 10,
                'dep_id'     => null,
                'serv_id'    => null,
                'is_admin'   => 0,
                'is_active'  => 1,
                'manager_id' => 9,
                'domain'     => null,
            ],
            [
                'first_name' => 'dg',
                'last_name'  => 'dg',
                'user_name'  => 'j7',
                'email'      => '7@local.test',
                'password'   => $password,
                'poste'      => 'Directeur',
                'dir_id'     => 9,
                'sd_id'      => 10,
                'dep_id'     => null,
                'serv_id'    => null,
                'is_admin'   => 0,
                'is_active'  => 1,
                'manager_id' => 10,
                'domain'     => null,
            ],
            [
                'first_name' => 'sdg1',
                'last_name'  => 'sdg1',
                'user_name'  => 'j8',
                'email'      => '8@local.test',
                'password'   => $password,
                'poste'      => 'Sous-Directeur',
                'dir_id'     => 9,
                'sd_id'      => 10,
                'dep_id'     => null,
                'serv_id'    => null,
                'is_admin'   => 0,
                'is_active'  => 1,
                'manager_id' => 11,
                'domain'     => null,
            ],
            [
                'first_name' => 'sdg2',
                'last_name'  => 'sdg2',
                'user_name'  => 'j9',
                'email'      => '9@local.test',
                'password'   => $password,
                'poste'      => 'Sous-Directeur',
                'dir_id'     => 9,
                'sd_id'      => 10,
                'dep_id'     => null,
                'serv_id'    => null,
                'is_admin'   => 0,
                'is_active'  => 1,
                'manager_id' => 12,
                'domain'     => null,
            ],

            [
                'first_name' => 'srh',
                'last_name'  => 'srh',
                'user_name'  => 'j10',
                'email'      => '10@local.test',
                'password'   => $password,
                'poste'      => 'Secretaire',
                'dir_id'     => 3,
                'sd_id'      => 4,
                'dep_id'     => null,
                'serv_id'    => null,
                'is_admin'   => 0,
                'is_active'  => 1,
                'manager_id' => null,
                'domain'     => null,
            ],
            [
                'first_name' => 'drh',
                'last_name'  => 'drh',
                'user_name'  => 'j11',
                'email'      => '11@local.test',
                'password'   => $password,
                'poste'      => 'Directeur',
                'dir_id'     => 3,
                'sd_id'      => 4,
                'dep_id'     => null,
                'serv_id'    => null,
                'is_admin'   => 0,
                'is_active'  => 1,
                'manager_id' => null,
                'domain'     => null,
            ],
        ];

        // 3. Boucler pour créer ou mettre à jour les utilisateurs
        foreach ($users as $userData) {
            // updateOrCreate( [champs de recherche], [champs à mettre à jour] )
            User::updateOrCreate(
                ['email' => $userData['email']], // On cherche par email (unique)
                $userData                        // On met à jour ou on crée avec le reste
            );
        }

        // 4. Réactiver les contraintes
        Schema::enableForeignKeyConstraints();
    }
}