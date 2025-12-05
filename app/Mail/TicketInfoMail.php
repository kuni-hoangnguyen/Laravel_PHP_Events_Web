<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketInfoMail extends Mailable
{
    use Queueable, SerializesModels;

    public Ticket $ticket;
    public string $qrCode;
    public string $qrImageUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Ticket $ticket, string $qrCode, string $qrImageUrl)
    {
        $this->ticket = $ticket;
        $this->qrCode = $qrCode;
        $this->qrImageUrl = $qrImageUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->ticket->attendee->email],
            subject: 'Thông tin vé - '.($this->ticket->ticketType->event->title ?? 'Seniks Events'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-info',
            with: [
                'ticket' => $this->ticket,
                'qrCode' => $this->qrCode,
                'qrImageUrl' => $this->qrImageUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
