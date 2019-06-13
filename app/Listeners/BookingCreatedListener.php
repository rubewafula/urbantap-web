<?php

namespace App\Listeners;

use App\Contracts\ShouldSendMail;
use App\Contracts\ShouldSendSMS;
use App\Events\BookingCreated;
use App\Notifications\BookingCreatedNotification;
use App\Traits\ProviderDataTrait;
use App\Traits\SendEmailTrait;
use App\Traits\SendSMSTrait;
use App\Traits\UserDataTrait;
use App\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Class BookingCreatedListener
 * @package App\Listeners
 */
class BookingCreatedListener implements ShouldSendSMS, ShouldSendMail
{
    use SendSMSTrait, SendEmailTrait, ProviderDataTrait, UserDataTrait;

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
     * @param BookingCreated $event
     * @return void
     * @throws \Exception
     */
    public function handle(BookingCreated $event)
    {
        $data = $event->data;
        // Send user email
        $this->send($this->getUserNotificationData($event->user, $data), $this->userMailTemplate);

        // Send service provider mail, notification and sms
        $data['user'] = $event->user->toArray();
        [
            $data,
            $serviceProvider,
        ] = $this->getServiceProviderNotificationData($data);
        Log::info("Found service provider", $serviceProvider);
        Log::info("Final service provider data", $data);
        // Send SP mail
        $this->send($data, $this->serviceProviderMailTemplate);
        // Notify SP
        $serviceProvider->notify(new BookingCreatedNotification([
            'user'             => $event->user->toArray(),
            'booking_id'       => $data['booking_id'],
            'service_provider' => $serviceProvider->toArray(),
        ]));
        // Send SMS
        if (!is_null($data['msisdn'])) {
            $this->sms(
                array_merge(
                    Arr::get($data, 'sms'),
                    [
                        'message'             => "Booking Request. " . $data['service_name']
                            . " Start Time: " . $data['booking_time'] . ", Cost " . $data['cost']
                            . " Confirm this request within 15 Minutes to reserve the slot. Urbantap",
                        'reference'           => $data['booking_id'],
                        'user_id'             => $data['request']['user_id'],
                        'service_provider_id' => $data['request']['service_provider_id']
                    ]
                )
            );
        }
    }

    /**
     * @param array $data
     * @return string
     */
    protected function getNotificationMessage(array $data): ?string
    {
        return sprintf("BOOKING Request received from %s FOR %s Service ", Arr::get($data, 'user.first_name'), Arr::get($data, 'provider.service_name'));
    }

    /**
     * @param User $user
     * @param array $data
     * @return string
     */
    protected function getUserNotificationMessage(User $user, array $data): string
    {
        $message = sprintf("BOOKING Request received from %s FOR %s Service ", $user->first_name, Arr::get($data, 'provider.service_name'));
        $message .= "<br/>";
        return $message;
    }


}
