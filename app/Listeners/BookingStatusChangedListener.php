<?php

namespace App\Listeners;

use App\Events\BookingStatusChanged;
use App\Notifications\BookingAcceptedNotification;
use App\Notifications\BookingRejectedNotification;
use App\Utilities\DBStatus;
use Illuminate\Support\Facades\Notification;

class BookingStatusChangedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param BookingStatusChanged $event
     * @return void
     */
    public function handle(BookingStatusChanged $event)
    {
        switch ($event->status) {
            case DBStatus::BOOKING_ACCEPTED:
                Notification::send($event->notifiable, new BookingAcceptedNotification($event->data));
                break;
            case DBStatus::BOOKING_REJECTED:
                Notification::send($event->notifiable, new BookingRejectedNotification($event->data));
                break;
        }
    }
}
