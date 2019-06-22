<?php
/**
 *Evance
 *CRUD
 **/

namespace App\Http\Controllers;

use App\Booking;
use App\Category;
use App\Events\BookingCreated;
use App\Notifications\BookingCreatedNotification;
use App\User;
use App\Utilities\DBStatus;
use App\Utilities\HTTPCodes;
use App\Utilities\RabbitMQ;
use App\Utilities\RawQuery;
use App\Utilities\Utils;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class BookingsController extends Controller
{
    /**
     * curl -i -XGET -H "content-type:application/json" -d '{"id":3}'
     * 'http://127.0.0.1:8000/api/bookings/provider/booking-with-details/3'
     **/
    public function getProviderBookingWithDetails($service_provider_id)
    {
        $validator = Validator::make(['service_provider_id' => $service_provider_id],
            ['service_provider_id' => 'integer|exists:service_providers,id|nullable']
        );
        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

        $sp_providers_url = Utils::SERVICE_PROVIDERS_URL;

        $query = "select b.created_at, b.id, b.service_provider_id, b.user_id, b.amount, b.balance, "
            . " concat(if(u.first_name is null, '', u.first_name), ' ', "
            . " if(u.last_name is null, '', u.last_name)) as client, "
            . " u.email,u.phone_no,  ss.service_name,  b.booking_time, "
            . " b.booking_duration, b.expiry_time, s.status_code, "
            . " b.booking_type, b.location, sp.service_provider_name, "
            . " sp.business_description, sp.business_phone, "
            . " s.description as status_description, ps.description as "
            . " provider_service_description, ps.cost, ps.duration, "
            . " concat( '$sp_providers_url' , '/', if(sp.cover_photo is null, 'img-03.jpg', "
            . " JSON_UNQUOTE(json_extract(sp.cover_photo, '$.media_url')))) as cover_photo "
            . " from bookings b inner join statuses s on "
            . " b.status_id = s.id inner join service_providers sp "
            . " on sp.id=b.service_provider_id "
            . " inner join provider_services ps on "
            . " ps.id = b.provider_service_id inner join services ss "
            . " on ss.id=ps.service_id inner join users u on "
            . " u.id = b.user_id where b.service_provider_id = '$service_provider_id'";

        $results = RawQuery::paginate($query);

        if (empty($results)) {
            return Response::json([], HTTPCodes::HTTP_NO_CONTENT);
        }

        $new_results = [];

        foreach ($results['result'] as $key => $result) {
            $id = $result->id;
            $booking_trail_sql = "select bt.created_at, bt.description, s.description "
                . " as status, s.status_code as status_code from "
                . " booking_trails bt inner join statuses s on s.id = bt.status_id "
                . " where bt.booking_id = '$id' order by bt.created_at desc";

            $booking_trails = RawQuery::paginate($booking_trail_sql);

            $payment_sql = " select p.id, p.reference, p.date_received, p.payment_method, "
                . " p.paid_by_name, p.paid_by_msisdn as msisdn, p.amount, p.received_payment, "
                . " p.balance, p.status_id, p.created_at, s.description as status, "
                . " s.status_code from payments p inner join "
                . " statuses s on p.status_id = s.id where p.booking_id = '$id' "
                . " order by p.created_at desc ";

            $payments = RawQuery::paginate($payment_sql);

            $result->booking_trails = $booking_trails;
            $result->payments = $payments;

            $new_results = $result;

        }


        return Response::json($new_results, HTTPCodes::HTTP_OK);

    }

    /**
     * curl -i -XGET -H "content-type:application/json" -d '{"id":3}'
     * 'http://127.0.0.1:8000/api/bookings/user/booking-with-details/2'
     **/
    public function getUserBookingWithDetails(Request $request)
    {
        $user = $request->user();
        Log::info("USER => " . var_export($user, 1));
        $user_id = $user->id;

        $page = $request->get('page');
        if (!is_numeric($page)) {
            $page = 1;
        }

        $sp_providers_url = Utils::SERVICE_PROVIDERS_URL;

        $query = "select b.created_at, b.id, b.service_provider_id, b.user_id, b.amount, b.balance, "
            . " concat(if(u.first_name is null, '', u.first_name), ' ', "
            . " if(u.last_name is null, '', u.last_name)) as client, "
            . " u.email,u.phone_no,  ss.service_name,  b.booking_time, "
            . " b.booking_duration, b.expiry_time, s.status_code, "
            . " b.booking_type, b.location, sp.service_provider_name, "
            . " sp.business_description, sp.business_phone, "
            . " s.description as status_description, ps.description as "
            . " provider_service_description, ps.cost, ps.duration, "
            . " concat( '$sp_providers_url' , '/', if(sp.cover_photo is null, 'img-03.jpg', "
            . " JSON_UNQUOTE(json_extract(sp.cover_photo, '$.media_url')))) as cover_photo "
            . " from bookings b inner join statuses s on "
            . " b.status_id = s.id inner join service_providers sp "
            . " on sp.id=b.service_provider_id "
            . " inner join provider_services ps on "
            . " ps.id = b.provider_service_id inner join services ss "
            . " on ss.id=ps.service_id inner join users u on "
            . " u.id = b.user_id where b.user_id = '$user_id'";


        $results = RawQuery::paginate($query, $page = $page);

        if (empty($results)) {
            return Response::json([], HTTPCodes::HTTP_NO_CONTENT);
        }

        $new_results = [];

        foreach ($results['result'] as $key => $result) {
            $id = $result->id;
            $booking_trail_sql = "select bt.created_at, bt.description, s.description "
                . " as status, s.status_code as status_code from "
                . " booking_trails bt inner join statuses s on s.id = bt.status_id "
                . " where bt.booking_id = '$id' order by bt.created_at desc";

            $booking_trails = RawQuery::paginate($booking_trail_sql);

            $payment_sql = " select p.id, p.reference, p.date_received, p.payment_method, "
                . " p.paid_by_name, p.paid_by_msisdn as msisdn, p.amount, p.received_payment, "
                . " p.balance, p.status_id, p.created_at, s.description as status, "
                . " s.status_code from payments p inner join "
                . " statuses s on p.status_id = s.id where p.booking_id = '$id' "
                . " order by p.created_at desc ";

            $payments = RawQuery::paginate($payment_sql);

            $result->booking_trails = $booking_trails;
            $result->payments = $payments;

            $new_results = $result;

        }


        return Response::json($new_results, HTTPCodes::HTTP_OK);

    }

    /**
     * curl -i -XGET -H "content-type:application/json"
     * 'http://127.0.0.1:8000/api/bookings/details/3'
     **/
    public function getBookingDetails($id, Request $request)
    {

        $validator = Validator::make(['id' => $id],
            ['id' => 'integer|exists:bookings,id|nullable']
        );
        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }
        
        $user = $request->user();

        $image_url = Utils::IMAGE_URL;
        $sp_providers_url = Utils::SERVICE_PROVIDERS_URL;
        $icon_url = Utils::ICONS_URL;
        $profile_url = Utils::PROFILE_URL;
        $p_services_url = Utils::PROVIDER_PORTFOLIOS_URL;
        $service_image_url = Utils::SERVICE_IMAGE_URL;

        $query = "select b.created_at, b.id, b.service_provider_id, b.user_id, b.amount, b.balance, "
            . " concat(if(u.first_name is null, '', u.first_name), ' ', "
            . " if(u.last_name is null, '', u.last_name)) as client,  r.rating, "
            . " r.created_at as review_date, r.review, "
            . " u.email,u.phone_no,  ss.service_name,  b.booking_time, "
            . " b.booking_duration, b.expiry_time, s.status_code, b.status_id as status_id, "
            . " b.booking_type, b.location, sp.service_provider_name, "
            . " sp.business_description, sp.business_phone, ps.id as provider_service_id, "
            . " s.description as status_description, ps.description as "
            . " provider_service_description, ps.cost, ps.duration, "
            . " concat( '$sp_providers_url' , '/', if(sp.cover_photo is null, 'img-03.jpg', "
            . " JSON_UNQUOTE(json_extract(sp.cover_photo, '$.media_url')))) as cover_photo "
            . " from bookings b inner join statuses s on "
            . " b.status_id = s.id inner join service_providers sp "
            . " on sp.id=b.service_provider_id inner join provider_services ps on "
            . " ps.id = b.provider_service_id inner join services ss "
            . " on ss.id=ps.service_id inner join users u on u.id = b.user_id "
            . " left join reviews r on r.user_id = u.id and b.id = r.booking_id "
            . " where b.id = :bid and (u.id = :uid or sp.user_id = :suid)";


        $results = RawQuery::query($query, ['bid'=>$id, 'uid'=>$user->id, 'suid'=>$user->id]);
        if(empty($results)){
            $out = [
                'success' => false,
                'message' => ['booking_id' => 'Could not find booking']
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

        $booking_trail_sql = "select bt.created_at, bt.description, s.description "
            . " as status, s.status_code as status_code from "
            . " booking_trails bt inner join statuses s on s.id = bt.status_id "
            . " where bt.booking_id = '$id' order by bt.created_at desc";

        $booking_trails = RawQuery::query($booking_trail_sql);

        $booking = array_get($results,0, new \stdClass);
        $out = array(
            'booking'        => $booking,
            'booking_trails' => $booking_trails,

        );
        return Response::json($out, HTTPCodes::HTTP_OK);

    }


    public function getUserBookings(Request $request)
    {
        return $this->get($request, 1);
    }


    /**
     * Display the specified service providers.
     * curl -i -XGET -H "content-type:application/json"
     * http://127.0.0.1:8000/api/bookings/all
     *
     * @param Category $category
     *
     * @return JSON
     */
    public function get(Request $request, $client = false)
    {

        $user = $request->user();
        Log::info("USER => " . var_export($user, 1));
        $user_id = $user->id;

        $page = $request->get('page');
        if (!is_numeric($page)) {
            $page = 1;
        }
        
        $filter_col = $client == false ? " sp.user_id " : " u.id ";
        
        $sp_providers_url = Utils::SERVICE_PROVIDERS_URL;

        $query = "select ps.id as provider_service_id, b.created_at,  "
            . " b.id as booking_id, b.service_provider_id, r.rating, "
            . " r.created_at as review_date, r.review, "
            . " b.user_id, u.first_name as client, b.amount, b.balance, "
            . " u.email,u.phone_no,  ss.service_name,  b.booking_time, "
            . " b.booking_duration, b.expiry_time, s.status_code, s.id as status_id, "
            . " b.booking_type, b.location, sp.service_provider_name, "
            . " s.description as status_description, ps.description as "
            . " provider_service_description, ps.cost, ps.duration, "
            . " concat( '$sp_providers_url' , '/', if(sp.cover_photo is null, 'img-03.jpg', "
            . " JSON_UNQUOTE(json_extract(sp.cover_photo, '$.media_url')))) as cover_photo "
            . " from bookings b inner join statuses s on "
            . " b.status_id = s.id inner join service_providers sp "
            . " on sp.id=b.service_provider_id "
            . " inner join provider_services ps on "
            . " ps.id = b.provider_service_id inner join services ss "
            . " on ss.id=ps.service_id inner join users u on "
            . " u.id = b.user_id left join reviews r on r.user_id = u.id and b.id = r.booking_id "
            . " where  $filter_col = '" . $user_id . "' order by b.id desc";


        $results = RawQuery::paginate($query, $page = $page);

        Log::info("Bookings QUERY " . $query);
        Log::info('Extracted service bookings results : ' . var_export($results, 1));
        if (empty($results)) {
            return Response::json($results, HTTPCodes::HTTP_NO_CONTENT);
        }
        return Response::json($results, HTTPCodes::HTTP_OK);

    }

    /**
     * curl -i -XPOST -H "content-type:application/json"
     * --data '{"provider_service_id":1, "booking":"Golden PAP",
     *  "description":"Best salon jab for the old"}'
     * 'http://127.0.0.1:8000/api/bookings/create'
     * @param Illuminate\Http\Request $request
     * @return JSON|JsonResponse
     *
     ***/

    public function create(Request $request)
    {
        $user = $request->user();
        $user_id = $user->id;
        $validator = Validator::make($request->all(), [
            'service_provider_id' => 'required|exists:service_providers,id',
            'service_id'          => 'integer|exists:provider_services,id',
            'booking_date'        => 'required|date_format:Y-m-d',
            'booking_time'        => 'required|date_format:H:i',
            'booking_duration'    => 'integer|required',
            'expiry_time'         => 'nullable|date_format:Y-m-d H:i',
            'location'            => 'required|json',
            'booking_type'        => ['required', Rule::in(['PROVIDER LOCATION', 'USER LOCATION'])],

        ]);


        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];

            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

        $booked_date = $request->get('booking_date');
        $timestamp = strtotime($booked_date);
        $booked_time = $request->get('booking_time');
        $day = date('l', $timestamp);
        $duration = $request->get('booking_duration');

        $service_provider_id = $request->get('service_provider_id');

        $working_hours_sql = "select oh.service_day, oh.time_from, "
            . " oh.time_to, oh.status_id from operating_hours oh "
            . " where service_provider_id = :pid and service_day= :dd "
            . " and :booked_time between time_from and time_to ";

        $result = RawQuery::query($working_hours_sql,
            ['pid' => $service_provider_id, 'dd' => $day, 'booked_time' => $booked_time]);

        $booking_fail_reason = "";
        $booking_allowed = true;
        if (empty($result)) {
            $booking_allowed = false;
            $booking_fail_reason = "Provider does not have active slot at this time";
        } else {
            $from_time = $result[0]->time_from;
            $to_time = $result[0]->time_to;

            $timeplusduration = date('H:i', strtotime("+" . $duration . " minutes",
                strtotime($booked_time)));

            if (strtotime($booked_time) > strtotime($from_time) &&
                strtotime($booked_time) < strtotime($to_time)) {
                $booked_slots = "select b.booking_time, "
                    . " b.booking_duration, b.expiry_time  "
                    . " from bookings b where b.status_id = :st and "
                    . " service_provider_id = :spid and date(booking_time) "
                    . " = :booked_date and "
                    . " ( time(booking_time) "
                    . "     between :time and :timeplusduration "
                    . "       or time(booking_time+interval b.booking_duration minute) "
                    . "     between :time1 and :timeplusduration1 "
                    . "       or (time(booking_time) < :time2 "
                    . "       and time(booking_time) > :timeplusduration2)"
                    . " ) ";

                $result = RawQuery::query($booked_slots,
                    ['st'                => DBStatus::BOOKING_COMPLETE,
                     'spid'              => $service_provider_id,
                     'booked_date'       => $booked_date,
                     'time'              => $booked_time,
                     'timeplusduration'  => $timeplusduration,
                     'time1'             => $booked_time,
                     'timeplusduration1' => $timeplusduration,
                     'time2'             => $booked_time,
                     'timeplusduration2' => $timeplusduration
                    ]
                );

                #if (!empty($result)){
                #    $booking_allowed = false;
                #    $booking_fail_reason = "Provider slot for specified time already booked";

                #}

            } else {
                $booking_allowed = false;
                $booking_fail_reason = "Provider does not have active slot at this time";
            }
        }

        if ($booking_allowed) {
            $booking_cost_sql = "select coalesce(ps.cost, sc.base_cost) as cost from service_costs sc left join provider_services ps "
                . " on ps.service_id =sc.service_id  where ps.service_id =:ps_id";

            $cresult = RawQuery::query($booking_cost_sql, ['ps_id' => $request['service_id']]);

            if (empty($cresult)) {
                $booking_allowed = false;
                $booking_fail_reason = "Service not yet available, Kindly contact admin";

            }
        }

        if (!$booking_allowed) {
            $out = [
                'success' => false,
                'message' => [
                    "service_provider_id" => [$booking_fail_reason]
                ]
            ];

            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);

        } else {

            $base_cost = array_get($cresult, 0)->cost;
            $other_amount = 0;

            $other_cost_result = RawQuery::query("select sum(c.amount)amt from "
                . " cost_parameters c  "
                . " where service_id=:ps_id", ['ps_id' => $request['service_id']]);

            if (!empty($other_cost)) {
                $other_amount = $other_cost_result[0]->amt;
            }

            $actual_cost = $base_cost + $other_amount;

            $actual_booking_time = $booked_date . ' ' . $booked_time;
            DB::insert("insert into bookings (provider_service_id, service_provider_id, user_id, booking_time, booking_duration, expiry_time, status_id, created_at, updated_at, deleted_at, booking_type, location, amount) values (:provider_service_id, :service_provider_id, :user_id, :booking_time, :booking_duration, :expiry_time, :status_id, now(), now(), now(), :booking_type, :location, :amount)", [
                    'provider_service_id' => $request['service_id'],
                    'service_provider_id' => $request['service_provider_id'],
                    'user_id'             => $user_id,
                    'booking_time'        => $actual_booking_time,
                    'booking_duration'    => $request['booking_duration'],
                    'expiry_time'         => $request['expiry_time'],
                    'status_id'           => DBStatus::BOOKING_NEW,
                    'booking_type'        => $request['booking_type'],
                    'location'            => $request['location'],
                    'amount'              => $actual_cost

                ]
            );

            /**send notifications to users
             * 1. Customer - notify booking OK  (email)
             * 2. provider - notify service booked - (email && sms)
             * 3. provider - push over app
             **/
            $booking_id = DB::getPdo()->lastInsertId();

            $booking = Booking::with([
                'user',
                'provider.user',
                'providerService'
            ])->find($booking_id);

            Log::info("Booking Data", $booking->toArray());
            broadcast(new BookingCreated($booking));

            $out = [
                'success' => true,
                'booking' => $booking,
                'message' => 'Bookings Created'
            ];

            return Response::json($out, HTTPCodes::HTTP_CREATED);
        }
    }

}


