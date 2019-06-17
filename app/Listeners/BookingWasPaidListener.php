<?php

namespace App\Listeners;

use App\Events\BookingWasPaidEvent;
use App\Mail\BookingWasPaid;
use App\Notifications\BookingPaidNotification;
use App\Traits\SendEmailTrait;
use App\Traits\SendSMSTrait;

/**
 * Class BookingWasPaidListener
 * @package App\Listeners
 */
class BookingWasPaidListener
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
     * @param BookingWasPaidEvent $event
     * @return void
     */
    public function handle(BookingWasPaidEvent $event)
    {
        $user = $event->user;
        $paymentData = $event->data;

        // Send sms or email
        if ($user->email)
            $this->send([
                'email_address' => $user->email,
                'subject'       => "Payment Received",
                'mailable'      => BookingWasPaid::class,
                'data'          => $paymentData
            ], "");
        else
            $this->sms([
                'recipients' => [$user->phone_no],
                'message'    => "Your payment has been received. Visit " . config('app.name') . " to complete your booking" .
                    config('app.name')
            ]);

        // Send notification
        $user->notify(new BookingPaidNotification(
            array_merge(
                $paymentData,
                [
                    'message' => "Your payment has been received, and wallet account updated"
                ]
            )
        ));
    }
}
