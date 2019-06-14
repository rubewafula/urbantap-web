<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;    
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;    
use App\Utilities\HTTPCodes;
use App\Utilities\DBStatus;
use App\Utilities\Utils;
use Illuminate\Support\Facades\Validator;
use App\Utilities\RawQuery;
use Illuminate\Support\Facades\URL;


class ProviderServicesController extends Controller
{
  
    /**
     * Get specific provider service details given provider_service_id
     */
    public function provider_service_detail($id){

        $validator = Validator::make(['provider_service_id'=>$id],
            ['provider_service_id'=>'integer|exists:provider_services,id|required']
        );

        //die(print_r($request->all()));
        Log::info("Provider service ID ".$id);
        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }


        $image_url          = Utils::IMAGE_URL;
        $sp_providers_url   = Utils::SERVICE_PROVIDERS_URL;
        $icon_url           = Utils::ICONS_URL;
        $profile_url        = Utils::PROFILE_URL;
        $p_services_url     = Utils::PROVIDER_PORTFOLIOS_URL;
        $service_image_url =  Utils::SERVICE_IMAGE_URL;

        $provideQ = "select sp.id as service_provider_id, sp.id as id, "
            . " sp.type, s.id as service_id, "
            . " s.service_name, ps.cost as service_cost, ps.description, ps.duration, "
            . " sp.work_location, sp.work_lat, sp.work_lng, sp.status_id, sp.overall_rating, "
            . " sp.service_provider_name as business_name, sp.overall_likes, "
            . " sp.overall_dislikes, sp.created_at,"
            . " sp.updated_at,  d.id_number, d.date_of_birth, d.gender, "
            . " concat( '$image_url' ,'/', if(d.passport_photo is null, 'avatar-bg-1.png', "
            . " json_extract(d.passport_photo, '$.media_url')) ) as thumbnail, "
            . " concat( '$sp_providers_url' , '/', if(sp.cover_photo is null, 'img-03.jpg', "
            . " JSON_UNQUOTE(json_extract(sp.cover_photo, '$.media_url')))) as cover_photo, "
            . " concat( '$service_image_url' , '/', if(ps.media_url is null, '2.jpg', "
            . " JSON_UNQUOTE(json_extract(ps.media_url, '$.media_url')))) as service_photo, "
            . " d.home_location, d.gender, work_phone_no, sp.business_description,  "
            . " date_format(sp.created_at, '%b, %Y') as since, total_requests, "  
            . " (select count(*) from reviews where service_provider_id = sp.id) as reviews "
            . " from provider_services ps inner join service_providers sp on ps.service_provider_id = sp.id inner " 
            . " join services s on s.id=ps.service_id left  join user_personal_details  d using(user_id)  "
            . " where ps.id =:id ";

        Log::info("Query to get provider service details" . $provideQ );
        
        $service_data =  RawQuery::query( $provideQ,['id'=>$id]);

        Log::info('Extracted statuses results : ' . var_export($service_data, 1));

        if(empty($service_data)){
            return Response::json(new stdClass, HTTPCodes::HTTP_OK );
        }

        $service_provider_id = optional(array_get($service_data, 0))->id;

        $working_hours_sql = "select id,service_day, time_from, time_to from operating_hours "
            . " where service_provider_id ='" . $service_provider_id . "'"
            . " and status_id=". DBStatus::TRANSACTION_ACTIVE;

        $service_data[0]->operating_hours = RawQuery::query($working_hours_sql);
        return Response::json(array_get($service_data, 0), HTTPCodes::HTTP_OK);

    }



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
            'location' =>'nullable|string'
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }
        
        $request->service  = $request->service ?: "";
        $request->service_time = $request->service_time ?: "";
        $request->location = $request->location ?: "" ;

        $filter = $service_filter = '';
        if(!is_null($id)){
            $filter = " and sp.id = '" .$id . "' ";
        }else {
            $service_filter = " and s.service_name like  :service ";
            $filter  = " and (work_location like :location or work_location_city like :location2) group by sp.id ";
        }

        $image_url          = Utils::IMAGE_URL;
        $sp_providers_url   = Utils::SERVICE_PROVIDERS_URL;
        $icon_url           = Utils::ICONS_URL;
        $profile_url        = Utils::PROFILE_URL;
        $p_services_url     = Utils::PROVIDER_PORTFOLIOS_URL;
        $service_image_url  =  Utils::SERVICE_IMAGE_URL;
        
         $service_params = [];
         if($service_filter){
             $service_params = [ 'service'=>'%'.$request->service ?: "" .'%',];
         }
         $date_params = ['service_date'=>$request->service_time ?: "" ,
          'service_date2'=>$request->service_time ?: "" ,];

         $location_params =[
             'location'=>'%'.$request->location ?: "" .'%',
             'location2'=>'%'.$request->location ?: "" .'%'
         ];


        $provideQ = "select sp.id as service_provider_id, sp.id as id, "
            . " sp.type, sp.service_provider_name, "
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
            . " from service_providers sp left  join user_personal_details  d using(user_id)  "
            . " where 1=1 ". $filter ;

        //echo print_r($params, 1);
        $provider_data =  RawQuery::paginate( $provideQ, $page = null, $limit = null, 
            $params=$location_params);

        $results = [];
        foreach ($provider_data['result'] as $key => $provider) {

            $serviceQ = "select ps.id as provider_service_id, s.id as service_id, "
                . " s.service_name, ps.cost as service_cost, ps.description, ps.duration, ps.created_at,"
                . " concat( '$service_image_url' , '/', if(ps.media_url is null, '2.jpg', "
                . " JSON_UNQUOTE(json_extract(ps.media_url, '$.media_url')))) as service_photo, "
                . " ps.updated_at from provider_services ps inner join services s "
                . " on s.id = ps.service_id  where  ps.service_provider_id = :spid ". $service_filter ;

            Log::info("Provider data form service fetch " . $provider->service_provider_id);
            $service_params['spid'] = $provider->service_provider_id;
            Log::info("Service params ==> " . print_r($service_params, 1));
            $service_results = RawQuery::query( $serviceQ, $service_params);
            $provider->services= $service_results;

            array_push($results, $provider);
        }

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

            $service_photo= $this->upload_servicephoto($request);

            if($service_photo !=  FALSE)
            {
                  $service_photo = json_encode($service_photo);
            }else{
                 $service_photo = NULL;
            }

            DB::insert("insert into provider_services (service_provider_id,"
                . " service_id, description, cost, duration, media_url, created_at, updated_at)"
                . " values (:service_provider_id, :service_id, :description, "
                . " :cost, :duration, :media_url, now(),  now())  ", 
                    [
                        'service_provider_id'=> $request->get('service_provider_id'),
                        'service_id'=>$request->get('service_id'),
                        'description'=>$request->get('description'),
                        'media_url' => $service_photo,
                        'cost'=>$request->get('cost'),
                        'duration'=>$request->get('duration')
                    ]
                );

            $psid = DB::getPdo()->lastInsertId();
            $out = [
                'success' => true,
                'id'=> $psid,
                'service' => $this->get_service_data($psid),
                'message' => 'Provider service Created'
            ];

            return Response::json($out, HTTPCodes::HTTP_CREATED);
        }
    }


    private function get_service_data($id){
        $service_image_url = URL::to('/storage/static/image/services/');

        $sql_provider_services = "select ps.id as provider_service_id,  c.category_name, c.id as category_id, "
            . " concat('$service_image_url' ,'/', if(ps.media_url is null, '2.jpg', "
            . " JSON_UNQUOTE(json_extract(ps.media_url, '$.media_url'))) ) as service_photo, "
            . " ps.service_provider_id, ps.service_id, s.service_name, ps.rating, "
            . " ps.description, ps.cost , ps.duration, ps.rating, ps.created_at, "
            . "  ps.updated_at from provider_services ps inner join services s on "
            . " s.id = ps.service_id inner join categories c on s.category_id = c.id "
            . " where ps.id = :id and ps.status_id =:active";
        $service_data = RawQuery::query($sql_provider_services, ['id'=> $id, 'active'=>DBStatus::TRANSACTION_ACTIVE]);
        return array_get($service_data, 0);
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

            $service_photo= $this->upload_servicephoto($request);

            if($service_photo !=  FALSE)
            {

                  $update['media_url'] = json_encode($service_photo);

            }

            DB::table('provider_services')
                ->where('id', $request->get('id'))
                ->update($update);

            $out = [
                'success' => true,
                'user_id'=>$request->get('id'),
                'service' => $this->get_service_data($request->get('id')),
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
                ->update(['status_id' => DBStatus::TRANSACTION_DELETED]);

            $out = [
                'success' => true,
                'user_id'=>$request->get('id'),
                'message' => 'Provider service deleted OK'
            ];

            return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
        }
    }

    public  function  upload_servicephoto($request)
    {
        return Utils::upload_media($request, 'services', 'service_image');
    }



}
