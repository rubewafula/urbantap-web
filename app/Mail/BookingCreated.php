<?php

namespace App\Mail;

/**
 * Class BookingCreated
 * @package App\Mail
 */
class BookingCreated extends Mailable
{
    /**
     * BookingCreated constructor.
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
        return $this->markdown('emails.booking.created', $this->data);
    }
}
