<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class BookingCreatedProvider
 * @package App\Mail
 */
class BookingCreatedProvider extends Mailable
{
    use SerializesModels;

    /**
     * @var array
     */
    private $data = [];

    /**
     * Create a new message instance.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
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
