<?php

namespace App\Mail;

use App\Models\EmailConversation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConversationReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly EmailConversation $conversation,
        public readonly string $body,
        public readonly User $admin,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [config('mail.from.address')],
            subject: 'Re: ' . $this->conversation->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.conversation-reply',
        );
    }
}
