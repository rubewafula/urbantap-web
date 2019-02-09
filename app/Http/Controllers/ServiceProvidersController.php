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
use App\Utilities\RawPaginate;
use Illuminate\Support\Facades\Validator;


class ServiceProvidersController extends Controller{

	 /**
     * Display the specified service providers.
     * curl -i -XGET -H "content-type:application/json" 
     * http://127.0.0.1:8000/api/service-providers/all
     *
     * @param  \App\Category $category
     *
     * @return JSON 
     */
 
    public function get($user_id=null, Request $request)
    {
        $req= $request->all();
        $page = 1; 
        $limit =null;
        //die(print_r($req, 1));
        if(array_key_exists('page', $req)){
             $page = is_numeric($request['page']) ? $request['page'] : 1;
        }
        if(array_key_exists('limit', $req)){
             $limit = is_numeric($request['limit']) ? $request['limit'] : null;
        }
       
    	$validator = Validator::make(['id'=>$user_id],
    		['user_id'=>'integer|exists:users,id|nullable']
        );
    	if($validator ->fails()){
    		$out =[
                'sucess'=> false, 
               'message'=> $validator->messages()

            ];

            return Response::json($out,HTTPCodes::HTTP_PRECONDITION_FAILED);
    	}

        $filter= '';
        if(!is_null($user_id)){
            $filter = " and sp.user_id = '" .$user_id . "' ";
        }

        $rawQuery = "SELECT sp.id, sp.type, sp.service_provider_name,sp.work_location, "
            . " sp.work_lat, sp.work_lng, sp.status_id, sp.overall_rating, "
            . " sp.overall_likes, sp.overall_dislikes, sp.created_at, sp.updated_at, "
            . " d.id_number, d.date_of_birth, d.gender,  d.passport_photo, "
            . " d.home_location work_phone_no "
            . " FROM service_providers sp inner join user_personal_details  d "
            . " using(user_id) where sp.status_id "
            . " not in (" . DBStatus::RECORD_DELETED . ") " . $filter ;

        $results = RawPaginate::paginate($rawQuery, $page=$page, $limit=$limit);

        //dd(HTTPCodes);
        Log::info('Extracted service service_providers results : '.var_export($results, 1));
        if(empty($results)){
        	return Response::json($results, HTTPCodes::HTTP_NO_CONTENT );
        }
        return Response::json($results, HTTPCodes::HTTP_OK);

    }

    /**
     * curl -i -XPOST -H "content-type:application/json" 
     * --data '{"category_id":1, "provider_name":"Golden PAP",
     *  "description":"Best salon jab for the old"}' 
     * 'http://127.0.0.1:8000/api/service-providers/create'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function create(Request $request)
    {

    	$validator = Validator::make($request->all(),[
		    'user_id' => 'required|exists:users,id|unique:service_providers',
            'service_provider_name' => 'required|unique:service_providers',
            'business_description' => 'required|string',
            'work_location' =>'string',
            'work_lat'=>'nullable|regex:/^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/',  
            'work_lng'=>'nullable|regex:/^(\+|-)?(?:180(?:(?:\.0{1,6})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,6})?))$/'           
		]);

		if ($validator->fails()) {
			$out = [
		        'success' => false,
		        'message' => $validator->messages()
		    ];
			return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
		}else{

        	DB::insert("insert into service_providers (type, user_id, service_provider_name,"
                . " business_description, work_location, work_lat, work_lng, status_id, "
                . " created_at, updated_at)  values (1, :user_id, "
                . " :service_provider_name, :business_description, :work_location, "
                . " :work_lat, :work_lng, " . DBStatus::RECORD_PENDING . ", now(), "
                . " now())  ", 

                    [
                        'service_provider_name'=> $request->get('service_provider_name'),
                        'user_id'=>$request->get('user_id'),
                        'work_location'=>$request->get('work_location'),
                        'work_lat'=>$request->get('work_lat'),
                        'work_lng'=>$request->get('work_lng'),
                        'business_description'=>$request->get('business_description')
                    ]
        	    );

	    	$out = [
		        'success' => true,
		        'id'=>DB::getPdo()->lastInsertId(),
		        'message' => 'Service provider Created'
		    ];

    		return Response::json($out, HTTPCodes::HTTP_CREATED);
    	}
    }

    /**
     *  curl -i -XPUT -H "content-type:application/json" 
     * --data '{"id":1, "provider_name":"Golden Ladies Salon", 
     * "description":"Best salon jab for the old", "new_name":"Golden Ladies Salon 23"}' 
     * 'http://127.0.0.1:8000/api/service-providers/update'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function update(Request $request)
    {
    	
    	$validator = Validator::make($request->all(),[
            'user_id' => 'required|exists:service_providers',
            'service_provider_name' => 'unique:service_providers',
            'business_description' => 'string',
            'work_location' =>'string',
            'work_lat'=>'nullable|regex:/^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/',  
            'work_lng'=>'nullable|regex:/^(\+|-)?(?:180(?:(?:\.0{1,6})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,6})?))$/' ,
            'status_id' =>'integer', 
            'overall_likes' =>'integer',  
            'overall_dislikes' =>'integer',  
            'overall_rating' =>'between:0,99.99',        
        ]);

		if ($validator->fails()) {
			$out = [
		        'success' => false,
		        'message' => $validator->messages()
		    ];
			return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
		}else{

            $update = [];
            if(!empty($request->get('service_provider_name')) ){
                $update['service_provider_name']  =$request->get('service_provider_name') ;
            }
            if(!empty($request->get('business_description')) ){
                $update['business_description']  =$request->get('business_description') ;
            }
             if(!empty($request->get('work_location')) ){
                $update['work_location']  =$request->get('work_location') ;
            }
             if(!empty($request->get('work_lat')) ){
                $update['work_lat']  =$request->get('work_lat') ;
            }
              if(!empty($request->get('work_lng')) ){
                $update['work_lng']  =$request->get('work_lng') ;
            }
            if(!empty($request->get('status_id')) ){
                $update['status_id']  =$request->get('status_id') ;
            }
            if(!empty($request->get('overall_rating')) ){
                $update['overall_rating']  =$request->get('overall_rating') ;
            }
            if(!empty($request->get('overall_likes')) ){
                $update['overall_likes']  =$request->get('overall_likes') ;
            }
            if(!empty($request->get('overall_dislikes')) ){
                $update['overall_dislikes']  =$request->get('overall_dislikes') ;
            }

	    	DB::table('service_providers')
                ->where('user_id', $request->get('id'))
                ->update($update);

	    	$out = [
		        'success' => true,
		        'user_id'=>$request->get('user_id'),
		        'message' => 'Service Provider updated OK'
		    ];

    		return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
    	}
    }

    /**
     * curl -i -XDELETE -H "content-type:application/json" --data 
     * '{"id":4}' 
     * 'http://127.0.0.1:8000/api/service-providers/delete'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function delete(Request $request)
    {
    	
    	$validator = Validator::make($request->all(),[
		    'user_id' => 'required|exists:service_providers'
		]);

		if ($validator->fails()) {
			$out = [
		        'success' => false,
		        'message' => $validator->messages()
		    ];
			return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
		}else{
	    	DB::table('service_providers')
            ->where('user_id', $request->get('id'))
            ->update(['status_id' => DBStatus::RECORD_DELETED]);

	    	$out = [
		        'success' => true,
		        'user_id'=>$request->get('user_id'),
		        'message' => 'Service provider marked deleted OK'
		    ];

    		return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
    	}
    }

}


