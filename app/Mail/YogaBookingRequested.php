<?php

namespace App\Mail;

use App\Models\YogaBooking;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** New Yoga Reset request, sent to the resort team. */
class YogaBookingRequested extends Mailable
{
    public function __construct(public YogaBooking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Yoga Reset request {$this->booking->reference} – {$this->booking->option_title}",
            replyTo: [$this->booking->email],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.yoga-booking-requested');
    }
}
