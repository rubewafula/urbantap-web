<?php

namespace App\Listeners;

use App\Contracts\ShouldSendMail;
use App\Contracts\ShouldSendSMS;
use App\Events\BookingCreated;
use App\Mail\BookingCreatedProvider;
use App\Notifications\BookingCreatedNotification;
use App\Traits\ProviderDataTrait;
use App\Traits\SendEmailTrait;
use App\Traits\SendSMSTrait;
use App\Traits\UserDataTrait;
use App\User;
use App\Utilities\RabbitMQ;
use Exception;
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
     * @throws Exception
     */
    public function handle(BookingCreated $event)
    {
        $data = $event->data;
        // Send user email

        // Send SP mail
        $booking = array_get($data, 'booking');
        $provider = $booking->provider;

        // Send customer email
        $this->send([
            'email_address' => $booking->user->email,
            'subject'       => Arr::get($data, 'subject'),
            'mailable'      => \App\Mail\BookingCreated::class,
            'data'          => [
                'business_name'    => $provider->service_provider_name,
                'service_name'     => $booking->service->service_name,
                'description'      => $booking->providerService->description,
                'booking_time'     => $booking->booking_time,
                'service_cost'     => $booking->amount,
                'service_duration' => $booking->providerService->duration,
            ]
        ], '');

        // Send provider email
        $this->send([
            'email_address' => $provider->business_email,
            'subject'       => Arr::get($data, 'subject'),
            'mailable'      => BookingCreatedProvider::class,
            'data'          => [
                'booking_time'         => $booking->booking_time,
                'location_name'        => Arr::get($booking->location, 'name'),
                'location_description' => Arr::get($booking->location, 'location_description'),
                'user_name'            => $booking->user->name,
            ]
        ], '');

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
