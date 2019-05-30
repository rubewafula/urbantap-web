<?php

namespace App\Events;

use App\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class BookingCreated
 * @package App\Events
 */
class BookingCreated
{
    use Dispatchable, SerializesModels;

    /**
     * @var User
     */
    public $user;
    /**
     * @var array
     */
    public $data;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param array $data
     */
    public function __construct(User $user, array $data)
    {
        $this->user = $user;
        $this->data = $data;
    }
}
