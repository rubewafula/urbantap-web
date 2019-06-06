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
use Illuminate\Support\Facades\URL;


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
 

    public function get($id=null, Request $request){

        $validator = Validator::make(['service_provider_id'=>$id],
            ['service_provider_id'=>'integer|exists:service_providers,id|nullable']
        );

        //die(print_r($request->all()));
        Log::info("Provider services search req", $request->all());
        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

        $validator = Validator::make($request->all(),[
            'service' => 'nullable|string',
            'service_time' =>'nullable|date_format:Y-m-d H:i',
            'location' =>'nullable'
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }
        
        $filter = '';
        if(!is_null($id)){
            $filter = " and ps.id = '" .$id . "' ";
        }else {
            $filter  = " and s.service_name like  :service "
            . " and (work_location like :location or work_location_city like :location2) group by sp.id ";
        }

        $image_url = URL::to('/storage/static/image/avatar/');
        $sp_providers_url =  URL::to('/storage/static/image/service-providers/');
        $icon_url = URL::to('/storage/static/image/icons/');
        $profile_url =  URL::to('/storage/static/image/profiles/');
        
        // $image_url = URL::to('/storage/image/avatar/');
        // $sp_providers_url =  URL::to('/storage/image/service-providers/');
        // $p_services_url =  URL::to('/storage/image/provider-services/');
       

         if(empty($request->get('service_time')) )
         {
            $request->service_time= date('Y-m-d H:i');
         } 
         $params =[
                #'service_date'=>$request->service_time,
                #'service_date2'=>$request->service_time,
                'service'=>'%'.$request->service.'%',
                'location'=>'%'.$request->location.'%',
                'location2'=>'%'.$request->location.'%'
            ];
        //echo print_r($params, 1);


         $query = "select sp.id as service_provider_id, sp.type, group_concat(s.id) as service_id, "
            . " group_concat(s.service_name) as service_name, sp.service_provider_name, "
            . " sp.work_location, sp.work_lat, sp.work_lng, sp.status_id, sp.overall_rating, "
            . " sp.service_provider_name, sp.overall_likes, sp.overall_dislikes, sp.created_at,"
            . " sp.updated_at,  d.id_number, d.date_of_birth, d.gender, "
            . " concat( '$image_url' ,'/', if(d.passport_photo is null, 'avatar-bg-1.png', "
            . " json_extract(d.passport_photo, '$.media_url')) ) as thumbnail, "
            . " concat( '$sp_providers_url' , '/', if(sp.cover_photo is null, 'img-03.jpg', "
            . " JSON_UNQUOTE(json_extract(sp.cover_photo, '$.media_url')))) as cover_photo, "
            . " d.home_location, d.gender, work_phone_no, sp.business_description,  "
            . " date_format(sp.created_at, '%b, %Y') as since, total_requests, "  
            . " (select count(*) from reviews where service_provider_id = sp.id) as reviews "
            . " from provider_services ps inner join service_providers sp on "
            . " ps.service_provider_id = sp.id  inner join services s on s.id = ps.service_id left  join "
            . " user_personal_details  d using(user_id)  "
            . " where 1=1 ". $filter ;

        if(!is_null($id)){
            $results = RawQuery::query( $query, $params=$params);
            if(!empty($results)){
                $results = $results[0];
            }
        }else{
            $results =  RawQuery::paginate(
                 $query,
                 $page = null, $limit = null, 
                 $params=$params
            );
        }


        // $query = "select s.id as service_id, s.service_name, ps.description, "
        //     . " c.category_name, ps.cost, ps.duration, ps.rating from provider_services "
        //     . " ps inner join services s on s.id=ps.service_id inner join categories "
        //     . "c on c.id =s.category_id where 1=1 ". $filter;

        // $provider_services = RawQuery::paginate($query);

        //echo   $query;

        //dd(HTTPCodes);
        Log::info('Extracted statuses results : ' . var_export($results, 1));

        if(empty($results)){
            return Response::json([], HTTPCodes::HTTP_OK );
        }
        return Response::json($results, HTTPCodes::HTTP_OK);

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
