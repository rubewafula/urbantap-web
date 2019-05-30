<?php

namespace App\Listeners;

use App\Contracts\ShouldSendMail;
use App\Contracts\ShouldSendSMS;
use App\Events\BookingCreated;
use App\Notifications\BookingCreatedNotification;
use App\Traits\ProviderDataTrait;
use App\Traits\SendEmailTrait;
use App\Traits\SendSMSTrait;
use App\User;
use Illuminate\Support\Arr;

/**
 * Class BookingCreatedListener
 * @package App\Listeners
 */
class BookingCreatedListener implements ShouldSendSMS, ShouldSendMail
{
    use SendSMSTrait, SendEmailTrait, ProviderDataTrait;

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
            $notification
        ] = $this->getServiceProviderNotificationData($event->user, $data);
        // Send SP mail
        $this->send($data, $this->serviceProviderMailTemplate);
        // Notify SP
        $serviceProvider->notify(new BookingCreatedNotification([
            'message'          => $notification,
            'user'             => $event->user->toArray(),
            'booking_id'       => $data['booking_id'],
            'service_provider' => $serviceProvider->toArray(),
        ]));
        // Send SMS
        if (!is_null($data['business_phone'])) {
            $this->sms([
                'recipients'          => [$data['business_phone']],
                'message'             => "Booking Request. " . $data['service_name']
                    . " Start Time: " . $data['booking_time'] . ", Cost " . $data['cost']
                    . " Confirm this request within 15 Minutes to reserve the slot. Urbantap",
                'reference'           => $data['booking_id'],
                'user_id'             => $data['request']['user_id'],
                'service_provider_id' => $data['request']['service_provider_id']
            ]);
        }
    }

    /**
     * User notification data
     *
     * @param User $user
     * @param array $data
     * @return array
     */
    private function getUserNotificationData(User $user, array $data)
    {
        return array_merge(
            $data,
            [
                'to'                  => $user->email,
                'subject'             => Arr::get($data, 'subject'),
                'reference'           => Arr::get($data, 'booking_id'),
                'user_id'             => $user->id,
                'service_provider_id' => Arr::get($data, 'request.service_provider_id'),
            ]
        );
    }

    /**
     * @param User $user
     * @param array $data
     * @return array
     * @throws \Exception
     */
    protected function getServiceProviderNotificationData(User $user, array $data): array
    {
        $sp = $this->queryData($data);
        return [
            array_merge(
                $data,
                [
                    'provider' => (array)$sp
                ]
            ),
            new User(['id' => $sp->user_id]),
            sprintf("BOOKING Request received from %s FOR %s Service ", $user->first_name, $sp->service_name)
        ];
    }
}
