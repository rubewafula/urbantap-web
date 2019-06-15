<?php

namespace App\Mail;

/**
 * Class BookingCancelledProvider
 * @package App\Mail
 */
class BookingCancelledProvider extends Mailable
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
        return 'emails.booking.cancelled-provider';
    }
}
