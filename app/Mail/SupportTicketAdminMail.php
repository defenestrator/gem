<?php

namespace App\Mail;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportTicketAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly SupportTicket $ticket,
        public readonly bool $isNewUser,
    ) {}

    public function envelope(): Envelope
    {
        $typeLabel = $this->ticket->type === 'suggestion' ? 'Suggestion' : 'Bug Report';
        return new Envelope(
            subject: "New {$typeLabel}: {$this->ticket->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.support-ticket-admin',
        );
    }
}
