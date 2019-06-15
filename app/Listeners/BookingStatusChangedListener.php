<?php

namespace App\Listeners;

use App\Booking;
use App\Events\BookingStatusChanged;
use App\Mail\BookingAccepted;
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
                    break;
                case DBStatus::BOOKING_REJECTED:
                    $event->user->notify(new BookingRejectedNotification($notificationData));
                    break;
            }
            $this->sendUserNotifications($booking);
        } else {
//            [
//                $data,
//                $serviceProvider,
//            ] = $this->getServiceProviderNotificationData($event->data);
//            $serviceProvider->notify(new BookingCancelledNotification($data));
        }
    }

    /**
     * @param Booking $booking
     */
    private function sendUserNotifications(Booking $booking): void
    {
        $service = $booking->service->service_name;
        $provider = $booking->provider->service_provider_name;
        $time = $booking->booking_time;
        $amount = $booking->amount;
        $id = $booking->id;
        if ($booking->user->email)
            $this->send([
                'email_address' => $booking->user->email,
                'subject'       => "Booking Request Accepted",
                'mailable'      => BookingAccepted::class,
                'data'          => [
                    'booking_id'       => $id,
                    'business_name'    => $provider,
                    'service_name'     => $service,
                    'description'      => $booking->providerService->description,
                    'booking_time'     => $time,
                    'service_cost'     => $amount,
                    'service_duration' => $booking->providerService->duration,
                ]
            ], "");
        else
            $this->sms([
                'recipients' => [$booking->user->phone_no],
                'message'    => "Your booking has been accepted. {$service}, {$provider} at {$time}." .
                    "Send {$amount} to " . env('URBANTAP_PAYBILL') . " A/C No. {$id}. " . config('app.name')
            ]);
    }
}
