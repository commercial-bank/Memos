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

        $entiteDTDSI = 'Direction Transformation Digital Et systeme information';
        $entiteDRH   = 'Direction des Ressources Humaines';
        $entiteDG    = 'Direction Générale';
        
        $sousDirProd = 'Production et si';
        $deptDev     = 'Dev';
        $serviceMgt  = 'Management';

        // --- DÉBUT DE LA CRÉATION DES USERS (Le même code que précédemment) ---

        // 1. LE DIRECTEUR (Top niveau - j5)
        $dtdsiDirecteur = User::create([
            'first_name'  => 'Paul',
            'last_name'   => 'MANAGER',
            'user_name'   => 'j5',
            'email'       => 'manager5@local.test',
            'password'    => $password,
            'poste'       => 'Directeur',
            'entity_name' => $entiteDTDSI,
            'sous_direction' => $sousDirProd,
            'departement' => $deptDev,
            'service'     => $serviceMgt,
            'role'        => 'simple_user',
            'manager_id'  => null,
            'domain'      => 'local',
        ]);

        // 2. LA SECRETAIRE (j6)
        $dtdsiSecretaire = User::create([
            'first_name'  => 'Paul',
            'last_name'   => 'MANAGER',
            'user_name'   => 'j6',
            'email'       => 'manager6@local.test',
            'password'    => $password,
            'poste'       => 'Secretaire',
            'entity_name' => $entiteDTDSI,
            'sous_direction' => $sousDirProd,
            'departement' => $deptDev,
            'service'     => $serviceMgt,
            'role'        => 'simple_user',
            'manager_id'  => $dtdsiDirecteur->id,
            'domain'      => 'local',
        ]);

        // Mise à jour assistante
        $dtdsiDirecteur->update(['director_assistant_id' => $dtdsiSecretaire->id]);

        // 3. SOUS-DIRECTEUR (j4)
        $dtdsiSousDirecteur = User::create([
            'first_name'  => 'Paul',
            'last_name'   => 'MANAGER',
            'user_name'   => 'j4',
            'email'       => 'manager4@local.test',
            'password'    => $password,
            'poste'       => 'Sous-Directeur',
            'entity_name' => $entiteDTDSI,
            'sous_direction' => $sousDirProd,
            'departement' => $deptDev,
            'service'     => $serviceMgt,
            'role'        => 'simple_user',
            'manager_id'  => $dtdsiDirecteur->id,
            'domain'      => 'local',
        ]);

        // 4. CHEF DEPARTEMENT (j3)
        $dtdsiChefDept = User::create([
            'first_name'  => 'Paul',
            'last_name'   => 'MANAGER',
            'user_name'   => 'j3',
            'email'       => 'manager3@local.test',
            'password'    => $password,
            'poste'       => 'Chef-Departement',
            'entity_name' => $entiteDTDSI,
            'sous_direction' => $sousDirProd,
            'departement' => $deptDev,
            'service'     => $serviceMgt,
            'role'        => 'simple_user',
            'manager_id'  => $dtdsiSousDirecteur->id,
            'domain'      => 'local',
        ]);

        // 5. CHEF SERVICE (j2)
        $dtdsiChefService = User::create([
            'first_name'  => 'Paul',
            'last_name'   => 'MANAGER',
            'user_name'   => 'j2',
            'email'       => 'manager2@local.test',
            'password'    => $password,
            'poste'       => 'Chef-Service',
            'entity_name' => $entiteDTDSI,
            'sous_direction' => $sousDirProd,
            'departement' => $deptDev,
            'service'     => $serviceMgt,
            'role'        => 'simple_user',
            'manager_id'  => $dtdsiChefDept->id,
            'domain'      => 'local',
        ]);

        // 6. EMPLOYÉ (j1)
        User::create([
            'first_name'  => 'Jean',
            'last_name'   => 'DIRECTEUR',
            'user_name'   => 'j1',
            'email'       => 'jeddd@local.test',
            'password'    => $password,
            'poste'       => 'employer', 
            'entity_name' => $entiteDTDSI,
            'sous_direction' => $sousDirProd,
            'departement' => 'Transformation Digital Et System Information',
            'service'     => 'Direction',
            'role'        => 'simple_user',
            'manager_id'  => $dtdsiChefService->id,
            'domain'      => 'local',
        ]);

        // --- GROUPE DG ---

        // SECRETAIRE DG (j7)
        User::create([
            'first_name'  => 'Paul',
            'last_name'   => 'MANAGER',
            'user_name'   => 'j7',
            'email'       => 'manager7@local.test',
            'password'    => $password,
            'poste'       => 'Secretaire',
            'entity_name' => $entiteDG,
            'sous_direction' => $sousDirProd,
            'departement' => $deptDev,
            'service'     => $serviceMgt,
            'role'        => 'simple_user',
            'manager_id'  => null,
            'domain'      => 'local',
        ]);

        // --- GROUPE DRH ---

        // DIRECTEUR DRH (j9)
        $drhDirecteur = User::create([
            'first_name'  => 'Paul',
            'last_name'   => 'MANAGER',
            'user_name'   => 'j9',
            'email'       => 'manager9@local.test',
            'password'    => $password,
            'poste'       => 'Directeur',
            'entity_name' => $entiteDRH,
            'sous_direction' => $sousDirProd,
            'departement' => $deptDev,
            'service'     => $serviceMgt,
            'role'        => 'simple_user',
            'manager_id'  => null,
            'domain'      => 'local',
        ]);

        // SECRETAIRE DRH (j8)
        User::create([
            'first_name'  => 'Paul',
            'last_name'   => 'MANAGER',
            'user_name'   => 'j8',
            'email'       => 'manager8@local.test',
            'password'    => $password,
            'poste'       => 'Secretaire',
            'entity_name' => $entiteDRH,
            'sous_direction' => $sousDirProd,
            'departement' => $deptDev,
            'service'     => $serviceMgt,
            'role'        => 'simple_user',
            'manager_id'  => $drhDirecteur->id,
            'domain'      => 'local',
        ]);

        // SOUS-DIRECTEURS DRH (j10, j11)
        $drhSousDir1 = User::create([
            'first_name'  => 'Paul',
            'last_name'   => 'MANAGER',
            'user_name'   => 'j10',
            'email'       => 'manager10@local.test',
            'password'    => $password,
            'poste'       => 'Sous-Directeur',
            'entity_name' => $entiteDRH,
            'sous_direction' => $sousDirProd,
            'departement' => $deptDev,
            'service'     => $serviceMgt,
            'role'        => 'simple_user',
            'manager_id'  => $drhDirecteur->id,
            'domain'      => 'local',
        ]);

        $drhSousDir2 = User::create([
            'first_name'  => 'Paul',
            'last_name'   => 'MANAGER',
            'user_name'   => 'j11',
            'email'       => 'manager11@local.test',
            'password'    => $password,
            'poste'       => 'Sous-Directeur',
            'entity_name' => $entiteDRH,
            'sous_direction' => $sousDirProd,
            'departement' => $deptDev,
            'service'     => $serviceMgt,
            'role'        => 'simple_user',
            'manager_id'  => $drhDirecteur->id,
            'domain'      => 'local',
        ]);

        // CHEFS DEPARTEMENT DRH (j12, j13)
        $drhChefDept1 = User::create([
            'first_name'  => 'Paul',
            'last_name'   => 'MANAGER',
            'user_name'   => 'j12',
            'email'       => 'manager12@local.test',
            'password'    => $password,
            'poste'       => 'Chef-Departement',
            'entity_name' => $entiteDRH,
            'sous_direction' => $sousDirProd,
            'departement' => $deptDev,
            'service'     => $serviceMgt,
            'role'        => 'simple_user',
            'manager_id'  => $drhSousDir1->id,
            'domain'      => 'local',
        ]);

        $drhChefDept2 = User::create([
            'first_name'  => 'Paul',
            'last_name'   => 'MANAGER',
            'user_name'   => 'j13',
            'email'       => 'manager13@local.test',
            'password'    => $password,
            'poste'       => 'Chef-Departement',
            'entity_name' => $entiteDRH,
            'sous_direction' => $sousDirProd,
            'departement' => $deptDev,
            'service'     => $serviceMgt,
            'role'        => 'simple_user',
            'manager_id'  => $drhSousDir2->id,
            'domain'      => 'local',
        ]);

        // CHEFS SERVICE DRH (j14, j15)
        $drhChefService1 = User::create([
            'first_name'  => 'Paul',
            'last_name'   => 'MANAGER',
            'user_name'   => 'j14',
            'email'       => 'manager14@local.test',
            'password'    => $password,
            'poste'       => 'Chef-Service',
            'entity_name' => $entiteDRH,
            'sous_direction' => $sousDirProd,
            'departement' => $deptDev,
            'service'     => $serviceMgt,
            'role'        => 'simple_user',
            'manager_id'  => $drhChefDept1->id,
            'domain'      => 'local',
        ]);

        $drhChefService2 = User::create([
            'first_name'  => 'Paul',
            'last_name'   => 'MANAGER',
            'user_name'   => 'j15',
            'email'       => 'manager15@local.test',
            'password'    => $password,
            'poste'       => 'Chef-Service',
            'entity_name' => $entiteDRH,
            'sous_direction' => $sousDirProd,
            'departement' => $deptDev,
            'service'     => $serviceMgt,
            'role'        => 'simple_user',
            'manager_id'  => $drhChefDept2->id,
            'domain'      => 'local',
        ]);

        // CAS PARTICULIER (j16)
        User::create([
            'first_name'  => 'Paul',
            'last_name'   => 'MANAGER',
            'user_name'   => 'j16',
            'email'       => 'manager16@local.test',
            'password'    => $password,
            'poste'       => 'Chef-Departement',
            'entity_name' => $entiteDRH,
            'sous_direction' => $sousDirProd,
            'departement' => $deptDev,
            'service'     => $serviceMgt,
            'role'        => 'simple_user',
            'manager_id'  => $drhChefService1->id,
            'domain'      => 'local',
        ]);
    }
}