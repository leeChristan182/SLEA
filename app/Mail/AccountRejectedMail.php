<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $rejectionReason;

    public function __construct(User $user, string $rejectionReason = '')
    {
        $this->user = $user;
        $this->rejectionReason = $rejectionReason;
    }

    public function build()
    {
        return $this->subject('Your SLEA Account Registration - Action Required')
            ->markdown('emails.account_rejected');
    }
}


