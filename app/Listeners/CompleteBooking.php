<?php

namespace App\Listeners;

use App\Events\BookingPaid;
use App\Utilities\DBStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Arr;

/**
 * Class CompleteBooking
 * @package App\Listeners
 */
class CompleteBooking implements ShouldQueue
{
    use InteractsWithQueue;

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
     */
    public function handle(BookingPaid $event)
    {
        $debitTransaction = $event->transaction;
        $booking = $event->booking;
        $paymentData = Arr::only($event->data, ['name', 'msisdn']);

        // FIXME: Calculate this
        $providerRunningBalance = 0;

        $transaction = new Transaction();
        $transaction->user_id = $booking->service_provider_id;
        $transaction->transaction_type = "CREDIT";
        $transaction->reference = $debitTransaction->reference;
        $transaction->amount = $amount = $debitTransaction->amount;
        $transaction->running_balance = $providerRunningBalance;
        $transaction->status_id = DBstatus::TRANSACTION_COMPLETE;

        DB::insert("insert into user_balance set user_id='" . $booking->service_provider_id . "',
              balance='" . $amount . "', available_balance='0',"
            . " transaction_id='" . $transaction->id . "',created=now() on duplicate key "
            . " update balance = balance + $amount"
        );

        DB::insert("insert into booking_trails set booking_id='" . $booking->id . "', 
             status_id='" . ($booking->balance ? DBStatus::BOOKING_PARTIALLY_PAID : DBStatus::BOOKING_PAID) . "',transaction_id = '" . $transaction->id . "',
             description='MPESA TRANSACTION', originator='MPESA', created_at=now()");

        DB::insert("insert into payments set reference='" . $transaction->id . "', date_received=now(),
             booking_id='" . $booking->id . "', payment_method='MPESA', paid_by_name='" . Arr::get($paymentData, 'name') . "',
             paid_by_msisdn='" . Arr::get($paymentData, 'msisdn') . "', amount='" . $booking->amount . "', 
             received_payment='" . $amount . "', balance='" . $booking->balance . "',
             status_id='" . DBStatus::TRANSACTION_COMPLETE . "', created_at=now()");
    }
}
