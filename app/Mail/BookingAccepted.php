<?php

namespace App\Mail;

use Illuminate\Queue\SerializesModels;

class BookingAccepted extends Mailable
{
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.booking.accepted', $this->data);
    }
}
