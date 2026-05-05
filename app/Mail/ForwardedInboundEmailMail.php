<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ForwardedInboundEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $from,
        public readonly string $to,
        public readonly string $subject,
        public readonly ?string $text,
        public readonly ?string $html,
        public readonly array $attachments = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Fwd: {$this->subject}",
            replyTo: [$this->from],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.forwarded-inbound',
        );
    }

    public function attachments(): array
    {
        return $this->attachments;
    }
}
