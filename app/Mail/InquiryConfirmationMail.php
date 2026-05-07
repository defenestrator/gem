<?php

namespace App\Mail;

use App\Models\Animal;
use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InquiryConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Inquiry $inquiry,
        public readonly Animal $animal,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your inquiry about {$this->animal->pet_name} [{$this->animal->slug}] has been sent",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.inquiry-confirmation',
        );
    }
}