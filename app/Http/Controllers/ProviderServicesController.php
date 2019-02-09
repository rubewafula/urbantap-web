<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;    
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;    
use App\Utilities\HTTPCodes;
use App\Utilities\DBStatus;
use Illuminate\Support\Facades\Validator;

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
 

    public function getProviderServiceDetails($id=null)
    
        
        $validator = Validator::make(['id'=>$id],[
            'id' => 'integer|exists:provider_services,id|required',
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
            $filter = " where ps.id = '" .$id . "' ";
        }
         

        $query = "select p.service_provider_name, s.id, ps.id as "
            . " provider_service_id, ps.description as provider_service_name,"
            . " s.service_name, c.category_name, ps.rating, ps.cost, ps.duration, "
            . " e.business_description, e.id_number, e.date_of_birth, e.gender, "
            . " e.passport_photo, e.home_location, e.work_phone_ni, e.work_location, "
            . " e.work_lat, e.work_lng, e.overall_rating, e.overall_likes, "
            . " e.overall_dislikes from provider_services ps "
            . " inner join service_providers p on p.id = ps.service_provider_id "
            . " inner join experts e on p.id = e.service_provider_id "
            . " inner join services s on s.id=ps.service_id inner join categories c"
            . "  on c.id =s.category_id  " . $filter . " order by ps.rating desc limit 50";

        Log::info('Query : ' . $query);

        $results = DB::select( 
            DB::raw($query) 
        );
        //dd(HTTPCodes);
        Log::info('Extracted statuses results : ' . var_export($results, 1));

        if(empty($results)){
            return Response::json($results[0], HTTPCodes::HTTP_NO_CONTENT );
        }
        return Response::json($results, HTTPCodes::HTTP_OK);

    }


   /**
     * Display the provider service beonging to specific category 
     * Default to highly rated services.
     * curl -i -XGET -H "content-type:application/json" 
     * http://127.0.0.1:8000/api/provider-services/top
     * http://127.0.0.1:8000/api/provider-services/top/{category_id}
     *
     * @param  \App\Category $category
     *
     * @return JSON 
     */
 

    public function getProviderServices($category_id=null)
    {
        
        $validator = Validator::make(['category_id'=>$id],[
            'category_id' => 'integer|exists:categories,id|nullable',
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
            $filter = " where c.category_id = '" .$id . "' ";
        }

        $query = "select p.service_provider_name, s.id, ps.id as "
            . " provider_service_id, ps.description as provider_service_name,"
            . " s.service_name, c.category_name, ps.rating, ps.cost, "
            . " ps.duration from provider_services ps "
            . " inner join service_providers p on p.id = ps.service_provider_id "
            . " inner join services s on s.id=ps.service_id inner join categories c"
            . "  on c.id =s.category_id  " . $filter . " order by ps.rating desc limit 50";

        Log::info('Query : ' . $query);

        $results = DB::select( 
            DB::raw($query) 
        );
        //dd(HTTPCodes);
        Log::info('Extracted statuses results : ' . var_export($results, 1));

        if(empty($results)){
            return Response::json($results, HTTPCodes::HTTP_NO_CONTENT );
        }
        return Response::json($results, HTTPCodes::HTTP_OK);

    }

    

}
