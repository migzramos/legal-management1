<?php

namespace App\Mail;

use App\Models\LegalCase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CaseStatusUpdated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public LegalCase $case;
    public string $oldStatus;
    public string $newStatus;

    public function __construct(LegalCase $case, string $oldStatus, string $newStatus)
    {
        $this->case = $case;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function build()
    {
        return $this->subject('Your Case Status Has Been Updated')
                    ->view('emails.case-status-updated');
    }
}
