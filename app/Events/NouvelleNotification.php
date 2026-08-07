<?php

namespace App\Events;

use App\Models\Utilisateur;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NouvelleNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;
    public $destinataire_id;

    public function __construct($notification, $destinataire_id)
    {
        $this->notification = $notification;
        $this->destinataire_id = $destinataire_id;
    }

    /**
     * Canaux de diffusion.
     */
    public function broadcastOn(): array
    {
        $channels = [];

        // 1. Canal privé du destinataire
        $channels[] = new PrivateChannel('App.Models.Utilisateur.' . $this->destinataire_id);

        // 2. Canaux privés pour chaque super admin (sauf si le destinataire en est déjà un)
        $superAdmins = Utilisateur::whereHas('roles', function ($q) {
            $q->where('nom', 'super_admin');
        })->pluck('id')->toArray();

        foreach ($superAdmins as $adminId) {
            if ($adminId != $this->destinataire_id) {
                $channels[] = new PrivateChannel('App.Models.Utilisateur.' . $adminId);
            }
        }

        return $channels;
    }

    /**
     * Nom de l'événement (doit correspondre à l'écoute frontend).
     */
    public function broadcastAs()
    {
        return 'NouvelleNotification';
    }

    /**
     * Données envoyées au frontend.
     */
    public function broadcastWith()
    {
        return [
            'id'       => $this->notification->id ?? uniqid(),
            'message'  => $this->notification->data['message'] ?? $this->notification->message ?? 'Nouvelle notification',
            'titre'    => $this->notification->data['titre'] ?? $this->notification->titre ?? 'Notification',
            'data'     => $this->notification->data ?? null,
        ];
    }
}