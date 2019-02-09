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
 
public function get($provider_id=null)
{
	$validator = Validator:make(['provider_id'=>$provider_id]
		['provider_id'=>'integer|exists:providers,id|nullable',
]);
	if($validator ->fails()){
		$out =[

           'sucess'=> false, 
           'message'=> $validator ->(messages)

            ];

            return Response::json($out,HTTPCodes_HTTP_PRECONDITION_FAILED);
	}

	  $filter= '';
	    if(!is_null($category_id)){
            $filter = " and provider_id = '" .$provider_id . "' ";
        }

      $results = DB::select( 
        	DB::raw("SELECT sp.id, sp.type, 
        	sp.provider_name,sp.work_location, sp.work_lat, sp.work_lng, sp.status_id, 
        	sp.overall_rating, sp.likes, sp.dislikes,
        	sp.created_at, sp.updated_at FROM service_providers sp inner join experts c on sp.id = c.service_provider_id where sp.status_id not in (" . DBStatus::RECORD_DELETED . ") " . $filter . " limit 100") 
        );

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
		    'category_id' => 'required|exists:categories,id',
            'service_provider_name' => 'required|unique:service_providers',
            'description' => 'string',
		]);

		if ($validator->fails()) {
			$out = [
		        'success' => false,
		        'message' => $validator->messages()
		    ];
			return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
		}else{

	 DB::insert("insert into service_providers (id, type, service_provider_name, work_location, work_lat, work_lng, status_id, overall_rating, overall_likes, overall_dislikes,"
         ."created_at, updated_at, deleted_at) "
         . " values (:id, :type, :service_provider_name,:work_location, :work_lat, :work_lng, :status_id, :overall_rating, :overall_likes, :overall_dislikes, "
         . " :status_id, now(), now(), now())", [
                    'id'=>$request->get('id'),
                    'type'=>$request->get('type'),
                    'service_provider_name'=> $request->get('service_provider_image'),
                    'work_location'=>$request->get('work_location'),
                    'work_lat'=>$request->get('work_lat'),
                    'work_lng'=>$request->get('work_lng'),
                    'status_id'=>$request->get('status_id'),
                    'overall_rating'=>$request->get('overall_rating'),
                    'overall_likes'=>$request->get('overall_likes'),
                    'overall_dislikes'=>$request->get('overall_dislikes'),
                    'status_id'=>DBStatus::RECORD_PENDING,
                    'description'=>$request->get('description')
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
		    'id' => 'required|integer',
		    'type' => 'required|exists:service_providers,type|max:255',
		    'new_type' => 'unique:service_providers,type|max:255',
   'service_provider_name' => 'required|exists:service_providers,service_provider_name|max:255',
            'new_name' => 'unique:service_providers,service_provider_name|max:255',
            'work_location'=>'required|exists:service_providers,work_location|max:255'
            'new_work_location' => 'unique:service_providers,work_location|max:255',
            'work_lat'=>'required|exists:service_providers,work_lat|max:255',
            'new_work_lat'=>'unique:service_providers,work_lat|max:255',
            'work_lng'=>'required|exists:service_providers,work_lng|max:255',
            'new_work_lng'=>'unique:service_providers,work_lng|max:255',
            'status_id'=>'required|exists:service_providers,status_id|max:255',
            'new_status_id'=>'unique:service_providers,status_id|max:255',
            'overall_rating'=>'required|exists:service_providers,overall_rating|max:255',
            'new_overall_rating'=>'unique:service_providers,overall_rating|max:255',
            'overall_likes'=>'required|exists:service_providers,overall_likes|max:255',
            'new_overall_likes'=>'unique:service_providers,overall_likes|max:255',
            'overall_dislikes'=>'required|exists:service_providers,overall_dislikes|max:255',
            'new_overall_dislikes'=>'unique:service_providers,overall_dislikes|max:255',

		]);

		if ($validator->fails()) {
			$out = [
		        'success' => false,
		        'message' => $validator->messages()
		    ];
			return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
		}else{

            $update = [];
            if(!empty($request->get('new_type')) ){
                $update['type']  =$request->get('new_type') ;
            }
            if(!empty($request->get('new_service_provider_name')) ){
                $update['service_provider_name']  =$request->get('new_service_provider_name') ;
            }
             if(!empty($request->get('new_work_location')) ){
                $update['work_location']  =$request->get('new_work_location') ;
            }
             if(!empty($request->get('new_work_lat')) ){
                $update['work_lat']  =$request->get('new_work_lats') ;
            }
              if(!empty($request->get('new_work_lng')) ){
                $update['work_lng']  =$request->get('new_work_lng') ;
            }
            if(!empty($request->get('new_status_id')) ){
                $update['status_id']  =$request->get('new_status_id') ;
            }
            if(!empty($request->get('new_overall_rating')) ){
                $update['overall_rating']  =$request->get('new_overall_rating') ;
            }
            if(!empty($request->get('new_overall_likes')) ){
                $update['overall_likes']  =$request->get('new_overall_likes') ;
            }
            if(!empty($request->get('new_overall_dislikes')) ){
                $update['overall_dislikes']  =$request->get('new_overall_dislikes') ;
            }

	    	DB::table('service_providers')
            ->where('id', $request->get('id'))
            ->update($update);

	    	$out = [
		        'success' => true,
		        'id'=>$request->get('id'),
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
		    'id' => 'required|exists:service_providers,id'
		]);

		if ($validator->fails()) {
			$out = [
		        'success' => false,
		        'message' => $validator->messages()
		    ];
			return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
		}else{
	    	DB::table('providers')
            ->where('id', $request->get('id'))
            ->update(['status_id' => DBStatus::RECORD_DELETED]);

	    	$out = [
		        'success' => true,
		        'id'=>$request->get('id'),
		        'message' => 'Service provider marked deleted OK'
		    ];

    		return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
    	}
    }

}


