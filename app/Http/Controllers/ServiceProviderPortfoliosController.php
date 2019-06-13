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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;



class ServiceProviderPortfoliosController extends Controller{

        /**
         * Display the specified service providers.
         * curl -i -XGET -H "content-type:application/json" 
         * http://127.0.0.1:8000/api/service-providers/portfolios/all
         *
         * @param  \App\Category $category
         *
         * @return JSON 
         */

        public function get($service_provider_id=null, Request $request)
        {
                $req= $request->all();
                $page = 1; 
                $limit =null;
                //die(print_r($req, 1));
                if(array_key_exists('page', $req)){
                        $page = is_numeric($request['page']) ? $request['page'] : 1;

                }
                if(array_key_exists('limit', $req)){
                        $limit = is_numeric($request['limit']) ? $request['limit'] : null;
                }

                $validator = Validator::make(['service_provider_id'=>$service_provider_id],
                                ['service_provider_id'=>'integer|exists:service_providers,id|nullable']
                                );
                if($validator ->fails()){
                        $out =[
                                'sucess'=> false, 
                                'message'=> $validator->messages()

                        ];

                        return Response::json($out,HTTPCodes::HTTP_PRECONDITION_FAILED);
                }

                $p_services_url =  Utils::PROVIDER_PORTFOLIOS_URL;

                $filter= '';
                if(!is_null($service_provider_id)){
                        $filter = " and p.service_provider_id = '" .$service_provider_id . "' ";
                }

                $rawQuery = "SELECT id, concat('$p_services_url' ,'/', if(media_data is null, '2.jpg', "
                        . " JSON_UNQUOTE(json_extract(media_data, '$.media_url'))) ) as media_photo "
                        . ", p.description FROM  portfolios p  where 1=1  " . $filter ;

                $results = RawQuery::paginate($rawQuery, $page=$page, $limit=$limit);

                //dd(HTTPCodes);
                Log::info('Extracted user personal details : '.var_export($results, 1));
                if(empty($results)){
                        return Response::json($results, HTTPCodes::HTTP_NO_CONTENT );
                }
                return Response::json($results, HTTPCodes::HTTP_OK);

        }

        /**
         * curl -i -XPOST -H "content-type:application/json" 
         * --data '{"service_provider_id":1, "@file":"FILES",
         *  "description":"Some very loong text "}' 
         * 'http://127.0.0.1:8000/api/service-providers/portfolios/create'
         *  @param  Illuminate\Http\Request $request
         *  @return JSON
         *
         ***/
        public function create(Request $request)
        {

                Log::info("Received request ==> ". print_r($request->all(), 1));
                $p_services_url =  Utils::PROVIDER_PORTFOLIOS_URL;

                $user = $request->user();
                $validator = Validator::make($request->all(),[
                                'description' => 'string'
                ]);

                if ($validator->fails()) {
                        $out = [
                                'success' => false,
                                'message' => $validator->messages()
                        ];
                        return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
                }else{
                        $sp = RawQuery::query("select id from service_providers where user_id=$user->id");

                        $service_provider_id = optional(array_get($sp, 0))->id;

                        $stored = $this->store($request);
                        Log::info("Store file result ==> " . print_r($stored, 1));

                        if($stored !== false){
                                $path_url = $p_services_url .'/'. array_get($stored, 'media_url');
                                DB::insert("insert into portfolios (service_provider_id, media_data, "
                                                . " description, created_at, updated_at, status_id)  "
                                                . " values (:service_provider_id, :media_data,  :description, "
                                                . " now(),  now(), :status_id)  ", 
                                                [
                                                'service_provider_id'=> $service_provider_id,
                                                'media_data'=>json_encode($stored),
                                                'description'=>$request->get('description'),
                                                'status_id'=>DBStatus::TRANSACTION_ACTIVE
                                                ]
                                          );

                                $out = [
                                        'success' => true,
                                        'id'=>DB::getPdo()->lastInsertId(),
                                        'decription' => $request->get('description'),
                                        'media_photo' => $path_url, 
                                        'message' => 'Service provider portfolios Created'
                                ];

                                return Response::json($out, HTTPCodes::HTTP_CREATED);
                        }else{
                                return Response::json(
                                                ['success'=>false, 'message'=>'Failed to upload file'],
                                                HTTPCodes::HTTP_UNPROCESSABLE_ENTITY);
                        }
                }
        }

        /**
         * curl -i -XDELETE -H "content-type:application/json" 
         * --data '{"id":1}' 
         * 'http://127.0.0.1:8000/api/service-providers/portfolios/del'
         *  @param  Illuminate\Http\Request $request
         *  @return JSON
         *
         ***/
        public function delete(Request $request)
        {

                $validator = Validator::make($request->all(),[
                                'id' => 'required|exists:portfolios,id'
                ]);

                if ($validator->fails()) {
                        $out = [
                                'success' => false,
                                'message' => $validator->messages()
                        ];
                        return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
                }else{

                        DB::table('portfolios')
                                ->where('id', $request->get('id'))
                                ->update(['status_id' => DBStatus::TRANSACTION_DELETED]);

                        $out = [
                                'success' => true,
                                'user_id'=>$request->get('id'),
                                'message' => 'Portfolio delete OK'
                        ];

                        return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
                }
        }

        /**
         * Upload new file and store it
         * @param  Request $request Request with form data: filename and file info
         * @return boolean          True if success, otherwise - false
         */
        public  function store($request)
        {

            return Utils::upload_media($request, 'portfolios', 'file');

        }

}


