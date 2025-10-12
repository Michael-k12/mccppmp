<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SecurityAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subjectLine;
    public $details;

    public function __construct($subjectLine, $details)
    {
        $this->subjectLine = $subjectLine;
        $this->details = $details;
    }

    public function build()
    {
        return $this->subject($this->subjectLine)
                    ->view('emails.security-alert');
    }
}
