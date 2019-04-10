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
use Illuminate\Support\Facades\URL;


class ServiceProvidersController extends Controller{

    /**
     * Display the specified service providers.
     * curl -i -XGET -H "content-type:application/json" 
     * http://127.0.0.1:8000/api/service-providers/details/[user_id]
     *
     * @param  \App\Category $category
     *
     * @return JSON 
     */
 
    public function details($user_id=null, Request $request)
    {
        $req= $request->all();
        $page = 1; 
        $limit =null;
        //die(print_r($req, 1));

        $image_url = URL::to('/static/images/avatar/');
        $sp_providers_url =  URL::to('/static/images/service-providers/');
        
        $validator = Validator::make(['id'=>$user_id],
            ['user_id'=>'integer|exists:service_providers']
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
            $filter = " and sp.id = '" .$user_id . "' ";
        }

        $rawQuery = "SELECT sp.id, sp.type, sp.service_provider_name,sp.work_location, "
            . " sp.work_lat, sp.work_lng, sp.status_id, sp.overall_rating, "
            . " sp.overall_likes, sp.overall_dislikes, sp.created_at, sp.updated_at, "
            . " d.id_number, d.date_of_birth, d.gender,  d.passport_photo, "
            . " d.home_location work_phone_no, sp.business_description "
            . " FROM service_providers sp inner join user_personal_details  d "
            . " using(user_id) where 1=1 " . $filter ;

        //die($rawQuery);

        $results =[]; 
        $results['provider'] =  RawQuery::query($rawQuery)[0];
       
        $service_provider_id =  $user_id;

        $sql_provider_services = "select ps.id as provider_service_id,  "
            . " ps.service_provider_id, ps.service_id, s.service_name, ps.rating, "
            . " ps.description, ps.cost , ps.duration, ps.rating, ps.created_at, "
            . "  ps.updated_at from provider_services ps inner join services s on " 
            . " s.id = ps.service_id  where ps.service_provider_id = '" . $service_provider_id . "' ";

        $services = RawQuery::query($sql_provider_services);
        $results['services'] = $services;

        $working_hours_sql = "select service_day, time_from, time_to from operating_hours "
            . " where service_provider_id ='" . $service_provider_id . "'";

        $results['operating_hours'] = RawQuery::query($working_hours_sql);

        $portfolios_sql = "SELECT p.media_data, p.description  FROM  portfolios p "
            . " where service_provider_id = '" . $service_provider_id. "'" ;

        $results['portfolios'] = RawQuery::query($portfolios_sql);

        
        $reviews_sql = "SELECT date_format(r.created_at,'%d %M %Y') created_at,"
                . " r.provider_service_id, r.rating, r.review, "
                . " r.status_id, u.name as reviewer, u.email, s.service_name, "
                . " concat( '$image_url' , if(d.passport_photo is null, 'avatar-bg-1.png', "
                . " json_extract(d.passport_photo, '$.media_url')) ) as thumbnail "
                . " FROM  reviews r  inner join users u on u.id=r.user_id "
                . " inner join user_personal_details d on u.id = d.user_id "
                . " inner join provider_services ps on ps.id = r.provider_service_id "
                . " inner join services s on s.id = ps.service_id where "
                . " r.service_provider_id = '" . $service_provider_id . "' "
                . " order by r.id desc limit 5";

       //die($reviews_sql);

        $results['reviews'] = RawQuery::query($reviews_sql);

        //dd(HTTPCodes);
        Log::info('Extracted service service_providers results : '.var_export($results, 1));
        if(empty($results)){
            return Response::json($results, HTTPCodes::HTTP_NO_CONTENT );
        }
        return Response::json($results, HTTPCodes::HTTP_OK);

    }


    public function getwithserviceid($service_id=null, Request $request){
        $req= $request->all();
        $page = 1; 
        $limit =null;
        $sort = null;
        $sort_by = " order by sp.overall_likes desc, sp.overall_rating desc ";
        //die(print_r($req, 1));
        if(array_key_exists('page', $req)){
             $page = is_numeric($request['page']) ? $request['page'] : 1;
        }
        if(array_key_exists('limit', $req)){
             $limit = is_numeric($request['limit']) ? $request['limit'] : null;
        }

        if(array_key_exists('sort', $req)){
             $sort = $request['sort'];
        }
       
        $validator = Validator::make(['id'=>$service_id, 'sort'=>$sort],
            ['id'=>'integer|exists:services,id|nullable', 
             'sort' => 'in:since,overall_likes, overall_ratings,total_requests|nullable']
        );
        if($validator ->fails()){
            $out =[
               'sucess'=> false, 
               'message'=> $validator->messages()

            ];

            return Response::json($out,HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

        $filter= '';
        if(!is_null($service_id)){
            $filter = " and s.id = '" .$service_id . "' ";
        }

        if(!is_null($sort)){
            $sort_by = " order by $sort desc ";
        }

        $image_url = URL::to('/static/images/avatar/');
        $sp_providers_url =  URL::to('/static/images/service-providers/');

        // $rawQuery = "SELECT sp.id, sp.type, sp.service_provider_name,sp.work_location, "
        //     . " sp.work_lat, sp.work_lng, sp.status_id, sp.overall_rating, "
        //     . " sp.overall_likes, sp.overall_dislikes, sp.created_at, sp.updated_at, "
        //     . " d.id_number, d.date_of_birth, d.gender,  d.passport_photo, "
        //     . " d.home_location work_phone_no "
        //     . " FROM service_providers sp inner join user_personal_details  d "
        //     . " using(user_id) where sp.status_id "
        //     . " not in (" . DBStatus::RECORD_DELETED . ") " . $filter ;

        $rawQuery = "SELECT sp.id, s.service_name, sp.type, "
            . " (select count(*) from reviews where service_provider_id = sp.id "
            . " and provider_service_id=ps.id) as reviews, "
            . " sp.service_provider_name,  sp.business_description, sp.work_location, "
            . " sp.overall_rating, sp.overall_likes, sp.overall_dislikes, sp.created_at, "
            . " sp.updated_at,  d.id_number, d.date_of_birth, d.gender, d.passport_photo, "
            . " d.home_location, work_phone_no, total_requests, date_format(sp.created_at, '%b, %Y') as since, "
            . " concat( '$image_url' ,if(d.passport_photo is null, 'avatar-bg-1.png', "
            . " json_extract(d.passport_photo, '$.media_url'))) as thumbnail, "
            . " concat( '$sp_providers_url' ,if(sp.cover_photo is null, 'img-03.jpg', "
            . " json_extract(sp.cover_photo, '$.media_url')) ) as cover_photo "
            . " FROM provider_services ps inner join "
            . " service_providers sp on sp.id = ps.service_provider_id inner  join "
            . " user_personal_details  d using(user_id) inner join services s on "
            . " s.id = ps.service_id where sp.status_id =1  " 
            . $filter .    $sort_by ;

        //die($rawQuery);

        //die($rawQuery);
        $results = RawQuery::paginate($rawQuery, $page=$page, $limit=$limit);

        //dd(HTTPCodes);
        Log::info('Extracted service service_providers results : '.var_export($results, 1));
        if(empty($results)){
            return Response::json($results, HTTPCodes::HTTP_NO_CONTENT );
        }
        return Response::json($results, HTTPCodes::HTTP_OK);


    }

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
        $sort = null;
        $sort_by = " order by sp.overall_likes desc, sp.overall_rating desc ";
        //die(print_r($req, 1));
        if(array_key_exists('page', $req)){
             $page = is_numeric($request['page']) ? $request['page'] : 1;
        }
        if(array_key_exists('limit', $req)){
             $limit = is_numeric($request['limit']) ? $request['limit'] : null;
        }

        if(array_key_exists('sort', $req)){
             $sort = $request['sort'];
        }
       
    	$validator = Validator::make(['id'=>$user_id, 'sort'=>$sort],
    		['user_id'=>'integer|exists:users,id|nullable', 
             'sort' => 'in:since,overall_likes, overall_ratings,total_requests|nullable']
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

        if(!is_null($sort)){
            $sort_by = " order by $sort desc ";
        }

        // $rawQuery = "SELECT sp.id, sp.type, sp.service_provider_name,sp.work_location, "
        //     . " sp.work_lat, sp.work_lng, sp.status_id, sp.overall_rating, "
        //     . " sp.overall_likes, sp.overall_dislikes, sp.created_at, sp.updated_at, "
        //     . " d.id_number, d.date_of_birth, d.gender,  d.passport_photo, "
        //     . " d.home_location work_phone_no "
        //     . " FROM service_providers sp inner join user_personal_details  d "
        //     . " using(user_id) where sp.status_id "
        //     . " not in (" . DBStatus::RECORD_DELETED . ") " . $filter ;

        $rawQuery = "SELECT sp.id, s.service_name, sp.type, "
            . " (select count(*) from reviews where service_provider_id = sp.id "
            . " and provider_service_id=ps.id) as reviews, "
            . " sp.service_provider_name,  sp.business_description, sp.work_location, "
            . " sp.overall_rating, sp.overall_likes, sp.overall_dislikes, sp.created_at, "
            . " sp.updated_at,  d.id_number, d.date_of_birth, d.gender, d.passport_photo, "
            . " d.home_location, work_phone_no, total_requests, date_format(sp.created_at, '%b, %Y') as since, "
            . " if(d.passport_photo is null, 'avatar-bg-1.png', "
            . " json_extract(d.passport_photo, '$.media_url')) as thumbnail, "
            . " if(sp.cover_photo is null, 'img-03.jpg', "
            . " json_extract(sp.cover_photo, '$.media_url')) as cover_photo "
            . " FROM provider_services ps inner join "
            . " service_providers sp on sp.id = ps.service_provider_id inner  join "
            . " user_personal_details  d using(user_id) inner join services s on "
            . " s.id = ps.service_id where sp.status_id =1  " 
            . $filter .    $sort_by ;

        //die($rawQuery);

        //die($rawQuery);
        $results = RawQuery::paginate($rawQuery, $page=$page, $limit=$limit);

        //dd(HTTPCodes);
        Log::info('Extracted service service_providers results : '.var_export($results, 1));
        if(empty($results)){
        	return Response::json($results, HTTPCodes::HTTP_NO_CONTENT );
        }
        return Response::json($results, HTTPCodes::HTTP_OK);

    }


    /**
     * Display the popular service providers.
     * curl -i -XGET -H "content-type:application/json" 
     * http://127.0.0.1:8000/api/service-providers/popular
     *
     * @param  \App\Category $category
     *
     * @return JSON 
     */
 
    public function popular()
    {
       
        $rawQuery = "SELECT sp.id, sp.type, sp.service_provider_name, "
            . " sp.business_description, sp.work_location,  sp.overall_rating, "
            . " sp.overall_likes, sp.overall_dislikes, sp.created_at, sp.updated_at, "
            . " d.id_number, d.date_of_birth, d.gender,  d.passport_photo,  "
            . " d.home_location, work_phone_no  FROM service_providers sp inner "
            . " join user_personal_details  d using(user_id) where sp.status_id =1 "
            . " order by overall_rating desc, overall_likes desc limit 20 ";

        $results = RawQuery::query($rawQuery);

        //dd(HTTPCodes);
        Log::info('Extracted popular service service_providers results : '.var_export($results, 1));
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


