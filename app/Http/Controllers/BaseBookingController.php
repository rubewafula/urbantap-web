<?php


namespace App\Http\Controllers;


use App\Utilities\DBStatus;
use Illuminate\Support\Facades\DB;

/**
 * Class BaseBookingController
 * @package App\Http\Controllers
 */
abstract class BaseBookingController extends Controller
{
    /**
     * BaseBookingController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Booking trail
     *
     * @param $bookingId
     * @param $status
     * @param $description
     * @param $origin
     */
    protected function trail($bookingId, $status, $description, $origin)
    {
        $insert = "insert into booking_trails (booking_id, status_id, "
            . " description, originator, created_at, updated_at) values (:bid, "
            . " :st, :desc, :originator, now(), now())";

        DB::insert($insert,
            [
                'bid'        => $bookingId,
                'st'         => $status,
                'desc'       => $description,
                'originator' => $origin
            ]
        );
    }
}