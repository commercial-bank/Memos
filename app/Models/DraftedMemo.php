<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Prunable;
use App\Notifications\BrouillonExpireNotification;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class DraftedMemo extends Model
{
     
    use Prunable;

    protected $fillable = [
        'object',
        'reference',
        'concern',
        'content',
        'status',
        
        // GESTION DES DÉTENTEURS
        'current_holders',   // L'ID du détenteur actuel (ou tableau d'IDs si JSON)
        'previous_holders',    // L'historique des anciens (Array/JSON)
        
        // SIGNATURES & WF
        'qr_code',
        'workflow_direction', 

        //Pieces Jointe
        'pieces_jointes',

        //destinataires
        'destinataires',
        
        'user_id',

        'parent_id',

       
    ];



    protected $casts = [
        // ⚠️ IMPORTANT : La colonne 'current_holder_id' doit être de type JSON dans la migration
        // si tu veux que ce cast fonctionne. Sinon, retire cette ligne.
        'current_holders' => 'array',

        // Convertit automatiquement le JSON de la BDD en tableau PHP
        'previous_holders'  => 'array',

        // Conversion automatique JSON <-> Array
        'pieces_jointes' => 'array',
        'destinataires' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Définir la requête pour les modèles qui doivent être supprimés (élagués).
     */
    public function prunable(): Builder
    {
        // On supprime les enregistrements créés il y a plus de 5 minutes
        return static::where('created_at', '<=', now()->subMinutes(1))
                     ->where('status', 'brouillon'); 
    }

    /**
     * (Optionnel) Préparer le modèle avant la suppression.
     * Utile si vous devez supprimer des fichiers liés (pièces jointes) avant de supprimer la ligne.
     */
    protected function pruning(): void
    {
        // 1. Récupération User & Notifications
        $user = \App\Models\User::find($this->user_id);
        
        if ($user) {
            try {
                $user->notify(new BrouillonExpireNotification($this->object));
                $this->sendDeletionEmail($user);
            } catch (\Exception $e) {
                // On capture l'erreur pour ne pas bloquer la suppression
                \Illuminate\Support\Facades\Log::error("Erreur notif pruning: " . $e->getMessage());
            }
        }

        // 2. Nettoyage Fichiers
        if (!empty($this->pieces_jointes)) {
            foreach ($this->pieces_jointes as $file) {
                $path = is_string($file) ? $file : ($file['path'] ?? null);
                if ($path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
                }
            }
        }

        // 3. (IMPORTANT) Supprimer les relations bloquantes
        // Si vous avez un modèle Historique ou autre lié à ce brouillon :
        // $this->historiques()->delete(); 
        
        // Supprimer aussi les notifications database liées à ce mémo si nécessaire
        // DB::table('notifications')->where('data->memo_id', $this->id)->delete(); 
    }

     /**
     * Envoie l'email de suppression via PHPMailer
     */
    private function sendDeletionEmail($user)
    {
        if (empty($user->email)) return;

        try {
            $mail = new PHPMailer(true);

            // Configuration SMTP (Identique à votre configuration)
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST', 'smtp.gie.local');
            $mail->SMTPAuth = false;
            $mail->Port = env('MAIL_PORT', 25);
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
            $mail->CharSet = 'UTF-8';
            
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            // Expéditeur
            $mail->setFrom(
                env('MAIL_FROM_ADDRESS', 'cbc_infos@groupecommercialbank.com'),
                env('MAIL_FROM_NAME', 'CBC MEMOS')
            );

            // Destinataire
            $mail->addAddress($user->email, $user->first_name . ' ' . $user->last_name);

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = "Expiration de brouillon : " . $this->object;
            $mail->Body = $this->buildDeletionEmailBody($user);
            $mail->AltBody = "Bonjour {$user->first_name}, votre brouillon '{$this->object}' a été supprimé automatiquement car le délai de 5 minutes est dépassé.";

            $mail->send();
            
            Log::info("Email de suppression envoyé à {$user->email} pour le brouillon #{$this->id}");

        } catch (PHPMailerException $e) {
            Log::error("Erreur envoi email suppression brouillon #{$this->id}: {$mail->ErrorInfo}");
        } catch (\Exception $e) {
            Log::error("Erreur générale email suppression: " . $e->getMessage());
        }
    }

    /**
     * Construit le HTML de l'email de suppression
     */
    private function buildDeletionEmailBody($user)
    {
        $userName = $user->first_name . ' ' . $user->last_name;
        $dateCreation = $this->created_at->format('d/m/Y à H:i');
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; }
                .header { background-color: #ef4444; padding: 20px; text-align: center; } /* Rouge pour l'alerte */
                .header h1 { color: #ffffff; margin: 0; font-size: 20px; text-transform: uppercase; }
                .content { padding: 30px; background: #fdf2f2; } /* Fond rougeâtre clair */
                .memo-box { background: white; border-left: 4px solid #ef4444; padding: 20px; margin: 20px 0; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
                .info-label { font-weight: bold; color: #7f1d1d; font-size: 12px; text-transform: uppercase; }
                .info-value { color: #111827; margin-bottom: 10px; }
                .footer { background: #1f2937; color: #9ca3af; padding: 15px; text-align: center; font-size: 11px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🗑️ Brouillon Supprimé</h1>
                </div>
                
                <div class='content'>
                    <p>Bonjour <strong>{$userName}</strong>,</p>
                    
                    <p>Le système a procédé au nettoyage automatique de vos brouillons.</p>
                    <p>Le document suivant a dépassé la durée de vie autorisée (5 minutes) sans être transmis :</p>
                    
                    <div class='memo-box'>
                        <div class='info-label'>Objet</div>
                        <div class='info-value'>{$this->object}</div>
                        
                        <div class='info-label'>Créé le</div>
                        <div class='info-value'>{$dateCreation}</div>

                        <div class='info-label'>Raison</div>
                        <div class='info-value'>Délai d'inactivité dépassé.</div>
                    </div>
                    
                    <p style='font-size: 13px; color: #6b7280;'>
                        Si vous souhaitez conserver ce mémo, vous devrez le rédiger à nouveau.
                    </p>
                </div>
                
                <div class='footer'>
                    <p><strong>Commercial Bank Cameroun</strong> - CBC MEMOS</p>
                    <p>Ceci est une notification automatique.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
