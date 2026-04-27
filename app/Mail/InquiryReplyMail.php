<?php

namespace App\Mail;

use App\Models\Inquiry;
use App\Models\InquiryReply;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InquiryReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Inquiry $inquiry,
        public readonly InquiryReply $reply,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: $this->reply->admin?->email ?? config('mail.from.address'),
            subject: "Re: Your inquiry about {$this->inquiry->animal?->pet_name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.inquiry-reply',
        );
    }
}
