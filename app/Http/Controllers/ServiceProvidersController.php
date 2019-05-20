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
use Illuminate\Support\Facades\Storage;
use IlluminateSupportFacadesLog;
use DateTime;
use DateInterval;



class ServiceProvidersController extends Controller{

    private $image_ext = ['jpg', 'jpeg', 'png', 'gif'];
    private $audio_ext = ['mp3', 'ogg', 'mpga', 'iff', 'm3u', 'mpa','wav', 'wma', 'aif'];
    private $video_ext = ['mp4', 'mpeg','3g2','3gp','asf','flv','m4v','mpg','swf','vob', 'wmv'];



     private function allExtensions()
    {
        return array_merge($this->image_ext, $this->audio_ext, $this->video_ext);
    }


    /**
    * provider time slots 
    * Default slots for today
    */
    public function timeslots(Request $request){

        date_default_timezone_set('Africa/Nairobi');
        $req= $request->all();
        $date = new DateTime();
        $date->sub(new DateInterval('P1D'));
      
        $validator = Validator::make($req,
            ['service_provider_id'=>'integer|exists:service_providers,id', 
             'slot_date' => 'date_format:Y-m-d|after:'. $date->format('Y-m-d'),]
        );
        if($validator ->fails()){
            $out =[
               'sucess'=> false, 
               'message'=> $validator->messages()

            ];

            return Response::json($out,HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

        $slots = [];
        $slot_data = $request->get('slot_date');

        $slot_date = DateTime::createFromFormat('Y-m-d H:i', $slot_data . " 00:00");

        $date_now = new DateTime();

        $date_5min_roundup = sprintf("%d minutes %d seconds", 
            $date_now->format("i") % 5, 
            $date_now->format("s")
        );
        $round_date = $date_now->sub(\DateInterval::createFromDateString($date_5min_roundup));
        echo  $slot_date->format('Y-m-d H:i');

        $provider_booking_sql = "select booking_time, booking_duration from bookings where service_provider_id=:pid and date(booking_time)=:booking_date and status_id=:st ";
        $params = ['pid'=>$request->get('service_provider_id'),
                    'booking_date'=> $slot_data, 'st'=>DBStatus::BOOKING_PAID];
        $booked_records = RawQuery::query($provider_booking_sql, $params);
        $booked_slots = [];
        foreach($booked_records as $key=>$record){
            $start = $record->booking_time;
            $bb_date = DateTime::createFromFormat('Y-m-d H:i', $start);
            $ls_date = DateTime::createFromFormat('Y-m-d H:i', $start)
                ->add(new DateInterval('PT'.$record->booking_duration.'M'));
            do{

                array_push($booked_slots, $bb_date->format("H:i"));
                $bb_date->add(new DateInterval('PT15M'));

            }while($bb_date < $ls_date );

        }


        while($slot_data == $slot_date->format('Y-m-d')){
            Log::info("Checking " . $slot_data  . "==> " . $slot_date->format('Y-m-d H:i'));
            if($slot_date <  $round_date ){
                $slot_date = $round_date;
                //shooting the pegion no closer that 30minutes away
                $slot_date->add(new DateInterval('PT15M'));
            }

            $slot_date->add(new DateInterval('PT15M'));
            Log::info("New slot dat " . $slot_date->format('Y-m-d H:i') ); 
            if($slot_data == $slot_date->format('Y-m-d')){

                if(!in_array($slot_date->format('H:i'), $booked_slots)){
                    array_push($slots, $slot_date->format('H:i'));
                }else{
                    Log::info("Browsing a booked time slot for SP => "
                        . $request->get('service_provider_id') . "Time =>" . $slot_date->format('H:i'));
                }
                
            }
            
        }

        return Response::json($slots, HTTPCodes::HTTP_OK);



    }

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

        $image_url = URL::to('/storage/static/image/avatar/');
        $sp_providers_url =  URL::to('/storage/static/image/service-providers/');
        $icon_url = URL::to('/storage/static/image/icons/');
        $profile_url =  URL::to('/storage/static/image/profiles/');


        // $image_url = URL::to('/storage/image/avatar/');
        // $sp_providers_url =  URL::to('/storage/image/service-providers/');
        $p_services_url =  URL::to('/storage/static/image/provider-services/');
        
        $validator = Validator::make(['id'=>$user_id],
            ['user_id'=>'integer|exists:service_providers']
        );
        if($validator ->fails()){
            $out =[
                'success'=> false, 
               'message'=> $validator->messages()

            ];

            return Response::json($out,HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

       
        $filter= '';
        if(!is_null($user_id)){
            $filter = " and sp.id = '" .$user_id . "' ";
        }

        $rawQuery = "SELECT sp.id,  "
            . " (select count(*) from reviews where service_provider_id=sp.id) as reviews, "
            . " (select group_concat(distinct category_name) from categories c inner join services ss " 
            . " on c.id = ss.category_id  inner join provider_services ps "
            . " on ss.id = ps.service_id where "
            . " ps.service_provider_id=sp.id ) as service_name, "
            . " sp.service_provider_name,  sp.business_description, sp.work_location, "
            . " sp.overall_rating, sp.overall_likes, sp.overall_dislikes, sp.created_at, "
            . " sp.updated_at,  d.id_number, d.date_of_birth, d.gender, d.passport_photo, "
            . " d.home_location, work_phone_no, total_requests, date_format(sp.created_at, '%b, %Y') as since, "
            . " concat('$profile_url' , '/', (if(d.passport_photo is null, 'avatar-bg-1.png', "
            . " JSON_UNQUOTE(json_extract(d.passport_photo, '$.media_url') ))) ) as thumbnail, "
            . " concat( '$sp_providers_url' , '/', if(sp.cover_photo is null, 'img-03.jpg', "
            . " JSON_UNQUOTE(json_extract(sp.cover_photo, '$.media_url')))) as cover_photo "
            . " FROM  service_providers sp left join "
            . " user_personal_details  d using(user_id) where 1=1 "  . $filter ;

        //die($rawQuery);

        $results =[]; 
        $provider_data =  RawQuery::query($rawQuery);

        if(empty($provider_data)){
            return Response::json(['success'=>false, 'message'=>'Could not fetch provider data'], HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

        $results['provider'] = $provider_data[0];
       
        $service_provider_id =  $user_id;

        $sql_provider_services = "select ps.id as provider_service_id,  "
            . " concat('$p_services_url' ,'/', if(ps.media_url is null, '2.jpg', "
            . " JSON_UNQUOTE(json_extract(ps.media_url, '$.media_url'))) ) as service_photo, "
            . " ps.service_provider_id, ps.service_id, s.service_name, ps.rating, "
            . " ps.description, ps.cost , ps.duration, ps.rating, ps.created_at, "
            . "  ps.updated_at from provider_services ps inner join services s on " 
            . " s.id = ps.service_id  where ps.service_provider_id = '" . $service_provider_id . "' ";

       

        $services = RawQuery::query($sql_provider_services);
        $results['services'] = $services;

        $working_hours_sql = "select id, service_day, time_from, time_to from operating_hours "
            . " where service_provider_id ='" . $service_provider_id . "'";

        $results['operating_hours'] = RawQuery::query($working_hours_sql);

        $portfolios_sql = "SELECT  "
            . " concat('$p_services_url' ,'/', if(media_data is null, '2.jpg', "
            . " JSON_UNQUOTE(json_extract(media_data, '$.media_url'))) ) as media_photo, " 
            . " p.description  FROM  portfolios p "
            . " where service_provider_id = '" . $service_provider_id. "'" ;

        $results['portfolios'] = RawQuery::query($portfolios_sql);

        
        $reviews_sql = "SELECT date_format(r.created_at,'%d %M %Y') created_at,"
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

        $image_url = URL::to('/storage/static/image/avatar/');
        $sp_providers_url =  URL::to('/storage/static/image/service-providers/');
        $icon_url = URL::to('/storage/static/image/icons/');
        $profile_url =  URL::to('/storage/static/image/profiles/');

        // $image_url = URL::to('/storage/images/avatar/');
        // $sp_providers_url =  URL::to('/storage/images/service-providers/');


        $rawQuery = "SELECT sp.id,  "
            . " (select count(*) from reviews where service_provider_id=sp.id) as reviews, "
            . " (select group_concat(distinct category_name) from categories c inner join services ss " 
            . " on c.id = ss.category_id  inner join provider_services ps "
            . " on ss.id = ps.service_id where "
            . " ps.service_provider_id=sp.id ) as service_name, "
            . " sp.service_provider_name,  sp.business_description, sp.work_location, "
            . " sp.overall_rating, sp.overall_likes, sp.overall_dislikes, sp.created_at, "
            . " sp.updated_at,  d.id_number, d.date_of_birth, d.gender, d.passport_photo, "
            . " d.home_location, work_phone_no, total_requests, date_format(sp.created_at, '%b, %Y') as since, "
            . " concat('$profile_url' , '/', (if(d.passport_photo is null, 'avatar-bg-1.png', "
            . " JSON_UNQUOTE(json_extract(d.passport_photo, '$.media_url') ))) ) as thumbnail, "
            . " concat( '$sp_providers_url' , '/', if(sp.cover_photo is null, 'img-03.jpg', "
            . " JSON_UNQUOTE(json_extract(sp.cover_photo, '$.media_url')))) as cover_photo "
            . " FROM  service_providers sp left join "
            . " user_personal_details  d using(user_id) where sp.status_id =1  " 
            . $filter .    $sort_by ;

        //die($rawQuery);

        $results = RawQuery::paginate($rawQuery, $page=$page, $limit=$limit);

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

        // $image_url = URL::to('/storage/image/avatar/');
        // $sp_providers_url =  URL::to('/storage/image/service-providers/');

        $image_url = URL::to('/storage/static/image/avatar/');
        $sp_providers_url =  URL::to('/storage/static/image/service-providers/');
        $icon_url = URL::to('/storage/static/image/icons/');
        $profile_url =  URL::to('/storage/static/image/profiles/');


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


        $rawQuery = "SELECT sp.id,  "
            . " (select count(*) from reviews where service_provider_id=sp.id) as reviews, "
            . " (select group_concat(distinct category_name) from categories c inner join services ss " 
            . " on c.id = ss.category_id  inner join provider_services ps "
            . " on ss.id = ps.service_id where "
            . " ps.service_provider_id=sp.id ) as service_name, "
            . " sp.service_provider_name,  sp.business_description, sp.work_location, "
            . " sp.overall_rating, sp.overall_likes, sp.overall_dislikes, sp.created_at, "
            . " sp.updated_at,  d.id_number, d.date_of_birth, d.gender, d.passport_photo, "
            . " d.home_location, work_phone_no, total_requests, date_format(sp.created_at, '%b, %Y') as since, "
            . " concat('$profile_url' , '/', (if(d.passport_photo is null, 'avatar-bg-1.png', "
            . " JSON_UNQUOTE(json_extract(d.passport_photo, '$.media_url') ))) ) as thumbnail, "
            . " concat( '$sp_providers_url' , '/', if(sp.cover_photo is null, 'img-03.jpg', "
            . " JSON_UNQUOTE(json_extract(sp.cover_photo, '$.media_url')))) as cover_photo "
            . " FROM  service_providers sp left join "
            . " user_personal_details  d using(user_id) where sp.status_id =1  " 
            . $filter .    $sort_by ;

        
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

    /** popular**/



 
    public function popular()
    {
       
        $rawQuery = "SELECT sp.id,  "
            . " (select count(*) from reviews where service_provider_id=sp.id) as reviews, "
            . " (select group_concat(distinct category_name) from categories c inner join services ss " 
            . " on c.id = ss.category_id  inner join provider_services ps "
            . " on ss.id = ps.service_id where "
            . " ps.service_provider_id=sp.id ) as service_name, "
            . " sp.service_provider_name,  sp.business_description, sp.work_location, "
            . " sp.overall_rating, sp.overall_likes, sp.overall_dislikes, sp.created_at, "
            . " sp.updated_at,  d.id_number, d.date_of_birth, d.gender, d.passport_photo, "
            . " d.home_location, work_phone_no, total_requests, date_format(sp.created_at, '%b, %Y') as since, "
            . " concat('$profile_url' , '/', (if(d.passport_photo is null, 'avatar-bg-1.png', "
            . " JSON_UNQUOTE(json_extract(d.passport_photo, '$.media_url') ))) ) as thumbnail, "
            . " concat( '$sp_providers_url' , '/', if(sp.cover_photo is null, 'img-03.jpg', "
            . " json_extract(sp.cover_photo, '$.media_url'))) as cover_photo "
            . " FROM  service_providers sp left join "
            . " user_personal_details  d using(user_id) where sp.status_id =1  "
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




public  function  upload_coverphoto($request)
{

        $file = $request->file('cover_photo');
        if(is_null($file)){
            /** No file uploaded accept and proceeed **/
            return null;
        }
        $max_size = (int)ini_get('upload_max_filesize') * 1000;
        $all_ext = implode(',', $this->allExtensions());

        $this->validate($request, [
            'name' => 'nullable|unique:files',
            'file' => 'nullable|file|mimes:' . $all_ext . '|max:' . $max_size
        ]);

        $file = $request->file('cover_photo');

        if(is_null($file)){
            /** No file uploaded accept and proceeed **/
            return FALSE;
        }
        $ext = $file->getClientOriginalExtension();
        $size = $file->getClientSize();
        $name = preg_replace('/[^A-Za-z0-9\-]/', '-', $request->get('user_id'));
        $type = $this->getType($ext);

        if($type == 'unknown'){
            Log::info("Aborting file upload unknown file type "+ $type);
            return FALSE;
        }

        $fullPath = $name . '.' . $ext;

        $file_path = 'public/static/' . $type . '/service-providers/'.$fullPath;

        if (Storage::exists($file_path)) {
            Storage::delete($file_path);
        }

        if (Storage::putFileAs('public/static/' . $type . '/service-providers', $file, $fullPath)) {
            return [
                    'media_url'=>$fullPath,
                    'name' => $name,
                    'type' => $type,
                    'extension' => $ext,
                    'size'=>$size
                ];
        }

        return false;



}



 private function getType($ext)
    {
        if (in_array($ext, $this->image_ext)) {
            return 'image';
        }

        if (in_array($ext, $this->audio_ext)) {
            return 'audio';
        }

        if (in_array($ext, $this->video_ext)) {
            return 'video';
        }

        return 'unknown';
    }


    public function create(Request $request)
    {

        $request->replace($request->all());    

    	$validator = Validator::make($request->all(),[
		    'user_id' => 'required|exists:users,id|unique:service_providers,user_id',
            'business_name' => 'required|unique:service_providers,service_provider_name',
            'business_description' => 'required|string',
            'keywords' => 'string|nullable',
            'location_name' =>'string',
            'location_city' =>'string',
            'business_phone' => [
                'required',
                'regex:/^((\+?254)|0)?7\d{8}$/'
            ],
            'business_email' =>'nullable|email',
            'facebook_page'=>'string|nullable',
            'twitter' =>'string|nullable',
            'instagram' => 'string|nullable',
            'work_lat'=>[
                 'required',
                 'regex:/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)$/'
             ],  
            'work_lng'=>[
                 'required', 
                 'regex:/^[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/'
            ] ,
            'services'=>['required'] ,  
            'cover_photo' => 'required|file|image|mimes:jpeg,png,gif,webp|max:2048'      
		]);
   
         if ($validator->fails()) {
		$out = [
		        'success' => false,
		        'message' => $validator->messages()
		    ];
			return Response::json($out, HTTPCodes::HTTP_OK);
	    }else{


            $cover_photo= $this->upload_coverphoto($request);
            
            if($cover_photo !=  FALSE)
            {

                  $cover_photo= json_encode($cover_photo); 

            }else{
                $cover_photo=NULL;
            }
      

        	DB::insert("insert into service_providers (type, user_id, service_provider_name,"
                . " business_description, work_location, work_location_city, business_phone, "
                . " business_email, facebook, twitter, instagram, work_lat, work_lng, status_id,cover_photo, "
                . " created_at, updated_at)  values (1, :user_id, "
                . " :service_provider_name, :business_description, :work_location, :work_location_city, "
                . " :business_phone, :business_email, :facebook, :twitter, :instagram, "
                . " :work_lat, :work_lng,". DBStatus::RECORD_PENDING .",:cover_photo, now(), "
                . " now())  ", 
                    [
                        'user_id'=>$request->get('user_id'),
                        'service_provider_name'=> $request->get('business_name'),
                        'business_description'=>$request->get('business_description'),
                        'work_location'=>$request->get('work_location'),
                        'work_location_city'=>$request->get('work_location_city'),
                        'business_phone'=>$request->get('business_phone'),
                        'business_email'=>$request->get('business_email'),
                        'facebook'=>$request->get('facebook'),
                        'twitter'=>$request->get('twitter'),
                        'instagram'=>$request->get('instagram'),
                        'work_lat'=>$request->get('work_lat'),
                        'work_lng'=>$request->get('work_lng'),
                        'cover_photo' => $cover_photo
                    ]
        	    );
                $service_provider_id = DB::getPdo()->lastInsertId();

                $services_query = "insert into provider_services (id, "
                    . " service_provider_id, service_id, description, cost, duration, "
                    . " rating, media_url, created_at, updated_at, status_id)  values "; 
                $values = [];
        
                foreach(json_decode($request->get('services'), 1)  as $key=>$service_id){
                   if(is_numeric($service_id)){
                       array_push($values, " (null, '$service_provider_id', "
                           . " '$service_id', '', 0, 60, 1, null, now(), now(), 1) ");
                   }
		}

                $services_query .= implode(",", $values);
                Log::info("Services QUERY: " . $services_query);
                DB::insert($services_query);

	    	$out = [
		        'success' => true,
		        'id'=>$service_provider_id,
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


    public  function  search_by_location_service(Request  $request)
    {

        $image_url = URL::to('/storage/static/image/avatar/');
        $sp_providers_url =  URL::to('/storage/static/image/service-providers/');
        $icon_url = URL::to('/storage/static/image/icons/');
        $profile_url =  URL::to('/storage/static/image/profiles/');

        // $image_url = URL::to('/storage/image/avatar/');
        // $sp_providers_url =  URL::to('/storage/image/service-providers/');
        // $p_services_url =  URL::to('/storage/image/provider-services/');

        $validator = Validator::make($request->all(),[
            'service' => 'required',
            'service_time' =>'nullable|date_format:Y-m-d H:i:s',
            'location' =>'nullable|max:50'
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

         if(empty($request->service_time))
         {
            $request->service_time= date('Y-m-d H:i:s');
         } 
      
         if(empty($request->location))
         {
            $request->location= 'Nairobi';
         }

         $service_providers =  RawQuery::paginate(
            "select sp.id, sp.type, sp.service_provider_name,sp.work_location, "
            . " sp.work_lat, sp.work_lng, sp.status_id, sp.overall_rating, "
            . " sp.overall_likes, sp.overall_dislikes, sp.created_at, sp.updated_at, "
            . " d.id_number, d.date_of_birth, d.gender, "
            . " concat( '$image_url' ,'/', if(d.passport_photo is null, 'avatar-bg-1.png', "
            . " json_extract(d.passport_photo, '$.media_url')) ) as thumbnail, "
            . " concat( '$sp_providers_url' , '/', if(sp.cover_photo is null, 'img-03.jpg', "
            . " JSON_UNQUOTE(json_extract(sp.cover_photo, '$.media_url')))) as cover_photo, "
            . " d.home_location, work_phone_no, sp.business_description  from service_providers sp  inner  join "
            . " user_personal_details  d using(user_id)  inner join operating_hours op on sp.id = op.service_provider_id inner join provider_services ps on ps.service_provider_id = sp.id inner join services s on s.id = ps.service_id where sp.status_id=1 and op.service_day = date_format(:service_date, '%W') and time(:service_date2) between time_from and time_to and s.service_name like  :service and (work_location like :location or work_location_city like :location2)",
             $page = null, $limit = null, $params=[
            'service_date'=>$request->service_time,
            'service_date2'=>$request->service_time,
            'service'=>'%'.$request->service.'%',
            'location'=>'%'.$request->location.'%',
            'location2'=>'%'.$request->location.'%'
        ]);
        
       return Response::json($service_providers, HTTPCodes::HTTP_OK);

        
   }

}


