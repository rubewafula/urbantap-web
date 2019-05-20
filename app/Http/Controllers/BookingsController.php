<?php 
/**
 *Evance 
 *CRUD 

 **/

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response; 
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;	
use App\Utilities\HTTPCodes;
use App\Utilities\DBStatus;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Utilities\RawQuery;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\URL;


class BookingsController extends Controller{
    /**
    * curl -i -XGET -H "content-type:application/json" -d '{"id":3}'
    * 'http://127.0.0.1:8000/api/bookings/provider/booking-with-details/3'
    **/
    public function getProviderBookingWithDetails($service_provider_id)
    {
        $validator = Validator::make(['service_provider_id'=>$service_provider_id],
            ['service_provider_id'=>'integer|exists:service_providers,id|nullable']
        );
        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }
        $sp_providers_url =  URL::to('/storage/static/image/service-providers/');

        $query = "select b.id, b.service_provider_id, b.user_id, "
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

        if(empty($results)){
            return Response::json([] , HTTPCodes::HTTP_NO_CONTENT);
        }

        $new_results = [];

        foreach($results['result'] as $key=>$result){
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

        
        return Response::json($new_results , HTTPCodes::HTTP_OK);

    }

    /**
    * curl -i -XGET -H "content-type:application/json" -d '{"id":3}' 
    * 'http://127.0.0.1:8000/api/bookings/user/booking-with-details/2'
    **/
    public function getUserBookingWithDetails($user_id)
    {
        $validator = Validator::make(['user_id'=>$user_id],
            ['user_id'=>'integer|exists:users,id|nullable']
        );
        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }
        $sp_providers_url =  URL::to('/storage/static/image/service-providers/');
        $query = "select b.id, b.service_provider_id, b.user_id, "
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


        $results = RawQuery::paginate($query);

        if(empty($results)){
            return Response::json([] , HTTPCodes::HTTP_NO_CONTENT);
        }

        $new_results = [];

        foreach($results['result'] as $key=>$result){
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

        
        return Response::json($new_results , HTTPCodes::HTTP_OK);

    }

    /**
    * curl -i -XGET -H "content-type:application/json"  
    * 'http://127.0.0.1:8000/api/bookings/details/3'
    **/
     public function getBookingDetails($id)
    {
        $validator = Validator::make(['id'=>$id],
            ['id'=>'integer|exists:bookings,id|nullable']
        );
        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }
        $sp_providers_url =  URL::to('/storage/static/image/service-providers/');
        
        $query = "select b.id, b.service_provider_id, b.user_id, "
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
            . " u.id = b.user_id where b.id = '$id'";


        $results = RawQuery::paginate($query);

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

        $out= array(
            'booking' => empty($results) ? [] : $results['result'][0],
            'booking_trails' => empty($booking_trails) ? [] :$booking_trails['result'],
            'payments' =>  empty($payments) ? [] : $payments['result']

        );
        return Response::json($out, HTTPCodes::HTTP_OK);

    }


     public function getUserBookings($user_id = null)
    {
        return $this->get($service_provider_id=null, $user_id = $user_id);
    }


    /**
     * Display the specified service providers.
     * curl -i -XGET -H "content-type:application/json" 
     * http://127.0.0.1:8000/api/bookings/all
     *
     * @param  \App\Category $category
     *
     * @return JSON 
     */
    public function get($service_provider_id = null, $user_id=null)
    {

        $validator = Validator::make(['service_provider_id'=>$service_provider_id,
            'user_id' => $user_id],
            ['service_provider_id'=>'integer|exists:service_providers,id|nullable',
            'user_id' =>'integer|exists:users,id|nullable' ]
        );
        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

        $filter= '';
        //$from_date = $request->get('from_date');
        

        if(!is_null($service_provider_id)){
            $filter = " and b.service_provider_id = '" .$service_provider_id . "' ";
        }
        if(!is_null($user_id)){
            $filter = " and b.user_id = '" .$user_id . "' ";
        }
         
        $query = "select b.service_provider_id, b.user_id, u.name as client,"
            . " u.email,u.phone_no,  ss.service_name,  b.booking_time, "
            . " b.booking_duration, b.expiry_time, s.status_code, "
            . " b.booking_type, b.location, "
            . " s.description as status_description, ps.description as "
            . " provider_service_description, ps.cost, ps.duration "
            . " from bookings b inner join statuses s on " 
            . " b.status_id = s.id inner join service_providers sp "
            . " on sp.id=b.service_provider_id "
            . " inner join provider_services ps on "
            . " ps.id = b.provider_service_id inner join services ss "
            . " on ss.id=ps.service_id inner join users u on "
            . " u.id = b.user_id " . $filter;


        $results = RawQuery::paginate($query);

        //dd(HTTPCodes);
        Log::info('Extracted service bookings results : '.var_export($results, 1));
        if(empty($results)){
            return Response::json($results, HTTPCodes::HTTP_NO_CONTENT );
        }
        return Response::json($results, HTTPCodes::HTTP_OK);

    }

    /**
     * curl -i -XPOST -H "content-type:application/json" 
     * --data '{"provider_service_id":1, "booking":"Golden PAP",
     *  "description":"Best salon jab for the old"}' 
     * 'http://127.0.0.1:8000/api/bookings/create'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
     ***/

    public function create(Request $request)
    {


        $validator = Validator::make($request->all(),[
            'service_provider_id' => 'required|exists:service_providers,id',
            'user_id' => 'required|exists:users,id',
            'service_id' =>'integer|exists:provider_services,id',
            'booking_date' => 'required|date_format:Y-m-d',
            'booking_time' => 'required|date_format:H:i',
            'booking_duration' => 'integer|required',
            'expiry_time' => 'nullable|date_format:Y-m-d H:i',
            'location' => 'required|json',
            'booking_type'=> ['required', Rule::in(['PROVIDER LOCATION', 'USER LOCATION'])],
            
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
            ['pid' =>$service_provider_id, 'dd' =>$day, 'booked_time'=>$booked_time]);

        $booking_fail_reason = "";
        $booking_allowed = true;
        if(empty($result)){
            $booking_allowed = false;
            $booking_fail_reason = "Provider does not have active slot at this time";
        }else{
            $from_time = $result[0]->time_from;
            $to_time = $result[0]->time_to;

            $timeplusduration = date('H:i',strtotime("+".$duration ." minutes", 
                strtotime($booked_time)));

            if(strtotime($booked_time) > strtotime($from_time) && 
                strtotime($booked_time) < strtotime($to_time) ){
                $booked_slots =  "select b.booking_time, "
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
                    . " ) " ;
                    
                $result = RawQuery::query($booked_slots, 
                    ['st'=>DBStatus::BOOKING_COMPLETE,
                    'spid' => $service_provider_id,
                    'booked_date' => $booked_date,
                    'time' => $booked_time,
                    'timeplusduration' => $timeplusduration,
                    'time1' => $booked_time,
                    'timeplusduration1' => $timeplusduration,
                    'time2' => $booked_time,
                    'timeplusduration2'=>$timeplusduration
                    ]
                );

                if (!empty($result)){
                    $booking_allowed = false;
                    $booking_fail_reason = "Provider slot for specified time already booked";

                }

            }else{
                $booking_allowed = false;
                $booking_fail_reason = "Provider does not have active slot at this time";
            }
        }

        if($booking_allowed){
            $booking_cost_sql = "select base_cost from service_costs sc inner join provider_services ps "
                    . " on ps.service_id =sc.service_id  where ps.service_id =:ps_id";

            $cresult = RawQuery::query($booking_cost_sql, ['ps_id'=>$request['service_id']]);
                
            if (empty($cresult)){
                $booking_allowed = false;
                $booking_fail_reason = "Service not yet available, Kindly contact admin";

            }
        }

        if(!$booking_allowed){
            $out = [
                'success' => false,
                'message' => [
                    "service_provider_id" =>[$booking_fail_reason]
                ]
            ];
            
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);

        }else {

            $base_cost = $cresult[0]->base_cost;
            $other_amount = 0;

            $other_cost_result = RawQuery::query("select sum(c.amount)amt from "
                . " cost_parameters c  "
                . " where service_id=:ps_id", ['ps_id'=>$request['service_id']]);

            if(!empty($other_cost)){
                $other_amount = $other_cost_result[0]->amt;
            }

            $actual_cost = $base_cost + $other_amount;

            $actual_booking_time = $booked_date . ' '. $booked_time;
            DB::insert("insert into bookings (provider_service_id, service_provider_id, user_id, booking_time, booking_duration, expiry_time, status_id, created_at, updated_at, deleted_at, booking_type, location, amount) values (:provider_service_id, :service_provider_id, :user_id, :booking_time, :booking_duration, :expiry_time, :status_id, now(), now(), now(), :booking_type, :location, :amount)", [
                    'provider_service_id'=>$request['service_id'],
                    'service_provider_id'=>$request['service_provider_id'],
                    'user_id'=> $request['user_id'],
                    'booking_time'=>$actual_booking_time,
                    'booking_duration'=>$request['booking_duration'],
                    'expiry_time'=>$request['expiry_time'],
                    'status_id'=>DBStatus::RECORD_PENDING,
                    'booking_type'=>$request['booking_type'],
                    'location'=>$request['location'],
                    'amount' => $actual_cost

                ]
            );

            $out = [
                'success' => true,
                'id'=>DB::getPdo()->lastInsertId(),
                'message' => 'Bookings Created'
            ];

            return Response::json($out, HTTPCodes::HTTP_CREATED);
        }
    }

    /**
     *  curl -i -XPUT -H "content-type:application/json" 
     * --data '{"id":1, "bookings":"Golden Ladies Salon", 
     * "description":"Best salon jab for the old", "new_name":"Golden Ladies Salon 23"}' 
     * 'http://127.0.0.1:8000/api/bookings/update'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
     ***/

    public function updateBooking(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'booking_id' => 'required|integer|exists:bookings,id',
            'user_id' => 'integer|exists:users,id|nullable',
            'service_provicer_id' => 'integer|exists:service_providers, id|nullable',
            'status' => 'required|in:cancel,accept,reject, post-reject',
            'reason' => 'required|string'
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }else{
            switch ($request->get('status')) {
                case 'cancel':
                    $status_id = DBStatus::BOOKING_CANCELLED;
                    break;
                case 'accept':
                    $status_id = DBStatus::BOOKING_ACCEPTED;
                    break;
                case 'reject':
                    $status_id = DBStatus::BOOKING_REJECTED;
                    break;
                case 'post-reject':
                    $status_id = DBStatus::BOOKING_POST_REJECTED;
                    break;
                
                default:
                    $status_id = DBStatus::BOOKING_CANCELLED;
                    break;


            }
            if($status_id == DBStatus::BOOKING_ACCEPTED 
                || $status_id == DBStatus::BOOKING_REJECTED
                || $status_id == DBStatus::BOOKING_POST_REJECTED){
                if(is_null($request->get('service_provicer_id'))){
                    $out = [
                        'success' => false,
                        'message' => ["service_provider_id" => ["Could not cancel booking. "
                                . " Missing valid service provider"]
                            ]
                    ];
                    return Response::json($out, HTTPCodes::HTTP_FORBIDDEN);
                }
                $originator = 'SERVICE PROVIDER';
            }

            if($status_id == DBStatus::BOOKING_CANCELLED ){
                if(is_null($request->get('user_id'))){
                    $out = [
                        'success' => false,
                        'message' => ["user_id" => ["Could not cancel booking. "
                                . " Missing valid user"]
                            ]
                    ];
                    return Response::json($out, HTTPCodes::HTTP_FORBIDDEN);
                }
                $originator = 'USER';
            }

            $update = ['status_id' => $status_id];

            $where = [
                    ['id', '=', $request->get('booking_id')]
               ];
            if($status_id == DBStatus::BOOKING_CANCELLED){
                $where[] = ['user_id', '=', $request->get('user_id')];
            }else{
                $where[] = ['service_provider_id', '=', $request->get('service_provider_id')];
            }   

            $updated = DB::table('bookings')
                ->where($where)
                ->update($update);

            if(!$updated > 0){
                 $out = [
                    'success' => false,
                    'message' => ["user_id" => ["Could not cancel booking. "
                            . "User booking not found"]
                        ]
                ];
                return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
            }

            $insert = "insert into booking_trails (booking_id, status_id, "
                . " description, originator, created_at, updated_at) values (:bid, "
                . " :st, :desc, :originator, now(), now())";

            DB::insert($insert, 
                [
                    'bid' => $request->get('booking_id'),
                    'st' => DBStatus::BOOKING_CANCELLED,
                    'desc' => $request->get('reason'),
                    'originator'=>$originator
                ]
            );

            $out = [
                'success' => true,
                'id' => $request->get('booking_id'),
                'message' => 'Bookings updated OK'
            ];

            return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
        }
    }


}


