<?php

namespace App\Mail;

/**
 * Class BookingWasPaid
 * @package App\Mail
 */
class BookingWasPaid extends Mailable
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
        return 'emails.bookings.booking-was-paid';
    }
}
