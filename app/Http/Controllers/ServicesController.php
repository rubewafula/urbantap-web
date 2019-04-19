<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;    
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;    
use App\Utilities\HTTPCodes;
use App\Utilities\DBStatus;
use Illuminate\Support\Facades\Validator;

class ServicesController extends Controller
{
   /**
     * Display the specified service packages.
     * curl -i -XGET -H "content-type:application/json" 
     * http://127.0.0.1:8000/api/services/all
     * http://127.0.0.1:8000/api/get/{category_id}
     *
     * @param  \App\Category $category
     *
     * @return JSON 
     */
 

    public function get($category_id=null)
    {
        
        $validator = Validator::make(['category_id'=>$category_id],[
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
        if(!is_null($category_id)){
            $filter = " and category_id = '" .$category_id . "' ";
        }

        $query = "SELECT s.category_id, c.category_name, s.service_name FROM services s inner join categories c on c.id = s.category_id where s.status_id not in (" . DBStatus::RECORD_DELETED . ") " . $filter . " limit 100";
        Log::info('Query : ' . $query);

        $results = DB::select( 
            DB::raw($query) 
        );
        //dd(HTTPCodes);
        Log::info('Extracted service services results : ' . var_export($results, 1));

        if(empty($results)){
            return Response::json([], HTTPCodes::HTTP_NO_CONTENT );
        }
        return Response::json($results, HTTPCodes::HTTP_OK);

    }

    /**
     * curl -i -XPOST -H "content-type:application/json" 
     * --data '{"category_id":1, "service_mame":"Golden Mums"}' 
     * 'http://127.0.0.1:8000/api/services/create'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function create(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'category_id' => 'required|exists:categories,id',
            'service_name' => 'required|unique:services'
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }else{

            DB::insert("insert into services (category_id, service_name, "
                . " status_id, created_at, updated_at, deleted_at) "
                . " values (:category_id, :service_name, "
                . " :status_id, now(), now(), now())", [
                    'category_id'=>$request->get('category_id'),
                    'service_name'=>$request->get('service_name'),
                    'status_id'=>DBStatus::RECORD_PENDING
                ]
            );

            $out = [
                'success' => true,
                'id'=>DB::getPdo()->lastInsertId(),
                'message' => 'Service Created OK'
            ];

            return Response::json($out, HTTPCodes::HTTP_CREATED);
        }
    }

    /**
     *  curl -i -XPUT -H "content-type:application/json" 
     * --data '{"id":1, "service_name":"Golden Mums", 
     * "new_name":"Golden Mums and Mums"}' 
     * 'http://127.0.0.1:8000/api/services/update'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function update(Request $request)
    {
        
        $validator = Validator::make($request->all(),[
            'id' => 'required|integer',
            'service_name' => 'required|exists:services,service_name|max:255',
            'new_name' => 'unique:services,service_name|max:255'
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }else{

            $update = [];
            if(!empty($request->get('new_name')) ){
                $update['service_name']  =$request->get('service_name') ;
            }
           
            DB::table('services')
            ->where('id', $request->get('id'))
            ->update($update);

            $out = [
                'success' => true,
                'id'=>$request->get('id'),
                'message' => 'Service updated OK'
            ];

            return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
        }
    }

    /**
     * curl -i -XDELETE -H "content-type:application/json" --data 
     * '{"id":4}' 
     * 'http://127.0.0.1:8000/api/services/del'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function delete(Request $request)
    {
        
        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:services,id'
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }else{
            DB::table('services')
            ->where('id', $request->get('id'))
            ->update(['status_id' => DBStatus::RECORD_DELETED]);

            $out = [
                'success' => true,
                'id'=>$request->get('id'),
                'message' => 'Service package marked deleted OK'
            ];

            return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
        }
    }

}
