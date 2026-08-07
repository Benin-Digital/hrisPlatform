<?php

namespace App\Notifications;

use App\Models\Tache;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TacheAssignee extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected $tache;

   public function __construct($tache)
{
    if (is_object($tache) && property_exists($tache, 'titre')) {
        // C'est un objet générique (stdClass) pour les tests
        $this->tache = $tache;
    } else {
        $this->tache = $tache;
    }
}

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'tache_id' => $this->tache->id,
            'titre' => $this->tache->titre,
            'message' => "Vous avez été assigné à la tâche : {$this->tache->titre}",
            'url' => route('taches.show', $this->tache->id),
            'type' => 'assignation_tache',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'tache_id' => $this->tache->id,
            'titre' => $this->tache->titre,
            'message' => "Nouvelle tâche assignée : {$this->tache->titre}",
            'type' => 'assignation_tache',
        ]);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->tache->assigne_a);
    }
}
