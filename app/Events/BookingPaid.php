<?php

namespace App\Events;

use App\Booking;
use App\Transaction;
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

    public $transaction;

    /**
     * Create a new event instance.
     *
     * @param Booking $booking
     * @param array $data
     * @param Transaction $transaction
     */
    public function __construct(Booking $booking, array $data, Transaction $transaction)
    {
        $this->booking = $booking;
        $this->data = $data;
        $this->transaction = $transaction;
    }
}
