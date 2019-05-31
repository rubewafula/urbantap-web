<?php

namespace App\Listeners;

use App\Events\BookingPaid;
use App\Notifications\BookingPaidNotification;
use App\User;
use Illuminate\Support\Arr;

/**
 * Class BookingPaidListener
 * @package App\Listeners
 */
class BookingPaidListener extends BookingBaseListener
{
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
     * @param BookingPaid $event
     * @return void
     * @throws \Exception
     */
    public function handle(BookingPaid $event)
    {
        $data = array_merge(
            [
                'subject' => 'Booking Paid'
            ],
            $event->data
        );
        // Send user notifications
        $this->sendUserNotification($event, $data);

        // Send service provider notifications
        [
            $data,
            $serviceProvider,
        ] = $this->getServiceProviderNotificationData($data);
        $serviceProvider->notify(new BookingPaidNotification($data));
        $this->send($data, $this->serviceProviderMailTemplate);
        $this->sms($data);

    }

    /**
     * @param array $data
     * @return string
     */
    protected function getNotificationMessage(array $data): ?string
    {
        if ($this->isHalfAmount($data)) {
            $message = "Dear Service Provider, Booking reference number, %s has been reserved. Please note the booking time is %s for this request.";
            return sprintf($message, Arr::get($data, 'transaction_id'), Arr::get($data, 'booking_time'));
        }
        return null;
    }

    /**
     * @param User $user
     * @param array $data
     * @return string
     */
    protected function getUserNotificationMessage(User $user, array $data): string
    {
        // Name, transaction amount, transaction id, booking time
        if ($this->isHalfAmount($data)) {
            $message = "Dear %s, you have successfully paid KSh. %s for your booking, reference %s. Your slot has been reserved for %s. Thank you.";
            return sprintf($message, Arr::get($data, 'name'), Arr::get($data, 'amount'), Arr::get($data, 'transaction_id'), Arr::get($data, 'booking_time'));
        }
        // Name, transaction amount, transaction id, amount to booking.
        $halfAmount = $this->getHalfAmount($data);
        $amountToBooking = $halfAmount - ($amount = Arr::get($data, 'amount'));
        $message = "Dear %s, you have successfully paid KSh. %s for your booking, reference %s. Please pay at least KSh. %s to reserve your booking. Thank you.";
        return sprintf($message, Arr::get($data, 'name'), $amount, Arr::get($data, 'transaction_id'), $amountToBooking);
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
     * @param BookingPaid $event
     * @param array $data
     */
    private function sendUserNotification(BookingPaid $event, array $data): void
    {
        $user = $event->user;
        $userData = $this->getUserNotificationData($user, $data);
        $user->notify(new BookingPaidNotification($userData));
        $this->send($userData, $this->userMailTemplate);
        $this->sms($data);
    }
}
