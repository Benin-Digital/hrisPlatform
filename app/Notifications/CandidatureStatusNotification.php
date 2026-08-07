<?php

namespace App\Notifications;

use App\Models\Candidature;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// ✅ On retire "implements ShouldQueue" et le trait Queueable
class CandidatureStatusNotification extends Notification
{
    protected $candidature;
    protected $message;

    public function __construct(Candidature $candidature, $message = null)
    {
        $this->candidature = $candidature;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $subject = match ($this->candidature->statut) {
            'entretien_planifie' => '📅 Entretien planifié',
            'accepte'            => '✅ Félicitations !',
            'refuse'             => '❌ Candidature refusée',
            default              => '📩 Mise à jour de votre candidature',
        };

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Bonjour ' . $this->candidature->prenom . ' ' . $this->candidature->nom . ',')
            ->line($this->message ?? 'Votre candidature a été mise à jour.')
            // ->action('Voir le suivi', route('candidatures.public.show', $this->candidature->id))
            ->line('Merci de votre confiance.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'candidature_id' => $this->candidature->id,
            'statut' => $this->candidature->statut,
            'message' => $this->message,
        ];
    }
}