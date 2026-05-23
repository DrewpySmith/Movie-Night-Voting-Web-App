<?php

namespace App\Jobs;

use App\Mail\InvitationMail;
use App\Models\Invitation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendInviteNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Invitation $invitation
    ) {}

    public function handle(): void
    {
        if (!$this->invitation->invitee_email) {
            return;
        }

        Mail::to($this->invitation->invitee_email)
            ->send(new InvitationMail($this->invitation));
    }
}
