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

class AppointmentsController extends Controller{

	 /**
     * Display the specified appointments.
     * curl -i -XGET -H "content-type:application/json" 
     * http://127.0.0.1:8000/api/appointments/all
     *
     * @param  \App\Category $category
     *
     * @return JSON 
     */
 
public function get($provider_services_id=null)
{
	$validator = Validator::make(['provider_services_id'=>$provider_services_id],
		['provider_services_id'=>'integer|exists:provider_services_id,id|nullable',
]);

	if($validator ->fails()){
		$out =[

           'sucess'=> false, 
           'message'=> $validator->messages()

            ];

            return Response::json($out,HTTPCodes_HTTP_PRECONDITION_FAILED);
	}

	  $filter= '';
	    if(!is_null($provider_services_id)){
            $filter = " and provider_services_id = '" .$provider_services_id . "' ";
        }

      $results = DB::select( 
        	DB::raw("select a.id, sa.id, c.id, a.date, a.time, a.status, "
        	. "a.created_at, a.updated_at FROM "
            . "appointments a inner join experts e on a.id = c.service_provider_id " 
            .  "where sp.status_id = sp.id"

            . $filter . " limit 100") 
        );

   //dd(HTTPCodes);
        Log::info('Extracted service appointments results : '.var_export($results, 1));
        if(empty($results)){
        		return Response::json($results, HTTPCodes::HTTP_NO_CONTENT );
        }
        return Response::json($results, HTTPCodes::HTTP_OK);

    }

    /**
     * curl -i -XPOST -H "content-type:application/json" 
     * --data '{"category_id":1, "provider_name":"Golden PAP",
     *  "description":"Best salon jab for the old"}' 
     * 'http://127.0.0.1:8000/api/appointments/create'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function create(Request $request)
    {
    
    	$validator = Validator::make($request->all(),[
		    'provider_service_id' => 'required|exists:provider_service_id',
             'service_provider_name' => 'required|unique:provider_service',
            'description' => 'string',
		]);

		if ($validator->fails()) {
			$out = [
		        'success' => false,
		        'message' => $validator->messages()
		    ];
			return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
		}else{

	 DB::insert("insert into appointments (provider_service_id, service_provider_id, custormer_id,date, time,status,"
         ."created_at, updated_at, deleted_at) "
         . " values (:provider_service_id, service_provider_id, custormer_id, date, time, status, "
         . " :status_id, now(), now(), now())", [
                    'provider_service_id'=>$request->get('provider_service_id'),
                    'service_provider_id'=>$request->get('service_provider_id'),
                    'custormer_id'=> $request->get('custormer_id'),
                    'date'=>$request->get('date'),
                    'time'=>$request->get('time'),
                    'status'=>$request->get('status'),
                    'status_id'=>DBStatus::RECORD_PENDING,
                    'description'=>$request->get('description')
                ]
	    	);

	    	$out = [
		        'success' => true,
		        'id'=>DB::getPdo()->lastInsertId(),
		        'message' => 'Appointment Created'
		    ];

    		return Response::json($out, HTTPCodes::HTTP_CREATED);
    	}
    }

    /**
     *  curl -i -XPUT -H "content-type:application/json" 
     * --data '{"id":1, "provider_name":"Golden Ladies Salon", 
     * "description":"Best salon jab for the old", "new_name":"Golden Ladies Salon 23"}' 
     * 'http://127.0.0.1:8000/api/appointments/update'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function update(Request $request)
    {
    	
    	$validator = Validator::make($request->all(),[
		    'id' => 'required|integer',
		    'provider_service_id' => 'required|exists:provider_services,type|max:255',
            'new_provider_services' => 'unique:provider_services,type|max:255',
            'service_provider_id' => 'required|exists:service_provider_id,type|max:255',
            'new_service_provide_id' => 'unique:service_provider_id,type|max:255', 
            'custormer_id' => 'required|exists:custormer_id,type|max:255',
            'new_custormer_id' => 'unique:custormer_id,type|max:255',
            'time' => 'required|exists:time,type|max:255',
            'new_time' => 'unique:time,type|max:255',
            'status' => 'required|exists:status,type|max:255',
            'new_status' => 'unique:status,type|max:255',

		]);

		if ($validator->fails()) {
			$out = [
		        'success' => false,
		        'message' => $validator->messages()
		    ];
			return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
		}else{

            $update = [];
            if(!empty($request->get('new_service_provide_id')) ){
                $update['provider_services_id']  =$request->get('new_provider_services_id') ;
            }
            if(!empty($request->get('new_service_provider_id')) ){
                $update['service_provider_id']  =$request->get('new_service_provider_id') ;
            }
             if(!empty($request->get('new_custormer_id')) ){
                $update['custormer_id']  =$request->get('new_custormer_id') ;
            }
             if(!empty($request->get('new_date')) ){
                $update['date']  =$request->get('new_date') ;
            }
              if(!empty($request->get('new_time')) ){
                $update['time']  =$request->get('new_time') ;
            }
            if(!empty($request->get('new_status')) ){
                $update['status']  =$request->get('new_status') ;
            }
             

	    	DB::table('appointments')
            ->where('id', $request->get('id'))
            ->update($update);

	    	$out = [
		        'success' => true,
		        'id'=>$request->get('id'),
		        'message' => 'Appointment updated OK'
		    ];

    		return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
    	}
    }

    /**
     * curl -i -XDELETE -H "content-type:application/json" --data 
     * '{"id":4}' 
     * 'http://127.0.0.1:8000/api/appointments/delete'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function delete(Request $request)
    {
    	
    	$validator = Validator::make($request->all(),[
		    'id' => 'required|exists:appointments,id'
		]);

		if ($validator->fails()) {
			$out = [
		        'success' => false,
		        'message' => $validator->messages()
		    ];
			return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
		}else{
	    	DB::table('appointment')
            ->where('id', $request->get('id'))
            ->update(['status_id' => DBStatus::RECORD_DELETED]);

	    	$out = [
		        'success' => true,
		        'id'=>$request->get('id'),
		        'message' => 'appointments marked deleted OK'
		    ];

    		return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
    	}
    }

}


