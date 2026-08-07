<?php

namespace App\Mail;

use App\Models\Utilisateur;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BienvenueUtilisateur extends Mailable
{
    use Queueable, SerializesModels;

    public $utilisateur;
    public $password;

    public function __construct(Utilisateur $utilisateur, string $password)
    {
        $this->utilisateur = $utilisateur;
        $this->password = $password;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vos identifiants de connexion - Bienvenue !',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.bienvenue',
        );
    }
}