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
use App\Utilities\RabbitMQ;
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

        // Send SP mail
        $booking = array_get($data, 'booking');
        $provider = $booking->provider;
        $data['booking'] = $booking->toArray();
        $data['provider'] = $provider;

        $this->send([
            'email_address' => $email = $provider->business_email ?: $provider->user->email,
            'subject'       => Arr::get($data, 'subject'),
            'mailable'      => \App\Mail\BookingCreated::class,
            'data'          => []
        ], "");

//        Log::info("Booked Provider", compact('provider'));
//        $this->send($this->getUserNotificationData($event->user, $data), $this->userMailTemplate);
//
//        $this->send($data, $this->serviceProviderMailTemplate);
//        // Notify SP
//        $provider->user->notify(new BookingCreatedNotification([
//            'user'             => $event->user->toArray(),
//            'booking_id'       => $data['booking_id'],
//            'service_provider' => $provider->toArray(),
//        ]));
//        // Send SMS
//        if ($provider->business_phone) {
//            $this->sms(
//                [
//                    'message'             => "Booking Request. " . $booking->service->service_name
//                        . " Start Time: " . $booking->booking_time . ", Cost " . $booking->amount
//                        . " Confirm this request within 15 Minutes to reserve the slot. Urbantap",
//                    'reference'           => $data['booking_id'],
//                    'user_id'             => $user->id,
//                    'service_provider_id' => $provider->id
//                ]
//            );
//        }
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
