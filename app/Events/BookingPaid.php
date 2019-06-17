<?php

namespace App\Events;

use App\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class BookingPaid
 * @package App\Events
 */
class BookingPaid
{
    use Dispatchable, SerializesModels;

    /**
     * @var Booking
     */
    public $booking;
    /**
     * @var array
     */
    public $data;

    /**
     * Create a new event instance.
     *
     * @param Booking $booking
     * @param array $data
     */
    public function __construct(Booking $booking, array $data)
    {
        $this->booking = $booking;
        $this->data = $data;
    }
}
