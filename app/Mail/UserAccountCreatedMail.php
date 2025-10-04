<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserAccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $userEmail;
    public $userPassword;

    public function __construct($userEmail, $userPassword)
    {
        $this->userEmail = $userEmail;
        $this->userPassword = $userPassword;
    }

    public function build()
    {
        return $this->subject('Your MCCPPMP Account Details')
                    ->view('emails.user_account_created');
    }
}
