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
use App\Utilities\RawPaginate;

class BookingsController extends Controller{


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
        // if(!is_null($from_date)){
        //     $filter = " and date(b.booking_time) = '" .$from_date . "' ";
        // }
         
        $query = "select b.service_provider_id, b.user_id, u.name as client,"
            . " u.email,u.phone_no,  ss.service_name,  b.booking_time, "
            . " b.booking_duration, b.expiry_time, s.status_code, "
            . " s.description as status_description, ps.description as "
            . " provider_service_description, ps.cost, ps.duration "
            . " from bookings b inner join statuses s on " 
            . " b.status_id = s.id inner join service_providers sp "
            . " on sp.id=b.service_provider_id "
            . " inner join provider_services ps on "
            . " ps.id = b.provider_service_id inner join services ss "
            . " on ss.id=ps.service_id inner join users u on "
            . " u.id = b.user_id " . $filter;


        $results = RawPaginate::paginate($query);

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

    public function create()
    {

        $req = file_get_contents('php://input');
$request = json_decode($req, true);

        $validator = Validator::make($request,[
            'provider_service_id' => 'required:bookings,id',
            'service_provider_id' => 'required|unique:bookings,id',
            'user_id' => 'required|unique:bookings,id',
            'booking_time' => 'required:bookings',
            'booking_duration' => 'required:bookings',
            'expiry_time' => 'required:bookings',
            'status_id' => 'required:bookings',
          //  'description' => 'string',
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        } else {

            DB::insert("insert into bookings (provider_service_id, service_provider_id, user_id, booking_time, booking_duration, expiry_time, status_id, created_at, updated_at, deleted_at) values (:provider_service_id, :service_provider_id, :user_id, :booking_time, :booking_duration, :expiry_time, :status_id, now(), now(), now())", [
                    'provider_service_id'=>$request['provider_service_id'],
                    'service_provider_id'=>$request['service_provider_id'],
                    'user_id'=> $request['user_id'],
                    'booking_time'=>$request['booking_time'],
                    'booking_duration'=>$request['booking_duration'],
                    'expiry_time'=>$request['expiry_time'],
                    'status_id'=>DBStatus::RECORD_PENDING
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

    public function update(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'provider_service_id' => 'required|integer',
            'service_provider_id' => 'required|integer',
            'user_id' => 'required|integer',
            'booking_time' => 'required|exists:bookings|max:255',
            'new_booking_time' => 'unique:bookings|max:255',
            'booking_duration' => 'required|exists:bookings|max:255',
            'new_booking_duration' => 'unique:bookings|max:255',
            'expiry_time' => 'required|exists:bookings|max:255',
            'expiry_time' => 'unique:bookings|max:255',
            'new_expiry_time' => 'required|exists:bookings|max:255',

        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }else{

            $update = [];
            if(!empty($request->get('new_provider_service_id')) ){
                $update['provider_service_id']  =$request->get('new_provider_service_id') ;
            }
            if(!empty($request->get('new_service_provider_id')) ){
                $update['service_provider_id']  =$request->get('new_service_provider_id') ;
            }
            if(!empty($request->get('user_id')) ){
                $update['user_id']  =$request->get('new_user_id') ;
            }
            if(!empty($request->get('new_booking_time')) ){
                $update['booking_time']  =$request->get('new_booking_time') ;
            }
            if(!empty($request->get('new_booking_duration')) ){
                $update['booking_duration']  =$request->get('new_booking_duration') ;
            }
            if(!empty($request->get('new_expiry_time')) ){
                $update['expiry_time']  =$request->get('new_expiry_time') ;
            }


            DB::table('bookings')
                ->where('id', $request->get('id'))
                ->update($update);

            $out = [
                'success' => true,
                'id'=>$request->get('id'),
                'message' => 'Bookings updated OK'
            ];

            return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
        }
    }

    /**
     * curl -i -XDELETE -H "content-type:application/json" --data 
     * '{"id":4}' 
     * 'http://127.0.0.1:8000/api/bookings/delete'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
     ***/
    public function delete(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:bookings,id'
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }else{
            DB::table('bookings')
                ->where('id', $request->get('id'))
                ->update(['status_id' => DBStatus::RECORD_DELETED]);

            $out = [
                'success' => true,
                'id'=>$request->get('id'),
                'message' => 'Bookings marked deleted OK'
            ];

            return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
        }
    }

}


