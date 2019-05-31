<?php

namespace App\Listeners;

use App\Contracts\ShouldSendMail;
use App\Contracts\ShouldSendSMS;
use App\Events\BookingStatusChanged;
use App\Notifications\BookingAcceptedNotification;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\BookingRejectedNotification;
use App\Traits\ProviderDataTrait;
use App\Traits\SendEmailTrait;
use App\Traits\SendSMSTrait;
use App\Traits\UserDataTrait;
use App\Utilities\DBStatus;

/**
 * Class BookingStatusChangedListener
 * @package App\Listeners
 */
class BookingStatusChangedListener implements ShouldSendMail, ShouldSendSMS
{
    use SendEmailTrait, SendSMSTrait, ProviderDataTrait, UserDataTrait;
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
                $notificationMessage
            ] = $this->getServiceProviderNotificationData($event->data);
            $serviceProvider->notify(new BookingCancelledNotification($data));
        }
    }

    /**
     * @return string
     */
    protected function getProviderFromClause()
    {
        return " from service_providers sp inner join provider_services ps on "
            . " sp.id = ps.service_provider_id  inner join services s on s.id = ps.service_id "
            . " inner join bookings b on b.provider_service_id = ps.id "
            . "  where b.id = :booking_id ";
    }

    /**
     * Query bindings
     *
     * @param array $data
     * @return array
     */
    protected function getProviderBindings(array $data): array
    {
        return [
            'booking_id' => $data['booking_id']
        ];
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
