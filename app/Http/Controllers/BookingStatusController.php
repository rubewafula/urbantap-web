<?php

namespace App\Http\Controllers;

use App\Booking;
use App\Events\BookingStatusChanged;
use App\Http\Requests\BookingStatusRequest;
use App\Status;
use App\Utilities\DBStatus;
use Illuminate\Support\Facades\DB;

/**
 * Class BookingStatusController
 * @package App\Http\Controllers
 */
class BookingStatusController extends BaseBookingController
{
    /**
     * @param BookingStatusRequest $request
     * @return array
     */
    public function update(BookingStatusRequest $request)
    {
        $originator = 'USER';
        $status = $request->get('status');
        if (in_array($status, [DBStatus::BOOKING_ACCEPTED, DBStatus::BOOKING_REJECTED, DBStatus::BOOKING_POST_REJECTED])) {
            $originator = 'SERVICE PROVIDER';
        }

        // Update booking
        $updated = DB::table('bookings')
            ->where(['id' => $bookingId = $request->get('booking_id')])
            ->update(['status_id' => $status]);
        if (!$updated) {
            return [
                'success' => false,
                'message' => "Failed to process request"
            ];
        }

        $this->trail($bookingId, $status, $request->get('reason', ""), $originator);
        $booking = Booking::with([
            'user',
            'provider.user',
            'providerService'
        ])->find($bookingId);
        broadcast(new BookingStatusChanged($booking));

        return [
            'success' => true,
            'id'      => $request->get('booking_id'),
            'message' => 'Bookings updated OK',
            'data'    => [
                'status_id'          => $status,
                'status_description' => Status::getDescription($status)
            ]
        ];
    }

    public function cancel_booking(Request $request){
        $booking_id = $request->booking_id;
        $status = $request->status;
        $user_id = $request->user_id;

        $affected = DB::update('update bookings set status = :status where booking_id = :booking_id', ['status'=>$status, 'booking_id' => $booking_id]);

        if($affected){
            return [
                'success' => true,
                'message' => "Booking cancelled successfully"
            ];
        }else{
            return [
                'success' => false,
                'message' => "Booking cancellation failed"
            ];
        }
    }
}