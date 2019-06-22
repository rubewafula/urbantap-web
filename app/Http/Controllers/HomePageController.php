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

class HomePageController extends Controller
{

    /**
     * Display the provider service details 
     * Default to highly rated services.
     * curl -i -XGET -H "content-type:application/json" 
     * http://127.0.0.1:8000/api/home-page/get
     *
     * @param  \App\Category $category
     *
     * @return JSON 
     */
 

    public function get($id=null){

        $image_url = Utils::IMAGE_URL;
        $sp_providers_url =  Utils::SERVICE_PROVIDERS_URL;
        $icon_url = Utils::ICONS_URL;
        $profile_url =  Utils::PROFILE_URL;

        $top_booking_id_q = "select id as customer_count from bookings order by 1 desc limit 1";
        $top_service_provider_id_q = "select id  as service_provider_count from service_providers order by 1 desc limit 1";
        $top_review_id_q = "select id as rating_count from reviews order by 1 desc limit 1";
        $weekly_providers_q = "select count(*) as weekly_providers_count from service_providers where created_at > now() - interval 1 week ";

        
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
            . " order by sp.created_at desc, overall_likes desc limit 10 " ;

        //echo print_r($params, 1);
        $provider_data =  RawQuery::query( $provideQ);

        $popular_providers = [];
        foreach ($provider_data as $key => $provider) {

            $serviceQ = "select ps.id as provider_service_id, s.id as service_id, "
                . " s.service_name, ps.cost, ps.description, ps.duration, ps.created_at,"
                . " ps.updated_at from provider_services ps inner join services s "
                . " on s.id = ps.service_id  where  ps.service_provider_id = :pid " ;

            $service_params['pid'] = $provider->service_provider_id;

            $service_results = RawQuery::query( $serviceQ, $params=$service_params);
            $provider->services = $service_results;

            array_push($popular_providers, $provider);
        }


        $featured_providers = "select sp.id as service_provider_id, sp.id as id, "
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
            . " order by overall_rating desc, overall_likes desc limit 2";

        $fp_data =  RawQuery::query( $featured_providers);

        $featured_providers = [];
        foreach ($fp_data as $key => $provider) {

            $serviceQ = "select ps.id as provider_service_id, s.id as service_id, "
                . " s.service_name, ps.cost, ps.description, ps.duration, ps.created_at,"
                . " ps.updated_at from provider_services ps inner join services s "
                . " on s.id = ps.service_id  where  ps.service_provider_id = :pid " ;

            $service_params['pid'] = $provider->service_provider_id;

            $service_results = RawQuery::query( $serviceQ, $params=$service_params);
            $provider->services = $service_results;

            array_push($featured_providers, $provider);
        }


        $results = RawQuery::queryMultiple(
                [
                $top_booking_id_q,
                $top_service_provider_id_q,
                $top_review_id_q, 
                $weekly_providers_q,
             ]);
       
        $out = [ 
                'customer_count'=> optional(array_get($results, '0.0'))->customer_count,
                'service_provider_count' => optional(array_get($results, '1.0'))->service_provider_count,
                'rating_count' => optional(array_get($results, '2.0'))->rating_count,
                'weekly_providers_count' => optional(array_get($results, '3.0'))->weekly_providers_count,
                'popular_providers' => $popular_providers,
                'featured_providers' => $featured_providers
            ];
        
        return Response::json($out, HTTPCodes::HTTP_OK);

    }


    

   

    

}
