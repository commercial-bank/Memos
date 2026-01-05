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
                'dir_id'     => null,
                'sd_id'      => null,
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
                'dir_id'     => null,
                'sd_id'      => null,
                'dep_id'     => null,
                'serv_id'    => null,
                'is_admin'   => 0,
                'is_active'  => 1,
                'manager_id' => null,
                'domain'     => null,
            ],
            [
                'first_name' => 'RENAN',
                'last_name'  => 'FRANCOIS',
                'user_name'  => 'j2',
                'email'      => '2@local.test',
                'password'   => $password,
                'poste'      => null,
                'dir_id'     => null,
                'sd_id'      => null,
                'dep_id'     => null,
                'serv_id'    => null,
                'is_admin'   => 0,
                'is_active'  => 1,
                'manager_id' => null,
                'domain'     => null,
            ],

            [
                'first_name' => 'Brice',
                'last_name'  => 'SOH',
                'user_name'  => 'j3',
                'email'      => '3@local.test',
                'password'   => $password,
                'poste'      => null,
                'dir_id'     => null,
                'sd_id'      => null,
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