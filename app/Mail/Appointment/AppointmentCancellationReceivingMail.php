<?php

namespace App\Mail\Appointment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentCancellationReceivingMail extends Mailable
{
    use Queueable, SerializesModels;

    public $recipientName;
    public $bookingDetails;
    public $recipientType; // 'coach' or 'admin'

    /**
     * Create a new message instance.
     */
    public function __construct($recipientName, $bookingDetails, $recipientType = 'coach')
    {
        $this->recipientName = $recipientName;
        $this->bookingDetails = $bookingDetails;
        $this->recipientType = $recipientType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->recipientType === 'admin' 
            ? 'Appointment Cancelled - Admin Notification'
            : 'Appointment Cancelled - Coach Notification';
            
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'backend.mail.appointment-cancellation-receiving',
            with: [
                'recipientName' => $this->recipientName,
                'bookingDetails' => $this->bookingDetails,
                'recipientType' => $this->recipientType,
            ]
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
