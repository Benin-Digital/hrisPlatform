<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class RappelPointage extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Rappel : Pointage du jour')
            ->greeting("Bonjour {$notifiable->prenom},")
            ->line("Vous n'avez pas encore pointé votre arrivée aujourd'hui.")
            ->line("Pensez à le faire avant 8h30 pour éviter un retard.")
            ->action('Pointer maintenant', url('/pointage'));
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Rappel : pointage du jour',
            'type' => 'pointage',
            'url' => '/pointage',
        ];
    }
}