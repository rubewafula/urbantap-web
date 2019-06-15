<?php

namespace App\Policies;

use App\Booking;
use App\User;
use App\Utilities\DBStatus;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class BookingPolicy
 * @package App\Policies
 */
class BookingPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * - User must be service provider
     * - Status should not be accepted
     *
     * @param User $user
     * @param Booking $booking
     * @return bool
     */
    public function reject(User $user, Booking $booking)
    {
        return $user->id === $booking->provider->user_id && $booking->status_id === DBStatus::BOOKING_NEW;
    }

    /**
     * - User must be service provider
     * - Status should not be accepted
     *
     * @param User $user
     * @param Booking $booking
     * @return bool
     */
    public function accept(User $user, Booking $booking)
    {
        return $this->reject($user, $booking);
    }
}
