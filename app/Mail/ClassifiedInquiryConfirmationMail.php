<?php

namespace App\Mail;

use App\Models\Classified;
use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClassifiedInquiryConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Inquiry $inquiry,
        public readonly Classified $classified,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your inquiry about {$this->classified->title} has been sent",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.classified-inquiry-confirmation',
        );
    }
}