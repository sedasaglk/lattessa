<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $customerName,
        public string $companyName,
        public string $serviceName,
        public string $staffName,
        public string $date,
        public string $time,
        public string $price,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Randevunuz Onaylandı - {$this->companyName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment-confirmation',
        );
    }
}
