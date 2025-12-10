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
        // 1. NETTOYAGE : On vide la table users avant de commencer
        // On désactive les vérifications de clés étrangères pour éviter les erreurs car les users se lient entre eux (manager_id)
        Schema::disableForeignKeyConstraints();
        User::truncate(); 
        Schema::enableForeignKeyConstraints();

        // 2. CRÉATION
        $password = Hash::make('password');

        $entiteDTDSI = 'Direction Transformation Digital  Systeme information';
        $entiteDRH   = 'Direction Ressources Humaines';
        $entiteDG    = 'Direction Générale';
        
        $sousDirProd = 'Production Si';
        $deptDev     = 'Dev';
        $serviceMgt  = 'Management';

        // --- DÉBUT DE LA CRÉATION DES USERS (Le même code que précédemment) ---

        // 1. LE DIRECTEUR (Top niveau - j5)
        $dtdsiDirecteur = User::create([
            'first_name'  => 'Marc Arthur',
            'last_name'   => 'Kemayou',
            'user_name'   => 'j1',
            'email'       => '1@local.test',
            'password'    => $password,
            'poste'       => 'Employer',
            'entity_id' => 1,
            'sous_direction_id' => 5,
            'departement' => "Departement Transformation Digital et Systeme d'Information",
            'service'     => "Production et Developement",
            'is_admin'    => false,
            'is_active'   => true,
            'manager_id'  => 2,
            'domain'      => 'local',
        ]);

        // 1. LE DIRECTEUR (Top niveau - j5)
        $dtdsiDirecteur = User::create([
            'first_name'  => 'Derick ',
            'last_name'   => 'Monsieur Derick',
            'user_name'   => 'j11',
            'email'       => '11@local.test',
            'password'    => $password,
            'poste'       => 'Employer',
            'entity_id' => 1,
            'sous_direction_id' => 2,
            'departement' => "Departement Transformation Digital et Systeme d'Information",
            'service'     => "Production et Developement",
            'is_admin'    => false,
            'is_active'   => true,
            'manager_id'  => null,
            'domain'      => 'local',
        ]);

        // 1. LE DIRECTEUR (Top niveau - j5)
        $dtdsiDirecteur = User::create([
            'first_name'  => 'Renan Francois',
            'last_name'   => 'Eyokinack',
            'user_name'   => 'j2',
            'email'       => '2@local.test',
            'password'    => $password,
            'poste'       => 'Chef-Service',
            'entity_id' => 1,
            'sous_direction_id' => 5,
            'departement' => "Departement Transformation Digital et Systeme d'Information",
            'service'     => "Production et Developement",
            'is_admin'    => false,
            'is_active'   => true,
            'manager_id'  => 3,
            'domain'      => 'local',
        ]);

        // 1. LE DIRECTEUR (Top niveau - j5)
        $dtdsiDirecteur = User::create([
            'first_name'  => 'Brice',
            'last_name'   => 'Soh Nanda',
            'user_name'   => 'j3',
            'email'       => '3@local.test',
            'password'    => $password,
            'poste'       => 'Chef-Departement',
            'entity_id' => 1,
            'sous_direction_id' => 5,
            'departement' => "Departement Transformation Digital et Systeme d'Information",
            'service'     => "Production et Developement",
            'is_admin'    => false,
            'is_active'   => true,
            'manager_id'  => 4,
            'domain'      => 'local',
        ]);

        // 1. LE DIRECTEUR (Top niveau - j5)
        $dtdsiDirecteur = User::create([
            'first_name'  => 'yannick',
            'last_name'   => 'Mengue',
            'user_name'   => 'j4',
            'email'       => '4@local.test',
            'password'    => $password,
            'poste'       => 'Sous-Directeur',
            'entity_id' => 1,
            'sous_direction_id' => 5,
            'departement' => "Departement Transformation Digital et Systeme d'Information",
            'service'     => "Production et Developement",
            'is_admin'    => false,
            'is_active'   => true,
            'manager_id'  => 5,
            'domain'      => 'local',
        ]);

        // 1. LE DIRECTEUR (Top niveau - j5)
        $dtdsiDirecteur = User::create([
            'first_name'  => 'paul',
            'last_name'   => 'wafo',
            'user_name'   => 'j5',
            'email'       => '5@local.test',
            'password'    => $password,
            'poste'       => 'Directeur',
            'entity_id' => 1,
            'sous_direction_id' => 5,
            'departement' => "Departement Transformation Digital et Systeme d'Information",
            'service'     => "Production et Developement",
            'is_admin'    => false,
            'is_active'   => true,
            'manager_id'  => 6,
            'domain'      => 'local',
        ]);

        // 1. LE DIRECTEUR (Top niveau - j5)
        $dtdsiDirecteur = User::create([
            'first_name'  => 'olivia',
            'last_name'   => 'elong essongo',
            'user_name'   => 'j6',
            'email'       => '6@local.test',
            'password'    => $password,
            'poste'       => 'Secretaire',
            'entity_id' => 1,
            'sous_direction_id' => 5,
            'departement' => "Departement Transformation Digital et Systeme d'Information",
            'service'     => "Production et Developement",
            'is_admin'    => false,
            'is_active'   => true,
            'manager_id'  => null,
            'domain'      => 'local',
        ]);

         // 1. LE DIRECTEUR (Top niveau - j5)
        $dtdsiDirecteur = User::create([
            'first_name'  => 'ol',
            'last_name'   => 'essongo',
            'user_name'   => 'j10',
            'email'       => '10@local.test',
            'password'    => $password,
            'poste'       => 'Secretaire',
            'entity_id' => 2,
            'sous_direction_id' => 2,
            'departement' => "Departement Transformation Digital et Systeme d'Information",
            'service'     => "Production et Developement",
            'is_admin'    => false,
            'is_active'   => true,
            'manager_id'  => null,
            'domain'      => 'local',
        ]);

         // 1. LE DIRECTEUR (Top niveau - j5)
        $dtdsiDirecteur = User::create([
            'first_name'  => 'oli',
            'last_name'   => 'ess',
            'user_name'   => 'j12',
            'email'       => '12@local.test',
            'password'    => $password,
            'poste'       => 'Secretaire',
            'entity_id' => 3,
            'sous_direction_id' => 2,
            'departement' => "Departement Transformation Digital et Systeme d'Information",
            'service'     => "Production et Developement",
            'is_admin'    => false,
            'is_active'   => true,
            'manager_id'  => null,
            'domain'      => 'local',
        ]);

          // 1. LE DIRECTEUR (Top niveau - j5)
        $dtdsiDirecteur = User::create([
            'first_name'  => 'oli',
            'last_name'   => 'ess',
            'user_name'   => 'j15',
            'email'       => '15@local.test',
            'password'    => $password,
            'poste'       => 'Directeur',
            'entity_id' => 3,
            'sous_direction_id' => 2,
            'departement' => "Departement Transformation Digital et Systeme d'Information",
            'service'     => "Production et Developement",
            'is_admin'    => false,
            'is_active'   => true,
            'manager_id'  => null,
            'domain'      => 'local',
        ]);

        // 1. LE DIRECTEUR (Top niveau - j5)
        $dtdsiDirecteur = User::create([
            'first_name'  => 'oli',
            'last_name'   => 'ess',
            'user_name'   => 'j16',
            'email'       => '16@local.test',
            'password'    => $password,
            'poste'       => 'Sous-Directeur',
            'entity_id' => 3,
            'sous_direction_id' => 2,
            'departement' => "Departement Transformation Digital et Systeme d'Information",
            'service'     => "Production et Developement",
            'is_admin'    => false,
            'is_active'   => true,
            'manager_id'  => null,
            'domain'      => 'local',
        ]);

        // 1. LE DIRECTEUR (Top niveau - j5)
        $dtdsiDirecteur = User::create([
            'first_name'  => 'oli',
            'last_name'   => 'ess',
            'user_name'   => 'j17',
            'email'       => '17@local.test',
            'password'    => $password,
            'poste'       => 'Sous-Directeur',
            'entity_id' => 3,
            'sous_direction_id' => 2,
            'departement' => "Departement Transformation Digital et Systeme d'Information",
            'service'     => "Production et Developement",
            'is_admin'    => false,
            'is_active'   => true,
            'manager_id'  => null,
            'domain'      => 'local',
        ]);

        
    }
}