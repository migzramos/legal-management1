<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewMessage extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Message $message;
    public string $link;

    public function __construct(Message $message, string $link)
    {
        $this->message = $message;
        $this->link = $link;
    }

    public function build()
    {
        return $this->subject('New Message from ' . $this->message->sender->name)
                    ->view('emails.new-message');
    }
}
