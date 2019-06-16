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
     * @return string
     */
    function getMailTemplate(): string
    {
        return 'emails.booking.created';
    }
}
