<?php

namespace App\Events;

use App\Models\Message;
use App\Models\Appointment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;
    public ?Appointment $appointment;

    /**
     * Create a new event instance.
     * 
     * @param Message $message The message that was sent
     * @param Appointment|null $appointment Optional appointment associated with the message
     */
    public function __construct(Message $message, ?Appointment $appointment = null)
    {
        $this->message = $message;
        $this->appointment = $appointment ?? $message->appointment;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        if ($this->appointment) {
            return [
                new PrivateChannel('appointment.' . $this->appointment->id),
            ];
        }

        return [
            new PrivateChannel('user.' . $this->message->receiver_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $data = [
            'id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name,
            'receiver_id' => $this->message->receiver_id,
            'body' => $this->message->body,
            'created_at' => $this->message->created_at->toIso8601String(),
        ];

        if ($this->appointment) {
            $data['appointment_id'] = $this->appointment->id;
        }

        return $data;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
