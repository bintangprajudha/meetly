<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message_id;
    public $sender_id;

    public function __construct($message_id, $sender_id)
    {
        $this->message_id = $message_id;
        $this->sender_id = $sender_id;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->sender_id);
    }

    public function broadcastWith()
    {
        return [
            'message_id' => $this->message_id,
            'status' => 'read'
        ];
    }
}
