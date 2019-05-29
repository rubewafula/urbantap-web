<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingStatusRequest;
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
            ->update(['status' => $status]);
        if (!$updated) {
            return [
                'success' => false,
                'message' => "Failed to process request"
            ];
        }

        $this->trail($bookingId, $status, $request->get('reason', ""), $originator);

        return [
            'success' => true,
            'id'      => $request->get('booking_id'),
            'message' => 'Bookings updated OK'
        ];
    }
}
