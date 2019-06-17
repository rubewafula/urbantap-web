<?php

namespace App\Listeners;

use App\Booking;
use App\Events\BookingPaid;
use App\Mail\BookingPaidProvider;
use App\Notifications\BookingPaidNotification;
use App\Traits\SendEmailTrait;
use App\Traits\SendSMSTrait;
use App\User;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Class BookingPaidListener
 * @package App\Listeners
 */
class BookingPaidListener
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
     * @param BookingPaid $event
     * @return void
     * @throws Exception
     */
    public function handle(BookingPaid $event)
    {
        $booking = $event->booking;
        $paymentData = $event->data;

        // Send user email, sms and notification
        $this->sendUserNotifications($booking, $paymentData);

        // Send provider email, sms and notification
        $this->sendProviderNotifications($booking, $paymentData);

    }


    /**
     * @param array $data
     * @return bool
     */
    private function isHalfAmount(array $data): bool
    {
        $halfAmount = $this->getHalfAmount($data);
        return Arr::get($data, 'balance') <= $halfAmount;
    }

    /**
     * @param array $data
     * @return float
     */
    private function getHalfAmount(array $data): float
    {
        return ceil(Arr::get($data, 'booking_amount') / 2);
    }

    /**
     * @param Booking $booking
     * @param array $paymentData
     */
    private function sendUserNotifications(Booking $booking, array $paymentData): void
    {
        Log::info("Preparing user notifications", compact('booking', 'paymentData'));
        $amount = Arr::get($paymentData, 'amount');
        $ref = Arr::get($paymentData, 'ref');
        $booking->user->notify(
            new BookingPaidNotification([
                'booking_id' => $booking->id,
                'message'    => "Payment received. {$amount}, reference {$ref}"
            ])
        );
        if ($booking->user->email)
            $this->send([
                'email_address' => $booking->user->email,
                'subject'       => "Booking Paid",
                'mailable'      => \App\Mail\BookingPaid::class,
                'data'          => [
                    'booking_id'        => $booking->id,
                    'business_name'     => $booking->provider->service_provider_name,
                    'service_name'      => $booking->service->service_name,
                    'description'       => $booking->providerService->description,
                    'booking_time'      => $booking->booking_time,
                    'service_cost'      => $booking->amount,
                    'service_duration'  => $booking->providerService->duration,
                    'amount_paid'       => $amount,
                    'payment_ref'       => $ref,
                    'balance'           => Arr::get($paymentData, 'balance', 0),
                    'reserved'          => $this->isHalfAmount($paymentData),
                    'amount_to_booking' => $this->getHalfAmount($paymentData) - $amount
                ]
            ], "");
        else
            $this->sms([
                'recipients' => [$booking->user->phone_no],
                'message'    => "Payment of Ksh. {$amount} received for service {$booking->service->service_name}, reference {$ref}. Balance Ksh. {$booking->balance}. " . config('app.url')
            ]);
    }

    /**
     * @param Booking $booking
     * @param array $paymentData
     */
    private function sendProviderNotifications(Booking $booking, array $paymentData)
    {
        # FIXME: Maybe we notify the provider only when the user has paid enough to confirm a service?
        $booking->provider->user->notify(
            new BookingPaidNotification([
                'booking_id' => $booking->id,
                'message'    => "Payment received for service {$booking->service->service_name}. Ksh.{$paymentData['amount']}."
            ])
        );
        $this->send([
            'email_address' => $booking->provider->user->business_email ?: $booking->user->email,
            'subject'       => "Booking Confirmed",
            'mailable'      => BookingPaidProvider::class,
            'data'          => [
                'booking_id'       => $booking->id,
                'business_name'    => $booking->provider->service_provider_name,
                'service_name'     => $booking->service->service_name,
                'description'      => $booking->providerService->description,
                'booking_time'     => $booking->booking_time,
                'service_cost'     => $booking->amount,
                'service_duration' => $booking->providerService->duration,
                'amount_paid'      => $paymentData['amount'],
                'payment_ref'      => $paymentData['ref'],
                'balance'          => Arr::get($paymentData, 'balance', 0)
            ]
        ], "");
        if ($booking->provider->business_phone)
            $this->sms([
                'recipients' => [$booking->provider->business_phone],
                'message'    => "Payment of Ksh. {$paymentData['amount']} received for service {$booking->service->service_name}. " . config('app.url')
            ]);
    }
}
