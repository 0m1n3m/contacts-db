<?php

namespace App\Mail;

use App\Models\UserInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public UserInvitation $invitation,
        public string $acceptUrl
    ) {}

    public function build()
    {
        return $this
            ->subject('Invitación a Contacts DB')
            ->view('emails.user-invitation');
    }
}