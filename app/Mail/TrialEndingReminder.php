<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialEndingReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $companyName,
        public string $ownerName,
        public int $daysLeft,
        public string $tenantSlug
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Deneme sureniz {$this->daysLeft} gun sonra bitiyor - Lattessa",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.trial-ending',
        );
    }
}
