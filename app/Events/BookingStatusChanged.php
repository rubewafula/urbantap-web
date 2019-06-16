<?php

namespace App\Events;

use App\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class BookingStatusChanged
 * @package App\Events
 */
class BookingStatusChanged
{
    use Dispatchable, SerializesModels;

    /**
     * @var Booking
     */
    public $booking;

    /**
     * Create a new event instance.
     *
     * @param Booking $booking
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }
}
