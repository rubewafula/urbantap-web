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
use App\Utilities\Utils;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use IlluminateSupportFacadesLog;
use DateTime;
use DateInterval;


class ServiceProvidersController extends Controller
{


    /**
     * provider time slots
     * Default slots for today
     */
    public function timeslots(Request $request)
    {

        date_default_timezone_set('Africa/Nairobi');
        $req = $request->all();
        $date = new DateTime();
        $date->sub(new DateInterval('P1D'));

        $validator = Validator::make($req,
            ['service_provider_id' => 'integer|exists:service_providers,id',
             'slot_date'           => 'required|date_format:Y-m-d|after:' . $date->format('Y-m-d'),]
        );
        if ($validator->fails()) {
            $out = [
                'sucess'  => false,
                'message' => $validator->messages()

            ];

            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

        $slots = [];
        $slot_data = $request->get('slot_date');

        $slot_date = DateTime::createFromFormat('Y-m-d H:i', $slot_data . " 00:00");

        $date_now = new DateTime();

        $date_5min_roundup = sprintf("%d minutes %d seconds",
            $date_now->format("i") % 5,
            $date_now->format("s")
        );
        $provider_booking_sql = "select booking_time, booking_duration "
            . " from bookings where service_provider_id=:pid and "
            . " date(booking_time)=:booking_date and status_id=:st ";
        $params = ['pid'          => $request->get('service_provider_id'),
                   'booking_date' => $slot_data, 'st' => DBStatus::BOOKING_PAID];

        $booked_records = RawQuery::query($provider_booking_sql, $params);
        $booked_slots = [];
        foreach ($booked_records as $key => $record) {
            $start = $record->booking_time;
            #Log::info("Trying tp generate booking slot from " . $start . "duration ". $record->booking_duration );

            $bb_date = DateTime::createFromFormat('Y-m-d H:i:s', $start);

            $ls_date = DateTime::createFromFormat('Y-m-d H:i:s', $start)
                ->add(new DateInterval('PT' . $record->booking_duration . 'M'));

            do {

                array_push($booked_slots, $bb_date->format("H:i"));
                $bb_date->add(new DateInterval('PT15M'));

            } while ($bb_date < $ls_date);

        }

        $working_hours_sql = "select id,service_day, time_from, time_to from operating_hours "
            . " where service_provider_id = :service_provider_id and service_day= :_day"
            . " and status_id=:active";

        $_day = $slot_date->format('l');

        $working_day = RawQuery::query($working_hours_sql,
            ['service_provider_id' => $request->get('service_provider_id'),
             '_day'                => $_day, 'active' => DBStatus::TRANSACTION_ACTIVE]);

        if (empty($working_day)) {
            $out = ['success' => false, 'message' => ['slot_date' => 'Service provider not available on this date']];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

        $work_start_time = $working_day[0]->time_from;
        $work_end_time = $working_day[0]->time_to;

        #die(" woking day time $work_start_time ==> $work_end_time");
        $round_date = $date_now->add(
            \DateInterval::createFromDateString($date_5min_roundup));

        $original_work_time = DateTime::createFromFormat('Y-m-d H:i:s',
            $slot_date->format('Y-m-d') . " " . $work_start_time);
        Log::info("Original work time " . $original_work_time->format('Y-m-d H:i'));
        if ($round_date < $original_work_time) {
            $round_date = $original_work_time;
            //->sub( \DateInterval::createFromDateString($date_5min_roundup));
        }
        Log::info("Round date " . $round_date->format('Y-m-d H:i'));
        $work_end_datetime = \DateTime::createFromFormat('Y-m-d H:i:s',
            $slot_date->format('Y-m-d') . " " . $work_end_time);

        while (true) {
            #Log::info("Checking " . $slot_data  . "==> " . $slot_date->format('Y-m-d H:i') 
            #    . " work end ==> ". $work_end_datetime->format('Y-m-d H:i'));
            if ($slot_date < $round_date) {
                #FIXME: Date overlap error
                $slot_date = $round_date->sub(new DateInterval('PT30M'));
                //shooting the pegion no closer that 30minutes away
                $slot_date->add(new DateInterval('PT15M'));
            }

            $slot_date->add(new DateInterval('PT15M'));
            #Log::info("New slot dat " . $slot_date->format('Y-m-d H:i') ); 
            if ($slot_data == $slot_date->format('Y-m-d')) {

                if (!in_array($slot_date->format('H:i'), $booked_slots)) {
                    array_push($slots, $slot_date->format('H:i'));
                }

            }
            //Break on work_end_time
            if ($slot_date >= $work_end_datetime) {
                break;
            }

        }

        return Response::json($slots, HTTPCodes::HTTP_OK);


    }

    /**
     * Display the specified service providers.
     * curl -i -XGET -H "content-type:application/json"
     * http://127.0.0.1:8000/api/service-providers/details/[user_id]
     *
     * @param \App\Category $category
     *
     * @return JSON
     */

    public function details($user_id = null, Request $request)
    {
        $req = $request->all();
        $page = 1;
        $limit = null;

        $image_url = Utils::IMAGE_URL;
        $sp_providers_url = Utils::SERVICE_PROVIDERS_URL;
        $icon_url = Utils::ICONS_URL;
        $profile_url = Utils::PROFILE_URL;
        $p_services_url = Utils::PROVIDER_PORTFOLIOS_URL;
        $service_image_url = Utils::SERVICE_IMAGE_URL;


        $validator = Validator::make(['id' => $user_id],
            ['user_id' => 'integer|exists:service_providers']
        );
        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()

            ];

            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }


        $filter = '';
        if (!is_null($user_id)) {
            $filter = " and sp.id = '" . $user_id . "' ";
        }

        $rawQuery = "SELECT sp.id,  "
            . " (select count(*) from reviews where service_provider_id=sp.id) as reviews, "
            . " sp.service_provider_name as business_name,  sp.business_description,"
            . "  sp.work_location as location_name, sp.work_lat, sp.work_lng, "
            . " sp.overall_rating, sp.overall_likes, sp.overall_dislikes, sp.created_at, "
            . " sp.updated_at,  d.id_number, d.date_of_birth, d.gender, d.passport_photo, "
            . " d.home_location, business_phone, business_email, key_words as keywords, "
            . " address_data as address_data, facebook as facebook_page, twitter, "
            . " instagram, total_requests, date_format(sp.created_at, '%b, %Y') as since, "
            . " concat('$profile_url' , '/', (if(d.passport_photo is null, 'avatar-bg-1.png', "
            . " JSON_UNQUOTE(json_extract(d.passport_photo, '$.media_url') ))) ) as thumbnail, "
            . " concat( '$sp_providers_url' , '/', if(sp.cover_photo is null, 'img-03.jpg', "
            . " JSON_UNQUOTE(json_extract(sp.cover_photo, '$.media_url')))) as cover_photo "
            . " FROM  service_providers sp left join "
            . " user_personal_details  d using(user_id) where 1=1 " . $filter;

        //die($rawQuery);

        $results = [];
        $provider_data = RawQuery::query($rawQuery);

        if (empty($provider_data)) {
            return Response::json(['success' => false, 'message' => 'Could not fetch provider data'], HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

        $results['provider'] = $provider_data[0];

        $service_provider_id = $user_id;

        $sql_provider_services = "select ps.id as provider_service_id,  c.category_name, c.id as category_id, "
            . " concat('$service_image_url' ,'/', if(ps.media_url is null, '2.jpg', "
            . " JSON_UNQUOTE(json_extract(ps.media_url, '$.media_url'))) ) as service_photo, "
            . " ps.service_provider_id, ps.service_id, s.service_name, ps.rating, "
            . " ps.description, ps.cost , ps.duration, ps.rating, ps.created_at, "
            . "  ps.updated_at from provider_services ps inner join services s on "
            . " s.id = ps.service_id inner join categories c on s.category_id = c.id "
            . " where ps.service_provider_id =:spid and ps.status_id=:active";


        $services = RawQuery::query($sql_provider_services,
            ['spid' => $service_provider_id, 'active' => DBStatus::TRANSACTION_ACTIVE]);
        $results['services'] = $services;

        $working_hours_sql = "select id,service_day, time_from, time_to from operating_hours "
            . " where service_provider_id ='" . $service_provider_id . "'"
            . " and status_id=" . DBStatus::TRANSACTION_ACTIVE;

        $results['operating_hours'] = RawQuery::query($working_hours_sql);

        $portfolios_sql = "SELECT id, "
            . " concat('$p_services_url' ,'/', if(media_data is null, '2.jpg', "
            . " JSON_UNQUOTE(json_extract(media_data, '$.media_url'))) ) as media_photo, "
            . " p.description  FROM  portfolios p "
            . " where service_provider_id = '" . $service_provider_id . "' "
            . " and status_id = " . DBStatus::TRANSACTION_ACTIVE;

        $pflios = RawQuery::query($portfolios_sql);

        if (empty($pflios)) {
            $pflios = [
                array(
                    'media_photo' => $p_services_url . '/2.jpg',
                    'description' => 'Service Provider')
            ];
        }

        $results['portfolios'] = $pflios;

        
        $reviews_sql = "SELECT r.created_at, "
            . " r.provider_service_id, r.rating, r.review, "
            . " r.status_id, concat(if(u.first_name is null, '', u.first_name), ' ', "
            . " if(u.last_name is null, '', u.last_name)) as reviewer, "
            . " u.email, s.service_name, "
            . " concat( '$profile_url' ,'/', if(d.passport_photo is null, 'avatar-bg-1.png', "
            . " JSON_UNQUOTE(json_extract(d.passport_photo, '$.media_url'))) ) as thumbnail "
            . " FROM  reviews r  inner join users u on u.id=r.user_id "
            . " inner join user_personal_details d on u.id = d.user_id "
            . " inner join provider_services ps on ps.id = r.provider_service_id "
            . " inner join services s on s.id = ps.service_id where "
            . " r.service_provider_id = '" . $service_provider_id . "' "
            . " order by r.id desc";

        //die($reviews_sql);

        $results['reviews'] = RawQuery::paginate($reviews_sql, $page = 1, $limit = 3);

        //dd(HTTPCodes);
        Log::info('Extracted service service_providers results : ' . var_export($results, 1));
        if (empty($results)) {
            return Response::json($results, HTTPCodes::HTTP_NO_CONTENT);
        }
        return Response::json($results, HTTPCodes::HTTP_OK);

    }


    public function getwithserviceid($service_id = null, Request $request)
    {
        $req = $request->all();
        $page = 1;
        $limit = null;
        $sort = null;

        $image_url = Utils::IMAGE_URL;
        $sp_providers_url = Utils::SERVICE_PROVIDERS_URL;
        $icon_url = Utils::ICONS_URL;
        $profile_url = Utils::PROFILE_URL;
        $p_services_url = Utils::PROVIDER_PORTFOLIOS_URL;


        $sort_by = " order by sp.overall_likes desc, sp.overall_rating desc ";
        //die(print_r($req, 1));
        if (array_key_exists('page', $req)) {
            $page = is_numeric($request['page']) ? $request['page'] : 1;
        }
        if (array_key_exists('limit', $req)) {
            $limit = is_numeric($request['limit']) ? $request['limit'] : null;
        }

        if (array_key_exists('sort', $req)) {
            $sort = $request['sort'];
        }

        $validator = Validator::make(['id' => $service_id, 'sort' => $sort],
            ['id'   => 'integer|exists:services,id|nullable',
             'sort' => 'in:since,overall_likes, overall_ratings,total_requests|nullable']
        );
        if ($validator->fails()) {
            $out = [
                'sucess'  => false,
                'message' => $validator->messages()

            ];

            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

        $filter = '';
        if (!is_null($service_id)) {
            $filter = " and ps.service_id = '" . $service_id . "' ";
        }

        if (!is_null($sort)) {
            $sort_by = " order by $sort desc ";
        }

        $rawQuery = "SELECT sp.id,  "
            . " (select count(*) from reviews where service_provider_id=sp.id) as reviews, "
            . " sp.service_provider_name as business_name,  sp.business_description,"
            . "  sp.work_location as location_name, sp.work_lat, sp.work_lng, "
            . " sp.overall_rating, sp.overall_likes, sp.overall_dislikes, sp.created_at, "
            . " sp.updated_at,  d.id_number, d.date_of_birth, d.gender, d.passport_photo, "
            . " d.home_location, business_phone, business_email, key_words as keywords, "
            . " address_data as address_data, facebook as facebook_page, twitter, "
            . " instagram, total_requests, date_format(sp.created_at, '%b, %Y') as since, "
            . " concat('$profile_url' , '/', (if(d.passport_photo is null, 'avatar-bg-1.png', "
            . " JSON_UNQUOTE(json_extract(d.passport_photo, '$.media_url') ))) ) as thumbnail, "
            . " concat( '$sp_providers_url' , '/', if(sp.cover_photo is null, 'img-03.jpg', "
            . " JSON_UNQUOTE(json_extract(sp.cover_photo, '$.media_url')))) as cover_photo "
            . " FROM  service_providers sp left join "
            . " user_personal_details  d using(user_id) where 1=1 " . $filter . " " . $sort_by;
        //die($rawQuery);

        $results = RawQuery::paginate($rawQuery, $page = $page, $limit = $limit);

        Log::info('Extracted service service_providers results : ' . var_export($results, 1));
        if (empty($results)) {
            return Response::json($results, HTTPCodes::HTTP_NO_CONTENT);
        }
        return Response::json($results, HTTPCodes::HTTP_OK);


    }

    /**
     * Display the specified service providers.
     * curl -i -XGET -H "content-type:application/json"
     * http://127.0.0.1:8000/api/service-providers/all
     *
     * @param \App\Category $category
     *
     * @return JSON
     */

    public function get($service_provider_id=null, Request $request)
    {
        $req = $request->all();

        $image_url = Utils::IMAGE_URL;
        $sp_providers_url = Utils::SERVICE_PROVIDERS_URL;
        $icon_url = Utils::ICONS_URL;
        $profile_url = Utils::PROFILE_URL;
        $p_services_url = Utils::PROVIDER_PORTFOLIOS_URL;


        $sort_by = " order by sp.overall_likes desc, sp.overall_rating desc ";

        $validator = Validator::make($request->all(),
            [
             'service_provider_id' => 'integer|exists:service_providers,id|nullable',
             'page' => 'nullable|integer',
             'limit' => 'nullable|integer',
            ]
        );
        if ($validator->fails()) {
            $out = [
                'sucess'  => false,
                'message' => $validator->messages()

            ];

            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

        $page = $request->page;
        $limit = $request->limit;

        $filter = '';
        if (!is_null($service_provider_id)) {
            $filter = " and sp.id = :spid ";
        }


        $rawQuery = "SELECT sp.id,  "
            . " (select count(*) from reviews where service_provider_id=sp.id) as reviews, "
            . " sp.service_provider_name as business_name,  sp.business_description,"
            . "  sp.work_location as location_name, sp.work_lat, sp.work_lng, "
            . " sp.overall_rating, sp.overall_likes, sp.overall_dislikes, sp.created_at, "
            . " sp.updated_at,  d.id_number, d.date_of_birth, d.gender, d.passport_photo, "
            . " d.home_location, business_phone, business_email, key_words as keywords, "
            . " address_data as address_data, facebook as facebook_page, twitter, "
            . " instagram, total_requests, date_format(sp.created_at, '%b, %Y') as since, "
            . " concat('$profile_url' , '/', (if(d.passport_photo is null, 'avatar-bg-1.png', "
            . " JSON_UNQUOTE(json_extract(d.passport_photo, '$.media_url') ))) ) as thumbnail, "
            . " concat( '$sp_providers_url' , '/', if(sp.cover_photo is null, 'img-03.jpg', "
            . " JSON_UNQUOTE(json_extract(sp.cover_photo, '$.media_url')))) as cover_photo "
            . " FROM  service_providers sp left join "
            . " user_personal_details  d using(user_id) where 1=1 " . $filter . " " . $sort_by;

        //die($rawQuery);
        if(is_null($service_provider_id)){
            $results = RawQuery::paginate($rawQuery, $page = $page, $limit = $limit);
        }else{
            $results = array_get(RawQuery::query($rawQuery, ['spid' => $service_provider_id]), 0, new \stdClass);
        }
        //dd(HTTPCodes);
        Log::info('Extracted service service_providers results : ' . var_export($results, 1));

        return Response::json($results, HTTPCodes::HTTP_OK);

    }


    /**
     * Display the popular service providers.
     * curl -i -XGET -H "content-type:application/json"
     * http://127.0.0.1:8000/api/service-providers/popular
     *
     * @param \App\Category $category
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
        Log::info('Extracted popular service service_providers results : ' . var_export($results, 1));
        if (empty($results)) {
            return Response::json($results, HTTPCodes::HTTP_NO_CONTENT);
        }
        return Response::json($results, HTTPCodes::HTTP_OK);

    }


    /**
     * curl -i -XPOST -H "content-type:application/json"
     * --data '{"category_id":1, "provider_name":"Golden PAP",
     *  "description":"Best salon jab for the old"}'
     * 'http://127.0.0.1:8000/api/service-providers/create'
     * @param Illuminate\Http\Request $request
     * @return JSON
     *
     ***/


    public function upload_coverphoto($request)
    {
        return Utils::upload_media($request, 'service-providers', 'cover_photo');
    }


    public function create(Request $request)
    {

        $request->replace($request->all());
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'business_name'        => 'required|unique:service_providers,service_provider_name',
            'business_description' => 'required|string|max:400',
            'keywords'             => 'string|nullable',
            'location_name'        => 'string|nullable',
            'location_city'        => 'string|nullable',
            'business_phone'       => [
                'required',
                'regex:/^((\+?254)|0)?7\d{8}$/'
            ],
            'business_email'       => 'nullable|email',
            'facebook_page'        => 'string|nullable',
            'twitter'              => 'string|nullable',
            'instagram'            => 'string|nullable',
            'work_lat'             => [
                'required',
                'regex:/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)$/'
            ],
            'work_lng'             => [
                'required',
                'regex:/^[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/'
            ],
            'services'             => ['required'],
            'cover_photo'          => 'required|file|image|mimes:jpeg,png,gif,webp|max:2048',
            'address_data'         => 'nullable|string'
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        } else {


            $cover_photo = $this->upload_coverphoto($request);

            if ($cover_photo != FALSE) {

                $cover_photo = json_encode($cover_photo);

            } else {
                $cover_photo = NULL;
            }


            DB::insert("insert into service_providers (type, user_id, service_provider_name,"
                . " business_description, work_location, work_location_city, business_phone, "
                . " business_email, facebook, twitter, instagram, work_lat, work_lng, status_id,cover_photo, "
                . " created_at, updated_at, key_words, address_data)  values (1, :user_id, "
                . " :service_provider_name, :business_description, :work_location, :work_location_city, "
                . " :business_phone, :business_email, :facebook, :twitter, :instagram, "
                . " :work_lat, :work_lng," . DBStatus::USER_ACTIVE . ",:cover_photo, now(), "
                . " now(), :keywords, :address_data)  ",
                [
                    'user_id'               => $user->id,
                    'service_provider_name' => $request->get('business_name'),
                    'business_description'  => $request->get('business_description'),
                    'work_location'         => $request->get('location_name'),
                    'work_location_city'    => $request->get('location_city'),
                    'business_phone'        => $request->get('business_phone'),
                    'business_email'        => $request->get('business_email'),
                    'facebook'              => $request->get('facebook'),
                    'twitter'               => $request->get('twitter'),
                    'instagram'             => $request->get('instagram'),
                    'work_lat'              => $request->get('work_lat'),
                    'work_lng'              => $request->get('work_lng'),
                    'cover_photo'           => $cover_photo,
                    'keywords'              => $request->get('keywords'),
                    'address_data'          => $request->get('address_data'),
                ]
            );
            $service_provider_id = DB::getPdo()->lastInsertId();

            $services_query = "insert into provider_services (id, "
                . " service_provider_id, service_id, description, cost, duration, "
                . " rating, media_url, created_at, updated_at, status_id)  values ";
            $values = [];

            foreach (json_decode($request->get('services'), 1) as $key => $service_id) {
                if (is_numeric($service_id)) {
                    array_push($values, " (null, '$service_provider_id', "
                        . " '$service_id', '', 0, 60, 1, null, now(), now(), 1) ");
                }
            }

            $services_query .= implode(",", $values);
            Log::info("Services QUERY: " . $services_query);
            DB::insert($services_query);

            $out = [
                'success' => true,
                'id'      => $service_provider_id,
                'message' => 'Service provider Created'
            ];

            return Response::json($out, HTTPCodes::HTTP_CREATED);
        }
    }


    public function like(){
        
        $user = $request->user();
        $user_id = $user->id;
        $validator = Validator::make($request->all(), [
            'service_provider_id' => 'required|exists:service_providers,id']);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }
        $sp = ServiceProviders::where(['id' => $request->service_provider_id])->first();
        $sp->overrall_like = $sp->overrall_like + 1;
        $sp->save();
        $out = [
                'success' => true,
                'user_id' => $user->id,
                'message' => 'Service Provider liked'
            ];
        return Response::json($out, HTTPCodes::HTTP_OK);

    }

    /**
     *  curl -i -XPUT -H "content-type:application/json"
     * --data '{"id":1, "provider_name":"Golden Ladies Salon",
     * "description":"Best salon jab for the old", "new_name":"Golden Ladies Salon 23"}'
     * 'http://127.0.0.1:8000/api/service-providers/update'
     * @param Illuminate\Http\Request $request
     * @return JSON
     *
     ***/
    public function update(Request $request)
    {

        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'business_name'        => 'unique:service_providers,service_provider_name',
            'business_description' => 'string',
            'location_name'        => 'nullable|string',
            'location_city'        => 'nullable|string',
            'business_email'       => 'nullable|email',
            'facebook_page'        => 'string|nullable',
            'twitter'              => 'string|nullable',
            'instagram'            => 'string|nullable',
            'keywords'             => 'string|nullable',
            'business_phone'       => [
                'nullable',
                'regex:/^((\+?254)|0)?7\d{8}$/'
            ],
            'work_lat'             => [
                'nullable',
                'regex:/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)$/'
            ],
            'work_lng'             => [
                'nullable',
                'regex:/^[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/'
            ],
            'status_id'            => 'integer',
            'address_data'         => 'nullable|string',
        ]);
        Log::info("Update request => " . var_export($request->all(), 1));
        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        } else {

            $update = [];
            if (!empty($request->get('business_name'))) {
                $update['service_provider_name'] = $request->get('business_name');
            }
            if (!empty($request->get('business_description'))) {
                $update['business_description'] = $request->get('business_description');
            }
            if (!empty($request->get('location_name'))) {
                $update['work_location'] = $request->get('location_name');
            }
            if (!empty($request->get('location_city'))) {
                $update['work_location_city'] = $request->get('location_city');
            }

            if (!empty($request->get('work_lat'))) {
                $update['work_lat'] = $request->get('work_lat');
            }
            if (!empty($request->get('work_lng'))) {
                $update['work_lng'] = $request->get('work_lng');
            }
            if (!empty($request->get('status_id'))) {
                $update['status_id'] = $request->get('status_id');
            }
            if (!empty($request->get('facebook_page'))) {
                $update['facebook'] = $request->get('facebook_page');
            }
            if (!empty($request->get('twitter'))) {
                $update['twitter'] = $request->get('twitter');
            }
            if (!empty($request->get('instagram'))) {
                $update['instagram'] = $request->get('instagram');
            }

            if (!empty($request->get('business_phone'))) {
                $update['business_phone'] = $request->get('business_phone');
            }
            if (!empty($request->get('keywords'))) {
                $update['key_words'] = $request->get('keywords');
            }
            if (!empty($request->get('address_data'))) {
                $update['address_data'] = $request->get('address_data');
            }
            if (!empty($request->get('business_email'))) {
                $update['business_email'] = $request->get('business_email');
            }

            $coverPhoto = $this->upload_coverphoto($request);
            if ($coverPhoto) {
                $update['cover_photo'] = json_encode($coverPhoto);
            }


            DB::table('service_providers')
                ->where('user_id', $user->id)
                ->update($update);

            $out = [
                'success' => true,
                'user_id' => $user->id,
                'message' => 'Service Provider updated OK'
            ];

            return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
        }
    }

    /**
     * curl -i -XDELETE -H "content-type:application/json" --data
     * '{"id":4}'
     * 'http://127.0.0.1:8000/api/service-providers/delete'
     * @param Illuminate\Http\Request $request
     * @return JSON
     *
     ***/
    public function delete(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:service_providers'
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        } else {
            DB::table('service_providers')
                ->where('user_id', $request->get('id'))
                ->update(['status_id' => DBStatus::TRANSACTION_DELETED]);

            $out = [
                'success' => true,
                'user_id' => $request->get('user_id'),
                'message' => 'Service provider marked deleted OK'
            ];

            return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
        }
    }


    public function search_by_location_service(Request $request)
    {

        $image_url = Utils::IMAGE_URL;
        $sp_providers_url = Utils::SERVICE_PROVIDERS_URL;
        $icon_url = Utils::ICONS_URL;
        $profile_url = Utils::PROFILE_URL;
        $p_services_url = Utils::PROVIDER_PORTFOLIOS_URL;


        $validator = Validator::make($request->all(), [
            'service'      => 'required',
            'service_time' => 'nullable|date_format:Y-m-d H:i:s',
            'location'     => 'nullable|max:50'
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

        if (empty($request->service_time)) {
            $request->service_time = date('Y-m-d H:i:s');
        }

        if (empty($request->location)) {
            $request->location = 'Nairobi';
        }

        $service_providers = RawQuery::paginate(
            "select sp.id, sp.type, sp.service_provider_name,sp.work_location, "
            . " sp.work_lat, sp.work_lng, sp.status_id, sp.overall_rating, "
            . " sp.overall_likes, sp.overall_dislikes, sp.created_at, sp.updated_at, "
            . " d.id_number, d.date_of_birth, d.gender, "
            . " concat( '$image_url' ,'/', if(d.passport_photo is null, 'avatar-bg-1.png', "
            . " json_extract(d.passport_photo, '$.media_url')) ) as thumbnail, "
            . " concat( '$sp_providers_url' , '/', if(sp.cover_photo is null, 'img-03.jpg', "
            . " JSON_UNQUOTE(json_extract(sp.cover_photo, '$.media_url')))) as cover_photo, "
            . " d.home_location, work_phone_no, sp.business_description  from service_providers sp  inner  join "
            . " user_personal_details  d using(user_id)  inner join operating_hours "
            . " op on sp.id = op.service_provider_id inner join provider_services ps " 
            . " on ps.service_provider_id = sp.id inner join services s on "
            . " s.id = ps.service_id where sp.status_id=1 and op.service_day = " 
            . " date_format(:service_date, '%W') and time(:service_date2) between "
            . " time_from and time_to and s.service_name like  :service and "
            . " (work_location like :location or work_location_city like :location2)",
            $page = null, $limit = null, $params = [
                'service_date'  => $request->service_time,
                'service_date2' => $request->service_time,
                'service'       => '%' . $request->service . '%',
                'location'      => '%' . $request->location . '%',
                'location2'     => '%' . $request->location . '%'
            ]);

        return Response::json($service_providers, HTTPCodes::HTTP_OK);


    }

    public function transactions(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'service_provider_id' => 'required|exists:service_providers,id',
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

        $transactions = RawQuery::paginate("select created_at, reference, "
            . " description, if(transaction_type='CREDIT', amount,-amount) as amount, "
            . " running_balance  from transactions where service_provider_id =:spid ",
            $page = $page, $limit = $limit, $params = ['spid' => $request->service_provider_id]);

        return Response::json($transactions, HTTPCodes::HTTP_OK);

    }

}


