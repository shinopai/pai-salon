<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
        public string $cancellationToken,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '予約確認のお知らせ',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservations.confirmation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
