<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NouveauMemoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $memo;
    public $sender;

    /**
     * Create a new message instance.
     */
    public function __construct($memo, $sender)
    {
        $this->memo = $memo;
        $this->sender = $sender;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('🔔 Nouveau Mémo : ' . $this->memo->object)
                    ->view('emails.nouveau_memo'); // On va créer cette vue à l'étape 2
    }
}