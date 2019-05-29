<?php

namespace App\Http\Controllers;

use App\Events\BookingStatusChanged;
use App\Http\Requests\BookingStatusRequest;
use App\User;
use App\Utilities\DBStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

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
            ->update(['status' => $status]);
        if (!$updated) {
            return [
                'success' => false,
                'message' => "Failed to process request"
            ];
        }

        $this->trail($bookingId, $status, $request->get('reason', ""), $originator);
        broadcast(new BookingStatusChanged([
            'booking_id' => $bookingId,
            'user_id'    => $userId = $request->get('user_id'),
        ], $status, [
            new User(['id' => $userId])
        ]));
        return [
            'success' => true,
            'id'      => $request->get('booking_id'),
            'message' => 'Bookings updated OK'
        ];
    }
}
