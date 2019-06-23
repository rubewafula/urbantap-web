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
use App\Utilities\Utils;
use App\Utilities\RawQuery;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;


class ServiceProviderReviewsController extends Controller{

    /**
     * Display the specified user reviewa.
     * curl -i -XGET -H "content-type:application/json" 
     * http://127.0.0.1:8000/api/service-providers/reviews/get
     * @return JSON 
     */

    public function getUserReviews(Request $request)
    {
        $user = $request->user();
        $user_id = $user->id;
        $page = 1; 
        $limit =null;
        if(array_key_exists('page', $req)){
            $page = is_numeric($request['page']) ? $request['page'] : 1;
        }
        if(array_key_exists('limit', $req)){
            $limit = is_numeric($request['limit']) ? $request['limit'] : null;
        }
        $image_url = Utils::PROFILE_URL;

        $rawQuery = "SELECT r.created_at, "
            . " r.provider_service_id, r.rating, r.review, "
            . " r.status_id, concat(if(u.first_name is null, '', u.first_name), ' ', "
            . " if(u.last_name is null, '', u.last_name)) as reviewer, "
            . " u.email, s.service_name, "
            . " concat( '$image_url' ,'/', if(d.passport_photo is null, 'avatar-bg-1.png', "
            . " JSON_UNQUOTE(json_extract(d.passport_photo, '$.media_url'))) ) as thumbnail "
            . " FROM  reviews r  inner join users u on u.id=r.user_id "
            . " inner join user_personal_details d on u.id = d.user_id "
            . " inner join provider_services ps on ps.id = r.provider_service_id "
            . " inner join services s on s.id = ps.service_id where "
            . " u.id = '" . $user_id . "' "
            . " order by r.id desc";

        $results = RawQuery::paginate($rawQuery, $page=$page, $limit=$limit);

        Log::info('Extracted user review review details : '.var_export($results, 1));
        if(empty($results)){
            return Response::json($results, HTTPCodes::HTTP_NO_CONTENT );
        }
        return Response::json($results, HTTPCodes::HTTP_OK);

    }

    /**
     * Display the specified service providers.
     * curl -i -XGET -H "content-type:application/json" 
     * http://127.0.0.1:8000/api/service-providers/reviews/all
     *
     * @param  \App\Category $category
     *
     * @return JSON 
     */

    public function get(Request $request)
    {
        $validator = Validator::make($request->all(),
                [
                'service_provider_id'=>'integer|exists:service_providers,id|nullable', 
                'page' =>'integer|nullable',
                'limit'=>'integer|nullable',
                'provider_service_id'=>'integer|exists:provider_services,id|nullable']
                );
        if($validator ->fails()){
            $out =[
                'sucess'=> false, 
                'message'=> $validator->messages()

            ];

            return Response::json($out,HTTPCodes::HTTP_PRECONDITION_FAILED);
        }
        $page = $request->page;
        $limit = $request->limit;
        $service_provider_id = $request->service_provider_id;
        $provider_service_id = $request->provider_service_id;

        $image_url = Utils::PROFILE_URL;
        $filter= '';
        if(!is_null($service_provider_id)){
            $filter = " and r.service_provider_id = '" .$service_provider_id . "' ";
        }

        if(!is_null($provider_service_id)){
            $filter = " and r.provider_service_id = '" .$provider_service_id . "' ";
        }

        $rawQuery = "SELECT r.created_at, s.service_name, ps.description,  "
            . " r.provider_service_id, r.rating, r.review, "
            . " r.status_id, concat(if(u.first_name is null, '', u.first_name), ' ', "
            . " if(u.last_name is null, '', u.last_name)) as reviewer, "
            . " u.email, s.service_name, "
            . " concat( '$image_url' ,'/', if(d.passport_photo is null, 'avatar-bg-1.png', "
            . " JSON_UNQUOTE(json_extract(d.passport_photo, '$.media_url'))) ) as thumbnail "
            . " FROM  reviews r  inner join users u on u.id=r.user_id "
            . " inner join user_personal_details d on u.id = d.user_id "
            . " inner join provider_services ps on ps.id = r.provider_service_id "
            . " inner join services s on s.id = ps.service_id where "
            . " 1= 1  " . $filter 
            . " order by r.id desc";


        $results = RawQuery::paginate($rawQuery, $page=$page, $limit=$limit);

        //dd(HTTPCodes);
        Log::info('Extracted service provider review details : '.var_export($results, 1));
        if(empty($results)){
            return Response::json($results, HTTPCodes::HTTP_NO_CONTENT );
        }
        return Response::json($results, HTTPCodes::HTTP_OK);

    }

    /**
     * curl -i -XPOST -H "content-type:application/json" 
     * --data '{"service_provider_id":1, "@file":"FILES",
     *  "description":"Some very loong text "}' 
     * 'http://127.0.0.1:8000/api/service-providers/reviews/create'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
     ***/
    public function create(Request $request)
    {


        $user = $request->user();
        $validator = Validator::make($request->all(),
                ['booking_id' => 'required|exists:bookings,id',
                'rating'=>'integer|min:1|max:5',
                'review' => 'string|min:10|max:250' ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }
        $booking = \App\Booking::where(['id'=>$request->booking_id, 'user_id'=>$user->id])->first();
        if(!$booking){

            $out = [
                'success' => false,
                'message' => ['booking_id'=> 'User booking not found']
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        } 

        if(!in_array($booking->status_id,
                    [
                    DBStatus::BOOKING_PAID, 
                    DBStatus::BOOKING_PARTIALLY_PAID, 
                    DBStatus::BOOKING_CLOSED, 
                    DBStatus::BOOKING_REJECTED])){
            $out = [
                'success' => false,
            'message' => ['booking_id'=> 'Cannot rate, booking was not completed']
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }
        $reviewed = \App\Review::where(['booking_id'=>$request->booking_id])->first();
        if($reviewed){
            $out = [
                'success' => false,
                'message' => ['booking_id'=> 'User booking already reviewed']
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }else{

            DB::insert("insert into reviews (user_id, service_provider_id, "
                    . " provider_service_id, booking_id, rating, review, created_at, updated_at,"
                    . " status_id) values (:user_id, :service_provider_id,  "
                    . " :provider_service_id, :booking_id, :rating, :review, now(),  now(), "
                    . " :status_id) ", 
                    [
                    'user_id'=> $user->id,
                    'service_provider_id'  => $booking->service_provider_id,
                    'provider_service_id'  => $booking->provider_service_id,
                    'booking_id'           => $booking->id,
                    'rating'               => $request->get('rating'),
                    'review'               => $request->get('review'),
                    'status_id'            => DBStatus::TRANSACTION_PENDING
                    ]
                    );
            $sql = "select avg(rating) as rating from reviews where service_provider_id=:spid";
            $result = RawQuery::query($sql, ['spid'=>$booking->service_provider_id]);
            $sql = "update service_providers set overall_rating =:rat where id=:spid";
            RawQuery::query($sql, ['rat'=>array_get($result, '0.rating', 0), 'spid'=>$booking->service_provider_id ]);

            $out = [
                'success'     => true,
                'id'          => DB::getPdo()->lastInsertId(),
                'rating'      => $request->rating,
                'review'      => $request->review,
                'review_date' => new \Carbon\Carbon,
                'booking_id'  => $booking->id,
                'message'     => 'Booking review added successfully'
            ];

            return Response::json($out, HTTPCodes::HTTP_CREATED);

        }
    }

    /**
     * curl -i -XDELETE -H "content-type:application/json" 
     * --data '{"id":1}' 
     * 'http://127.0.0.1:8000/api/service-providers/reviews/del'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
     ***/
    public function delete(Request $request)
    {

        $validator = Validator::make($request->all(),[
                'id' => 'required|exists:reviews,id'
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }else{

            DB::table('reviews')
                ->where('id', $request->get('id'))
                ->update(['status_id' => DBStatus::TRANSACTION_DELETED]);

            $out = [
                'success' => true,
                'user_id'=>$request->get('id'),
                'message' => 'Review delete OK'
            ];

            return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
        }
    }


}


