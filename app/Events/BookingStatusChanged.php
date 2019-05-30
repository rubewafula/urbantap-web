<?php

namespace App\Events;

use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class BookingStatusChanged
 * @package App\Events
 */
class BookingStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * @var array
     */
    public $data;
    /**
     * @var int
     */
    public $status;

    public $user;

    /**
     * Create a new event instance.
     *
     * @param array $data
     * @param int $status
     * @param User $user
     */
    public function __construct(array $data, int $status, User $user)
    {
        $this->data = $data;
        $this->status = $status;
        $this->user = $user;
    }
}
