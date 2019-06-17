<?php

namespace App\Listeners;

use App\Events\BookingNotFoundEvent;
use App\Notifications\BookingPaidNotification;
use App\Traits\SendEmailTrait;
use App\Traits\SendSMSTrait;

/**
 * Class BookingWasPaidListener
 * @package App\Listeners
 */
class BookingNotFoundListener
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
     * @param BookingNotFoundEvent $event
     * @return void
     */
    public function handle(BookingNotFoundEvent $event)
    {
        $user = $event->user;
        $paymentData = $event->data;

        // Send sms or email
        $this->sms([
            'recipients' => [$user->phone_no],
            'message'    => "Your payment of Kshs. {$paymentData['amount']} has been received. Visit " . config('app.url') . " to complete your booking."
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
