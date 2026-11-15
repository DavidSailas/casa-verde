<?php

namespace App\Mail;

use App\Models\YogaBooking;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** "We got your request" email to the guest. */
class YogaBookingReceived extends Mailable
{
    public function __construct(public YogaBooking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "We received your Yoga Reset request {$this->booking->reference} – Casa Verde Cliff Resort & Spa");
    }

    public function content(): Content
    {
        return new Content(view: 'mail.yoga-booking-received');
    }
}
