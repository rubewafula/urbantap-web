<?php

namespace App\Mail;

/**
 * Class BookingCreatedProvider
 * @package App\Mail
 */
class BookingCreatedProvider extends Mailable
{
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
        return $this->markdown('emails.booking.created-provider', $this->data);
    }
}
