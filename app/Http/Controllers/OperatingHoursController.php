<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;    
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;    
use App\Utilities\HTTPCodes;
use App\Utilities\DBStatus;
use Illuminate\Support\Facades\Validator;
use App\Utilities\RawQuery;

class OperatingHoursController extends Controller
{

    /**
     * Display the provider service details 
     * Default to highly rated services.
     * curl -i -XGET -H "content-type:application/json" 
     * http://127.0.0.1:8000/api/service-providers/operating-hours/get/{id}
     *
     * @param  \App\Category $category
     *
     * @return JSON 
     */
 

    public function get($id=null){

        $validator = Validator::make(['service_provider_id'=>$id],
            ['service_provider_id'=>'integer|exists:service_providers,id|nullable']
        );
        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }
        $filter = '';
        if(!is_null($id)){
            $filter = " and op.service_provider_id = '" .$id . "' ";
        }
         

        $query = "select id, service_day, time_from, time_to from operating_hours op "
            . " where 1=1 ". $filter . " and status_id = ".DBStatus::TRANSACTION_ACTIVE;

        $operating_hours = RawQuery::paginate($query);


        Log::info('Query : ' . $query);

       
        //dd(HTTPCodes);
        Log::info('Extracted operating hours results : ' . var_export($operating_hours, 1));

        if(empty($operating_hours)){
            return Response::json($operating_hours[0], HTTPCodes::HTTP_NO_CONTENT );
        }
        return Response::json($operating_hours, HTTPCodes::HTTP_OK);

    }


    /**
     * curl -i -XPOST -H "content-type:application/json" 
     * --data '{"service_provider_id":3,"time_from":"09:00","day":"Tuesday", "time_to":"15:00"} 
     * 'http://127.0.0.1:8000/api/service-providers/operating-hours/create'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function create(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'service_provider_id' => 'required|exists:service_providers,id',
            'day' => 'required',
            'time_from' => 'required|string',
            'time_to' =>'required|string'
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }else{

            DB::insert("insert into operating_hours (service_provider_id,"
                . " service_day, time_from, time_to, created_at, updated_at)"
                . " values (:service_provider_id, :day, :time_from, "
                . " :time_to, now(),  now())  ", 
                    [
                        'service_provider_id'=> $request->get('service_provider_id'),
                        'day'=>$request->get('day'),
                        'time_from'=>$request->get('time_from'),
                        'time_to'=>$request->get('time_to')
                    ]
                );

            $out = [
                'success' => true,
                'id'=>DB::getPdo()->lastInsertId(),
                'message' => 'Provider service Created'
            ];

            return Response::json($out, HTTPCodes::HTTP_CREATED);
        }
    }

    /**
     * curl -i -XPUT -H "content-type:application/json" 
     * --data '{"id":3,"time_from":"09:00","day":"Tuesday", "time_to":"15:00"}'
     * 'http://127.0.0.1:8000/api/service-providers/operating-hours/update'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function update(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:operating_hours,id',
            'time_from' => 'string|nullable',
            'time_to' =>'string|nullable',
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }else{
            $update = [];
            if(!empty($request->get('time_from')) ){
                $update['time_from']  =$request->get('time_from') ;
            }
            if(!empty($request->get('time_to')) ){
                $update['time_to']  =$request->get('time_to') ;
            }
            

            DB::table('operating_hours')
                ->where('id', $request->get('id'))
                ->update($update);

            $out = [
                'success' => true,
                'user_id'=>$request->get('id'),
                'message' => 'Operation hours updated OK'
            ];

            return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
        }
    }

    /**
     * curl -i -XDELETE -H "content-type:application/json" 
     * --data '{"id":1}' 
     * 'http://127.0.0.1:8000/api/service-providers/operating-hours/delete'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function delete(Request $request)
    {
	
	Log::info($request->all());
        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:operating_hours,id'
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }else{
           
            DB::table('operating_hours')
                ->where('id', $request->get('id'))
                ->update(['status_id' => DBStatus::TRANSACTION_DELETED]);

            $out = [
                'success' => true,
                'user_id'=>$request->get('id'),
                'message' => 'Operating hour deleted OK'
            ];

            return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
        }
    }


   

    

}
