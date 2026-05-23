<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invitation $invitation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->invitation->inviter->name . ' invited you to ' . $this->invitation->room->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.invitation',
            with: [
                'inviter' => $this->invitation->inviter,
                'room' => $this->invitation->room,
                'acceptUrl' => url("/invitations/{$this->invitation->token}/accept"),
                'inviteCode' => $this->invitation->room->invite_code,
                'expiresAt' => $this->invitation->expires_at?->format('M j, Y') ?? '7 days',
            ],
        );
    }

}

