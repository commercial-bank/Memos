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
            'first_name'  => 'paul',
            'last_name'   => 'nba',
            'user_name'   => 'j1',
            'email'       => '1@local.test',
            'password'    => $password,
            'poste'       => 'Employer',
            'entity_id' => 1,
            'sous_direction_id' => 2,
            'departement' => "Departement Transformation Digital et Systeme d'Information",
            'service'     => "Production et Developement",
            'is_admin'    => false,
            'is_active'   => true,
            'manager_id'  => 2,
            'domain'      => 'local',
        ]);

        // 1. LE DIRECTEUR (Top niveau - j5)
        $dtdsiDirecteur = User::create([
            'first_name'  => 'paul',
            'last_name'   => 'nba',
            'user_name'   => 'j2',
            'email'       => '2@local.test',
            'password'    => $password,
            'poste'       => 'Chef-Service',
            'entity_id' => 1,
            'sous_direction_id' => 2,
            'departement' => "Departement Transformation Digital et Systeme d'Information",
            'service'     => "Production et Developement",
            'is_admin'    => false,
            'is_active'   => true,
            'manager_id'  => 3,
            'domain'      => 'local',
        ]);

        // 1. LE DIRECTEUR (Top niveau - j5)
        $dtdsiDirecteur = User::create([
            'first_name'  => 'paul',
            'last_name'   => 'nba',
            'user_name'   => 'j3',
            'email'       => '3@local.test',
            'password'    => $password,
            'poste'       => 'Chef-Departement',
            'entity_id' => 1,
            'sous_direction_id' => 2,
            'departement' => "Departement Transformation Digital et Systeme d'Information",
            'service'     => "Production et Developement",
            'is_admin'    => false,
            'is_active'   => true,
            'manager_id'  => 4,
            'domain'      => 'local',
        ]);

        // 1. LE DIRECTEUR (Top niveau - j5)
        $dtdsiDirecteur = User::create([
            'first_name'  => 'paul',
            'last_name'   => 'nba',
            'user_name'   => 'j4',
            'email'       => '4@local.test',
            'password'    => $password,
            'poste'       => 'Sous-Directeur',
            'entity_id' => 1,
            'sous_direction_id' => 2,
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
            'last_name'   => 'nba',
            'user_name'   => 'j5',
            'email'       => '5@local.test',
            'password'    => $password,
            'poste'       => 'Directeur',
            'entity_id' => 1,
            'sous_direction_id' => 2,
            'departement' => "Departement Transformation Digital et Systeme d'Information",
            'service'     => "Production et Developement",
            'is_admin'    => false,
            'is_active'   => true,
            'manager_id'  => 6,
            'domain'      => 'local',
        ]);

        // 1. LE DIRECTEUR (Top niveau - j5)
        $dtdsiDirecteur = User::create([
            'first_name'  => 'paul',
            'last_name'   => 'nba',
            'user_name'   => 'j6',
            'email'       => '6@local.test',
            'password'    => $password,
            'poste'       => 'Secretaire',
            'entity_id' => 1,
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