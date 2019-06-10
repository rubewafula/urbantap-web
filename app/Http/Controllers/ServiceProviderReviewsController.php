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
use App\Utilities\RawQuery;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;


class ServiceProviderReviewsController extends Controller{

	 /**
     * Display the specified service providers.
     * curl -i -XGET -H "content-type:application/json" 
     * http://127.0.0.1:8000/api/service-providers/reviews/all
     *
     * @param  \App\Category $category
     *
     * @return JSON 
     */
 
    public function get($service_provider_id=null, Request $request)
    {
        $req= $request->all();
        $page = 1; 
        $limit =null;
        $provider_service_id = $request->get('provider_service_id');
        //die(print_r($req, 1));
        if(array_key_exists('page', $req)){
             $page = is_numeric($request['page']) ? $request['page'] : 1;
        }
        if(array_key_exists('limit', $req)){
             $limit = is_numeric($request['limit']) ? $request['limit'] : null;
        }
       
    	$validator = Validator::make(['service_provider_id'=>$service_provider_id, 
            'provicer_service_id' => $provider_service_id],
    		['service_provider_id'=>'integer|exists:service_providers,id|nullable', 
             'provider_service_id'=>'integer|exists:provider_services,id|nullable']
        );
    	if($validator ->fails()){
    		$out =[
                'sucess'=> false, 
               'message'=> $validator->messages()

            ];

            return Response::json($out,HTTPCodes::HTTP_PRECONDITION_FAILED);
    	}


        $filter= '';
        if(!is_null($service_provider_id)){
            $filter = " and r.service_provider_id = '" .$service_provider_id . "' ";
        }

        if(!is_null($provider_service_id)){
            $filter = " and r.provider_service_id = '" .$provider_service_id . "' ";
        }

        $rawQuery = "SELECT r.provider_service_id, r.rating, r.review, "
            . " r.status_id, u.name as reviewer, u.email, s.service_name"
            . " FROM  reviews r  inner join users u on u.id=r.user_id "
            . " inner join provider_services ps on ps.id = r.provider_service_id "
            . " inner join services s on s.id = ps.service_id where 1=1  " . $filter ;
        //die($rawQuery);

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
       
    	$validator = Validator::make($request->all(),[
		    'service_provider_id' => 'required|exists:service_providers,id',
            'provider_service_id' => 'required|exists:provider_services,id',
            'user_id' => 'required|exists:users,id',
            'rating'=>'integer',
            'review' => 'string'
		]);

		if ($validator->fails()) {
			$out = [
		        'success' => false,
		        'message' => $validator->messages()
		    ];
			return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
		}else{
           

        	DB::insert("insert into reviews (user_id, service_provider_id, "
                . " provider_service_id, rating, review, created_at, updated_at,"
                . " status_id) values (:user_id, :service_provider_id,  "
                . " :provider_service_id,  :rating, :review, now(),  now(), "
                . " :status_id) ", 
                    [
                        'user_id'=> $request->get('user_id'),
                        'service_provider_id'=>$request->get('service_provider_id'),
                        'provider_service_id'=>$request->get('provider_service_id'),
                        'rating'=>$request->get('rating'),
                        'review'=>$request->get('review'),
                        'status_id'=>DBStatus::TRANSACTION_PENDING
                    ]
        	    );

	    	$out = [
		        'success' => true,
		        'id'=>DB::getPdo()->lastInsertId(),
		        'message' => 'Service provider portfolios Created'
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
                ->update(['status_id' => DBStatus::RECORD_DELETED]);

	    	$out = [
		        'success' => true,
		        'user_id'=>$request->get('id'),
		        'message' => 'Review delete OK'
		    ];

    		return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
    	}
    }


}


