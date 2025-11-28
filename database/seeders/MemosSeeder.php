<?php

namespace Database\Seeders;

use App\Models\Memo;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MemosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       
        
      

        // ==========================================
        // SCÉNARIO 1 : Un BROUILLON (Draft)
        // ==========================================
        // L'employé rédige mais n'a pas encore envoyé.
        Memo::create([
            'object'             => 'Idée pour la fête du personnel',
            'concern'            => 'Comité des fêtes',
            'content'            => "Bonjour,\n\nJe pensais qu'il serait intéressant d'organiser un barbecue...",
            'status'             => 'brouillon', // Valeur par défaut
            'current_holder_id'  => null, // Le dossier est toujours chez lui
            'previous_holder_id' => null,
            'signature_sd'       => null,
            'signature_dir'      => null,
            'qr_code'            => null,
            'workflow_comment'   => null,
            'user_id'            => 1,
        ]);

        

        // ==========================================
        // SCÉNARIO 3 : EN ATTENTE DIRECTEUR (Validé par Manager)
        // ==========================================
        // Le Manager a validé, c'est maintenant chez le Directeur (Jean).
        Memo::create([
            'object'             => 'Rapport trimestriel d\'activité',
            'concern'            => 'Direction Générale',
            'content'            => "Voici le résumé des activités du Q3.\nPoints clés :\n- Augmentation de la prod de 15%\n- Recrutement de 2 devs.\n\nEn attente de votre validation finale.",
            'status'             => 'en_cours_directeur',
            'current_holder_id'  => null, // C'est Jean qui a la main
            'previous_holder_id' => null,
            'signature_sd'       => 'signature_paul_manager.png', // Le manager a signé !
            'signature_dir'      => null,
            'qr_code'            => null,
            'workflow_comment'   => 'Rapport conforme, je valide.', // Commentaire du manager
            'user_id'            => 1, // C'est le manager qui a initié ce rapport
        ]);

        // ==========================================
        // SCÉNARIO 4 : REJETÉ (Avec commentaire)
        // ==========================================
        Memo::create([
            'object'             => 'Demande de télétravail 100%',
            'concern'            => 'RH',
            'content'            => 'Je souhaite passer en full remote à partir de demain.',
            'status'             => 'rejete',
            'current_holder_id'  => null, // Retour à l'envoyeur
            'previous_holder_id' => null,
            'signature_sd'       => null,
            'signature_dir'      => null,
            'qr_code'            => null,
            'workflow_comment'   => 'Impossible, la politique de l\'entreprise exige 2 jours sur site.',
            'user_id'            => 1,
        ]);

        // ==========================================
        // SCÉNARIO 5 : VALIDÉ (Terminé avec QR Code)
        // ==========================================
        // Tout le monde a signé.
        Memo::create([
            'object'             => 'Note de service : Fermeture annuelle',
            'concern'            => 'Tout le personnel',
            'content'            => 'L\'entreprise sera fermée du 25 décembre au 2 janvier inclus. Bonnes fêtes à tous.',
            'status'             => 'valide',
            'current_holder_id'  => null, // Archivé chez le créateur ou admin
            'previous_holder_id' => null,
            'signature_sd'       => 'signature_paul_manager.png',
            'signature_dir'      => 'signature_jean_directeur.png',
            'qr_code'            => 'QR_VALIDATION_TOKEN_789456123', // QR Code généré
            'workflow_comment'   => 'Validé pour diffusion immédiate.',
            'user_id'            => 1,
        ]);

        // ==========================================
        // BOUCLE : Générer 10 autres mémos aléatoires
        // ==========================================
        for ($i = 0; $i < 10; $i++) {
            Memo::create([
                'object'             => fake()->sentence(5),
                'concern'            => fake()->jobTitle(),
                'content'            => fake()->paragraphs(3, true), // Génère un long texte (string)
                'status'             => 'brouillon',
                'current_holder_id'  => null,
                'previous_holder_id' => null,
                'signature_sd'       => null,
                'signature_dir'      => null,
                'qr_code'            => null,
                'workflow_comment'   => null,
                'user_id'            => 1,
            ]);
    }
 }
}
