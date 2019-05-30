<?php

namespace App\Listeners;

use App\Contracts\ShouldSendMail;
use App\Contracts\ShouldSendSMS;
use App\Events\BookingStatusChanged;
use App\Notifications\BookingAcceptedNotification;
use App\Notifications\BookingRejectedNotification;
use App\Traits\ProviderDataTrait;
use App\Traits\SendEmailTrait;
use App\Traits\SendSMSTrait;
use App\User;
use App\Utilities\DBStatus;
use Illuminate\Support\Facades\Notification;

/**
 * Class BookingStatusChangedListener
 * @package App\Listeners
 */
class BookingStatusChangedListener implements ShouldSendMail, ShouldSendSMS
{
    use SendEmailTrait, SendSMSTrait, ProviderDataTrait;
    /**
     * @var string
     */
    private $userMailTemplate = "";
    /**
     * @var string
     */
    private $serviceProviderMailTemplate = "";

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




    /**
     * Get provider data
     *
     * @param User $user
     * @param array $data
     * @return array
     */
    protected function getServiceProviderNotificationData(User $user, array $data): array
    {
        // TODO: Implement getServiceProviderNotificationData() method.
    }
}
