<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialExpired extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $companyName,
        public string $ownerName,
        public string $tenantSlug
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Deneme sureniz sona erdi - Lattessa',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.trial-expired',
        );
    }
}
