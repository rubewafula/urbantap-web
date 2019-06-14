<?php

namespace App\Mail;

use Illuminate\Queue\SerializesModels;

/**
 * Class BookingCreated
 * @package App\Mail
 */
class BookingCreated extends Mailable
{
    use SerializesModels;

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
