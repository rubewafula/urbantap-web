<?php

namespace App\Listeners;

use App\Booking;
use App\Events\BookingStatusChanged;
use App\Mail\BookingAccepted;
use App\Mail\BookingCancelled;
use App\Mail\BookingCancelledProvider;
use App\Mail\BookingRejected;
use App\Notifications\BookingAcceptedNotification;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\BookingRejectedNotification;
use App\Traits\SendEmailTrait;
use App\Traits\SendSMSTrait;
use App\Utilities\DBStatus;
use Exception;

/**
 * Class BookingStatusChangedListener
 * @package App\Listeners
 */
class BookingStatusChangedListener
{
    use SendEmailTrait, SendSMSTrait;

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
     * @throws Exception
     */
    public function handle(BookingStatusChanged $event)
    {
        $booking = $event->booking;
        // Send provider notification
        if (in_array($booking->status_id, [DBStatus::BOOKING_ACCEPTED, DBStatus::BOOKING_REJECTED])) {
            $notificationData = [
                'booking_id' => $booking->id,
            ];
            switch ($booking->status_id) {
                case DBStatus::BOOKING_ACCEPTED:
                    $booking->user->notify(new BookingAcceptedNotification($notificationData));
                    $this->sendBookingAcceptedUserNotifications($booking);
                    break;
                case DBStatus::BOOKING_REJECTED:
                    $booking->user->notify(new BookingRejectedNotification($notificationData));
                    $this->sendBookingRejectedUserNotifications($booking);
                    break;
                case DBStatus::BOOKING_CANCELLED:
                    $booking->user->notify(
                        new BookingCancelledNotification([
                            'booking_id' => $booking->id,
                            'message'    => $this->getBookingCancelledUserMessage($booking)
                        ])
                    );
                    $this->sendBookingCancelledUserNotifications($booking);
            }
        } else {
            switch ($booking->status_id) {
                case DBStatus::BOOKING_CANCELLED:
                    $booking->provider->user->notify(
                        new BookingCancelledNotification([
                            'booking_id' => $booking->id,
                            'message'    => $this->getBookingCancelledProviderMessage($booking)
                        ])
                    );
                    $this->sendBookingCancelledProviderNotifications($booking);
            }
        }
    }

    /**
     * @param Booking $booking
     */
    private function sendBookingAcceptedUserNotifications(Booking $booking): void
    {
        if ($booking->user->email)
            $this->send([
                'email_address' => $booking->user->email,
                'subject'       => "Booking Request Accepted",
                'mailable'      => BookingAccepted::class,
                'data'          => [
                    'booking_id'       => $booking->id,
                    'business_name'    => $booking->provider->service_provider_name,
                    'service_name'     => $booking->service->service_name,
                    'description'      => $booking->providerService->description,
                    'booking_time'     => $booking->booking_time,
                    'service_cost'     => $booking->amount,
                    'service_duration' => $booking->providerService->duration,
                ]
            ], "");
        else
            $this->sms([
                'recipients' => [$booking->user->phone_no],
                'message'    => "Your booking has been accepted. {$booking->service->service_name}, {$booking->provider->service_provider_name} at {$booking->booking_time}." .
                    "Send {$booking->amount} to " . env('URBANTAP_PAYBILL') . " A/C No. {$booking->id}. " . config('app.name')
            ]);
    }

    /**
     * @param Booking $booking
     */
    private function sendBookingRejectedUserNotifications(Booking $booking)
    {
        if ($booking->user->email)
            $this->send([
                'email_address' => $booking->user->email,
                'subject'       => "Booking Request Rejected",
                'mailable'      => BookingRejected::class,
                'data'          => [
                    'booking_id'       => $booking->id,
                    'business_name'    => $booking->provider->service_provider_name,
                    'service_name'     => $booking->service->service_name,
                    'description'      => $booking->providerService->description,
                    'booking_time'     => $booking->booking_time,
                    'service_cost'     => $booking->amount,
                    'service_duration' => $booking->providerService->duration,
                ]
            ], "");
        else
            $this->sms([
                'recipients' => [$booking->user->phone_no],
                'message'    => "Your booking has been rejected. {$booking->service->service_name} from {$booking->provider->service_provider_name}." .
                    "Visit " . config('app.name') . " to book with a different provider."
            ]);
    }

    /**
     * @param Booking $booking
     */
    private function sendBookingCancelledProviderNotifications(Booking $booking)
    {
        $this->send([
            'email_address' => $booking->provider->business_email ?: $booking->provider->user->email,
            'subject'       => "Booking Request Cancelled",
            'mailable'      => BookingCancelledProvider::class,
            'data'          => [
                'booking_id' => $booking->id
            ]
        ], "");
        if ($booking->provider->business_phone)
            $this->sms([
                'recipients' => [$booking->provider->business_phone],
                'message'    => $this->getBookingCancelledProviderMessage($booking)
            ]);
    }

    /**
     * @param Booking $booking
     */
    private function sendBookingCancelledUserNotifications(Booking $booking)
    {
        if ($booking->user->email)
            $this->send([
                'email_address' => $booking->user->email,
                'subject'       => "Booking Request Cancelled",
                'mailable'      => BookingCancelled::class,
                'data'          => [
                    'business_name' => $booking->provider->service_provider_name,
                    'service_name'  => $booking->service->service_name,
                ]
            ], "");
        else
            $this->sms([
                'recipients' => [$booking->user->phone_no],
                'message'    => $this->getBookingCancelledUserMessage($booking)
            ]);
    }

    /**
     * @param Booking $booking
     * @return string
     */
    private function getBookingCancelledUserMessage(Booking $booking): string
    {
        return "Your booking has been cancelled. {$booking->service->service_name} from {$booking->provider->service_provider_name}." .
            "Visit " . config('app.name') . " to book with a different provider.";
    }

    /**
     * @param Booking $booking
     * @return string
     */
    private function getBookingCancelledProviderMessage(Booking $booking): string
    {
        return "Your booking has been cancelled. {$booking->service->service_name} from {$booking->provider->service_provider_name}." .
            config('app.name');
    }
}
