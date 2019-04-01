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

       
        $service_query = "select service_name , service_meta from services ";
        $top_service_query = "select service_name , service_meta, service_icon from "
            . " top_services ts inner join services s on ts.service_id = s.id "
            . " order by ts.priority desc ";

        $top_booking_id_q = "select id as customer_count from bookings order by 1 desc limit 1";
        $top_service_provider_id_q = "select id  as service_provider_count from service_providers order by 1 desc limit 1";
        $top_review_id_q = "select id as rating_count from reviews order by 1 desc limit 1";
        $weekly_providers_q = "select count(*) as weekly_providers_count from service_providers where created_at > now() - interval 1 week ";

        $popular_providers = "SELECT sp.id, s.service_name, sp.type, "
            . " (select count(*) from reviews where service_provider_id = sp.id "
            . " and provider_service_id=ps.id) as reviews, "
            . " sp.service_provider_name,  sp.business_description, sp.work_location, "
	    . " sp.overall_rating, sp.overall_likes, sp.overall_dislikes, sp.created_at, "
	    . " sp.updated_at,  d.id_number, d.date_of_birth, d.gender, d.passport_photo, "
	    . " d.home_location, work_phone_no  FROM provider_services ps inner join "
	    . " service_providers sp on sp.id = ps.service_provider_id inner  join "
	    . " user_personal_details  d using(user_id) inner join services s on "
	    . " s.id = ps.service_id where sp.status_id =1  order by overall_rating desc, "
	    . " overall_likes desc limit 20";

      

        $results = RawQuery::queryMultiple(
                [
                $service_query, 
                $top_service_query,
                $top_booking_id_q,
                $top_service_provider_id_q,
                $top_review_id_q, 
                $weekly_providers_q,
                $popular_providers
             ]);
       
        $out = ['services' =>$results[0], 
                'top_services'=>$results[1],
                'customer_count'=>$results[2][0]->customer_count,
                'service_provider_count' => $results[3][0]->service_provider_count,
                'rating_count' => $results[4][0]->rating_count, 
                'weekly_providers_count' => $results[5][0]->weekly_providers_count,
                'popular_providers' => $results[6] ];
        
        return Response::json($out, HTTPCodes::HTTP_OK);

    }


    

   

    

}
