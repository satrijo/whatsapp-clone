<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserEnteredChatroom implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chatroom;

    public $user;

    public function __construct($chatroom, $user)
    {
        $this->chatroom = $chatroom;
        $this->user = $user;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chatroom.'.$this->chatroom->id);
    }
}
