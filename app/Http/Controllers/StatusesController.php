<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;    
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;    
use App\Utilities\HTTPCodes;
use App\Utilities\DBStatus;
use Illuminate\Support\Facades\Validator;

class StatusesController extends Controller
{
   /**
     * Display the specified service packages.
     * curl -i -XGET -H "content-type:application/json" 
     * http://127.0.0.1:8000/api/status-categories/all
     * http://127.0.0.1:8000/api/status-categories/get/{id}
     *
     * @param  \App\Category $category
     *
     * @return JSON 
     */
 

    public function get($id=null)
    {
        
        $validator = Validator::make(['status_category_id'=>$id],[
            'status_category_id' => 'integer|exists:status_categories,id|nullable',
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
            $filter = " where status_category_id = '" .$id . "' ";
        }

        $query = "SELECT s.id, s.status_code, s.description, sc.category_code, "
            . " sc.description as category from statuses s inner join "
            . " status_categories sc on sc.id=s.status_category_id " 
            . $filter . " limit 100";

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

    /**
     * curl -i -XPOST -H "content-type:application/json" 
     * --data '{"category_code":"001", "description":"USE ACCOUNTS"}' 
     * 'http://127.0.0.1:8000/api/status-categories/create'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function create(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'status_code' => 'required|unique:statuses',
            'description' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }else{

            DB::insert("insert into statuses (status_code, description, "
                . " created_at, updated_at, deleted_at) "
                . " values (:status_code, :description, "
                . " now(), now(), null)", [
                    'status_code'=>$request->get('status_code'),
                    'description'=>$request->get('description')
                ]
            );

            $out = [
                'success' => true,
                'id'=>DB::getPdo()->lastInsertId(),
                'message' => 'Status created OK'
            ];

            return Response::json($out, HTTPCodes::HTTP_CREATED);
        }
    }

    /**
     *  curl -i -XPUT -H "content-type:application/json" 
     * --data '{"id":1, "category_code":"001", 
     * "description":"User accounts statuses"}' 
     * 'http://127.0.0.1:8000/api/status-categories/update'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function update(Request $request)
    {
        
        $validator = Validator::make($request->all(),[
            'id' => 'required|integer',
            'status_code' => 'required|exists:statuses',
            'new_code' => 'unique:statuses,status_code|max:15|nullable',
            'description' => 'string|max:255|nullable'
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }else{

            $update = [];
            if(!empty($request->get('new_code')) ){
                $update['status_code']  =$request->get('status_code') ;
            }
             if(!empty($request->get('description')) ){
                $update['description']  =$request->get('description') ;
            }
           
            DB::table('statuses')
            ->where('id', $request->get('id'))
            ->update($update);

            $out = [
                'success' => true,
                'id'=>$request->get('id'),
                'message' => 'Status updated OK'
            ];

            return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
        }
    }

    

}
