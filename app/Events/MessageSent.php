<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;

class MessageSent implements \Illuminate\Contracts\Broadcasting\ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        // Broadcast à la fois sur le canal de l'entité (groupe) et le canal privé du destinataire
        $channels = [];
        
        if ($this->message->espace_id) {
            $channels[] = new PrivateChannel('espace.' . $this->message->espace_id);
        } elseif ($this->message->destinataire_id) {
            $channels[] = new PrivateChannel('user.' . $this->message->destinataire_id);
            $channels[] = new PrivateChannel('user.' . $this->message->utilisateur_id);
        } else {
            $channels[] = new PrivateChannel('entite.' . $this->message->entite_id);
        }

        return $channels;
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }
}