<?php

/** CategoryController 
  * Odero Oluoch
  * Handles category crude
  **/
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;	
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;	
use App\Utilities\HTTPCodes;
use App\Utilities\DBStatus;
use Illuminate\Support\Facades\Validator;


class CategoriesController extends Controller{
    /**
     * Display the specified category.
     * Endpoint GET http://127.0.0.1:8000/api/categories/all
     *
     * @param  \App\Category $category
     *
     * @return JSON 
     */

     public function get($id=null)
    {
        $validator = Validator::make(['id'=>$id],[
            'id' => 'integer|exists:categories,id|nullable',
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
            $filter = " and id = '" .$id . "' ";
        }

        $results = DB::select( 
        	DB::raw("SELECT category_name, created_at, updated_at FROM categories where status_id not in (" . DBStatus::RECORD_DELETED . ") " . $filter . "limit 100") 
        );
        //dd(HTTPCodes);
        Log::info('Extracted service categroy results : '.var_export($results, 1));
        if(empty($results)){
        		return Response::json($results, HTTPCodes::HTTP_NO_CONTENT );
        }
        return Response::json($results, HTTPCodes::HTTP_OK);

    }
    /**
     * curl -i -XPOST -H "content-type:application/json" 
     * --data '{"category_name":"Gym+Trainers"}' 
     * 'http://127.0.0.1:8000/api/categories/create' 
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function create(Request $request)
    {
    	
    	$validator = Validator::make($request->all(),[
		    'category_name' => 'required|unique:categories|max:255'
		]);

		if ($validator->fails()) {
			$out = [
		        'success' => false,
		        'message' => $validator->messages()
		    ];
			return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
		}else{
	    	DB::insert("insert into categories (category_name, created_at, "
	    			. "updated_at) values (:name, now(), now())",
	    		array('name'=>$request->get('category_name'))
	    	);

	    	$out = [
		        'success' => true,
		        'id'=>DB::getPdo()->lastInsertId(),
		        'message' => 'Service Category Created'
		    ];

    		return Response::json($out, HTTPCodes::HTTP_CREATED);
    	}
    } /**
     * curl -i -XPUT -H "content-type:application/json" --data 
     * '{"id":4,"category_name":"shoe23", "new_name":"under 23 shoes"}' 
     * 'http://127.0.0.1:8000/api/categories/update'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function update(Request $request)
    {
    	
    	$validator = Validator::make($request->all(),[
		    'id' => 'required|integer',
		    'category_name' => 'required|exists:categories,category_name|max:255',
		    'new_name' => 'required|unique:categories,category_name|max:255'

		]);

		if ($validator->fails()) {
			$out = [
		        'success' => false,
		        'message' => $validator->messages()
		    ];
			return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
		}else{
	    	DB::table('categories')
            ->where('id', $request->get('id'))
            ->update(['category_name' => $request->get('new_name')]);

	    	$out = [
		        'success' => true,
		        'id'=>$request->get('id'),
		        'message' => 'Service Category updated OK'
		    ];

    		return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
    	}
    } /**
     * curl -i -XDELETE -H "content-type:application/json" --data 
     * '{"id":4}' 
     * 'http://127.0.0.1:8000/api/categories/delete'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function delete(Request $request)
    {
    	
    	$validator = Validator::make($request->all(),[
		    'id' => 'required|exists:categories,id'
		]);

		if ($validator->fails()) {
			$out = [
		        'success' => false,
		        'message' => $validator->messages()
		    ];
			return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
		}else{
	    	DB::table('categories')
            ->where('id', $request->get('id'))
            ->update(['status_id' => DBStatus::RECORD_DELETED]);

	    	$out = [
		        'success' => true,
		        'id'=>$request->get('id'),
		        'message' => 'Service Category marked deleted OK'
		    ];

    		return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
    	}
    }





}
