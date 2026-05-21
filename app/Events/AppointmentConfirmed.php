<?php
namespace App\Events;

use App\Models\Appointment;
use App\Models\Invoice;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmed
{
    use InteractsWithSockets, SerializesModels;

    public Appointment $appointment;
    public Invoice $invoice;

    public function __construct(Appointment $appointment, Invoice $invoice)
    {
        $this->appointment = $appointment;
        $this->invoice = $invoice;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('appointments.' . $this->appointment->id);
    }
}
