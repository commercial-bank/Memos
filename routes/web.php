<?php

use App\Livewire\Memos\Memos;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\VerificationController;

Route::view('/', 'pdf/t')->name('memo.store');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


// Route publique pour la vérification du QR Code
Route::get('/verifier-document/{token}', [VerificationController::class, 'verify'])
     ->name('memo.verify');    

Route::get('/memos/{id}/print', [MemoController::class, 'print'])->name('memos.print')->middleware('auth');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

Route::get('/test-email', function () {
    $mail = new PHPMailer(true);

    try {
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gie.local';
        $mail->SMTPAuth = false; // Pas d'authentification
        $mail->Port = 25;
        $mail->SMTPSecure = false; // Pas de TLS pour le port 25
        $mail->SMTPAutoTLS = false; // Désactiver TLS automatique
        $mail->CharSet = 'UTF-8';
        
        // Options SSL pour éviter les erreurs de certificat
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Debug (facultatif - pour voir les détails)
        // $mail->SMTPDebug = 2;

        // Expéditeur
        $mail->setFrom('cbc_infos@groupecommercialbank.com', 'CBC MEMOS');
        
        // Destinataire - METTEZ VOTRE EMAIL ICI
        $mail->addAddress('angahami@groupecommercialbank.com', 'Test User');

        // Contenu de l'email
        $mail->isHTML(true);
        $mail->Subject = 'Test Email CBC - ' . now()->format('d/m/Y H:i:s');
        $mail->Body = '
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .header { background-color: #004080; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>CBC MEMOS</h1>
                </div>
                <div class="content">
                    <h2>✅ Test d\'envoi réussi !</h2>
                    <p>Cet email confirme que la configuration SMTP fonctionne correctement.</p>
                    <p><strong>Serveur :</strong> smtp.gie.local</p>
                    <p><strong>Port :</strong> 25</p>
                    <p><strong>Date :</strong> ' . now()->format('d/m/Y H:i:s') . '</p>
                </div>
            </body>
            </html>
        ';
        $mail->AltBody = 'Test d\'envoi réussi ! Configuration SMTP fonctionnelle.';

        $mail->send();
        
        return '
            <h1 style="color: green;">✅ Email envoyé avec succès !</h1>
            <p>Vérifiez votre boîte mail : <strong>votre.email@groupecommercialbank.com</strong></p>
            <p>Serveur : smtp.gie.local</p>
            <p>Port : 25</p>
        ';
        
    } catch (Exception $e) {
        return "
            <h1 style='color: red;'>❌ Erreur lors de l'envoi</h1>
            <p><strong>Message d'erreur :</strong></p>
            <pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>{$mail->ErrorInfo}</pre>
        ";
    }
});


require __DIR__.'/auth.php';
