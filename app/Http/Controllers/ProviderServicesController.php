<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;    
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;    
use App\Utilities\HTTPCodes;
use App\Utilities\DBStatus;
use Illuminate\Support\Facades\Validator;
use App\Utilities\RawPaginate;

class ProviderServicesController extends Controller
{

    /**
     * Display the provider service details 
     * Default to highly rated services.
     * curl -i -XGET -H "content-type:application/json" 
     * http://127.0.0.1:8000/api/provider-services/get/{id}
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
            $filter = " and ps.service_provider_id = '" .$id . "' ";
        }
         

        $query = "select s.id as service_id, s.service_name, ps.description, "
            . " c.category_name, ps.cost, ps.duration, ps.rating from provider_services "
            . " ps inner join services s on s.id=ps.service_id inner join categories "
            . "c on c.id =s.category_id where 1=1 ". $filter;

        $provider_services = RawPaginate::paginate($query);


        Log::info('Query : ' . $query);

       
        //dd(HTTPCodes);
        Log::info('Extracted statuses results : ' . var_export($provider_services, 1));

        if(empty($provider_services)){
            return Response::json($provider_services[0], HTTPCodes::HTTP_NO_CONTENT );
        }
        return Response::json($provider_services, HTTPCodes::HTTP_OK);

    }


    /**
     * curl -i -XPOST -H "content-type:application/json" 
     * --data '{"service_provider_id":2,"service_id":1,
     * "description":"Cut wam service 23","cost":1000,"duration":45}' 
     * 'http://127.0.0.1:8000/api/provider-service/create'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function create(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'service_provider_id' => 'required|exists:service_providers,id',
            'service_id' => 'required|exists:services,id',
            'description' => 'required|string',
            'cost' =>'integer',
            'duration' =>'integer',
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }else{

            DB::insert("insert into provider_services (service_provider_id,"
                . " service_id, description, cost, duration, created_at, updated_at)"
                . " values (:service_provider_id, :service_id, :description, "
                . " :cost, :duration, now(),  now())  ", 
                    [
                        'service_provider_id'=> $request->get('service_provider_id'),
                        'service_id'=>$request->get('service_id'),
                        'description'=>$request->get('description'),
                        'cost'=>$request->get('cost'),
                        'duration'=>$request->get('duration')
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
     * curl -i -XPOST -H "content-type:application/json" 
     * --data '{"service_provider_id":2,"service_id":1,
     * "description":"Cut wam service 23","cost":1000,"duration":45}' 
     * 'http://127.0.0.1:8000/api/provider-service/update'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function update(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:provider_services,id',
            'description' => 'string|nullable',
            'cost' =>'integer|nullable',
            'duration' =>'integer|nullable',
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }else{
            $update = [];
            if(!empty($request->get('description')) ){
                $update['description']  =$request->get('description') ;
            }
            if(!empty($request->get('cost')) ){
                $update['cost']  =$request->get('cost') ;
            }
            if(!empty($request->get('duration')) ){
                $update['duration']  =$request->get('duration') ;
            }

            DB::table('provider_services')
                ->where('id', $request->get('id'))
                ->update($update);

            $out = [
                'success' => true,
                'user_id'=>$request->get('id'),
                'message' => 'Provider service updated OK'
            ];

            return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
        }
    }

    /**
     * curl -i -XPOST -H "content-type:application/json" 
     * --data '{"id":1}' 
     * 'http://127.0.0.1:8000/api/provider-service/delete'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function delete(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:provider_services,id'
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }else{
           
            DB::table('provider_services')
                ->where('id', $request->get('id'))
                ->update(['status_id' => DBStatus::RECORD_DELETED]);

            $out = [
                'success' => true,
                'user_id'=>$request->get('id'),
                'message' => 'Provider service deleted OK'
            ];

            return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
        }
    }


   

    

}
