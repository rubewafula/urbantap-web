<?php

namespace App\Listeners;

use App\Events\BookingStatusChanged;
use App\Notifications\BookingAcceptedNotification;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\BookingRejectedNotification;
use App\Utilities\DBStatus;

/**
 * Class BookingStatusChangedListener
 * @package App\Listeners
 */
class BookingStatusChangedListener extends BookingBaseListener
{
    /**
     * @var string
     */
    private $userMailTemplate = "booking.email.blade.html";
    /**
     * @var string
     */
    private $serviceProviderMailTemplate = "booking.email.blade.html";

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
     * @throws \Exception
     */
    public function handle(BookingStatusChanged $event)
    {
        // Send provider notification
        if (in_array($event->status, [DBStatus::BOOKING_ACCEPTED, DBStatus::BOOKING_REJECTED])) {
            $data = $this->getUserNotificationData($event->user, $event->data);
            switch ($event->status) {
                case DBStatus::BOOKING_ACCEPTED:
                    $event->user->notify(new BookingAcceptedNotification($event->data));
                    break;
                case DBStatus::BOOKING_REJECTED:
                    $event->user->notify(new BookingRejectedNotification($event->data));
                    break;
            }
            $this->send($data, $this->userMailTemplate);
        } else {
            [
                $data,
                $serviceProvider,
            ] = $this->getServiceProviderNotificationData($event->data);
            $serviceProvider->notify(new BookingCancelledNotification($data));
        }
    }


    /**
     * @param array $data
     * @return string
     */
    protected function getNotificationMessage(array $data): string
    {
        return "Booking status changed";
    }
}
