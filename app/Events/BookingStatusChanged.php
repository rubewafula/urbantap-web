<?php

namespace App\Events;

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

    /**
     * @var array
     */
    public $notifiable = [];

    /**
     * Create a new event instance.
     *
     * @param array $data
     * @param int $status
     * @param array $notifiable
     */
    public function __construct(array $data, int $status, array $notifiable)
    {
        $this->data = $data;
        $this->status = $status;
        $this->notifiable = $notifiable;
    }
}
