<?php

namespace App\Listeners;

use App\Booking;
use App\Contracts\ShouldSendMail;
use App\Contracts\ShouldSendSMS;
use App\Events\BookingCreated;
use App\Mail\BookingCreatedProvider;
use App\Notifications\BookingCreatedNotification;
use App\ServiceProvider;
use App\Traits\SendEmailTrait;
use App\Traits\SendSMSTrait;
use App\User;
use Exception;
use Illuminate\Support\Arr;

/**
 * Class BookingCreatedListener
 * @package App\Listeners
 */
class BookingCreatedListener implements ShouldSendSMS, ShouldSendMail
{
    use SendSMSTrait, SendEmailTrait;
    /**
     * @var string
     */
    protected $mailSubject = "Booking Request Placed";

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
        $booking = $event->booking;

        // Send customer email
        $this->sendUserNotifications($booking, $booking->provider);

        // Send provider email
        $this->sendProviderNotifications($booking->provider, $booking);
    }

    /**
     * @param ServiceProvider $provider
     * @param Booking $booking
     */
    private function sendProviderNotifications(ServiceProvider $provider, Booking $booking): void
    {
        // Send email
        $this->send([
            'email_address' => $provider->business_email,
            'subject'       => $this->mailSubject,
            'mailable'      => BookingCreatedProvider::class,
            'data'          => [
                'booking_time'         => $booking->booking_time,
                'location_name'        => Arr::get($booking->location, 'name'),
                'location_description' => Arr::get($booking->location, 'location_description'),
                'user_name'            => $booking->user->name,
            ]
        ], '');

        // Send sms if phone number exists
        if ($provider->business_phone) {
            $location = Arr::get($booking->location, 'name');
            $this->sms([
                'recipients' => [$provider->business_phone],
                'message'    => "Booking received. {$booking->service->service_name}, at {$location} on " .
                    "{$booking->booking_time}. Use " . config('app.name') . " to accept",
            ]);
        }

        // Broadcast notification
        $provider->user->notify(new BookingCreatedNotification([
            'user_id'          => $booking->user->id,
            'booking_id'       => $booking->id,
            'service_provider' => $provider,
            'message'          => "Booking received from {$booking->user->name} for service {$booking->service->service_name}"
        ]));
    }

    /**
     * @param Booking $booking
     * @param ServiceProvider $provider
     */
    private function sendUserNotifications(Booking $booking, ServiceProvider $provider): void
    {
        // Send email
        if ($booking->user->email) {
            $this->send([
                'email_address' => $booking->user->email,
                'subject'       => $this->mailSubject,
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
        } else {
            $this->sms([
                'recipients' => [$booking->user->phone_no],
                'message'    => "Your request has been received. {$provider->service_provider_name}," .
                    " {$booking->service->service_name} at {$booking->booking_time}"
            ]);
        }
    }


}
