<?php

namespace App\Mail;

/**
 * Class BookingRejected
 * @package App\Mail
 */
class BookingRejected extends Mailable
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
     * @return string
     */
    function getMailTemplate(): string
    {
        return 'emails.booking.rejected';
    }
}
