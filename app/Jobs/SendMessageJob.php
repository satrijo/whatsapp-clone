<?php

namespace App\Jobs;

use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;


    protected $messageData;

    public function __construct(array $messageData)
    {
        $this->messageData = $messageData;
    }

    public function handle()
    {
        try {
            $message = Message::create($this->messageData);

            broadcast(new MessageSent($message));

            Log::info('Message processed successfully', [
                'message_id' => $message->id,
                'chatroom_id' => $message->chatroom_id
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing message in queue', [
                'error' => $e->getMessage(),
                'messageData' => $this->messageData
            ]);

            throw $e; // Re-throw untuk trigger retry mechanism
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Failed to process message', [
            'error' => $exception->getMessage(),
            'messageData' => $this->messageData
        ]);
    }
}
