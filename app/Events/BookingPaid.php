<?php

namespace App\Events;

use App\User;
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
     * @var array
     */
    public $data;
    /**
     * @var User
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param array $data
     * @param User $user
     */
    public function __construct(array $data, User $user)
    {
        $this->data = $data;
        $this->user = $user;
    }
}
