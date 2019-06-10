<?php
/** ServiceCategoryController 
  * Reuben Wafula
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


class ServicePackagesController extends Controller{

	/**
     * Display the specified service packages.
     * curl -i -XGET -H "content-type:application/json" 
     * http://127.0.0.1:8000/api/service-packages/all
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

        $results = DB::select( 
        	DB::raw("SELECT sp.id, c.id as category_id, c.category_name, sp.package_name, sp.description, sp.created_at, sp.updated_at FROM service_packages sp inner join categories c on c.id = sp.category_id where sp.status_id not in (" . DBStatus::TRANSACTION_DELETED . ") " . $filter . " limit 100") 
        );
        //dd(HTTPCodes);
        Log::info('Extracted service service_packages results : '.var_export($results, 1));
        if(empty($results)){
        		return Response::json($results, HTTPCodes::HTTP_NO_CONTENT );
        }
        return Response::json($results, HTTPCodes::HTTP_OK);

    }

    /**
     * curl -i -XPOST -H "content-type:application/json" 
     * --data '{"category_id":1, "package_name":"Golden PAP",
     *  "description":"Best salon jab for the old"}' 
     * 'http://127.0.0.1:8000/api/service-packages/create'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function create(Request $request)
    {
    
    	$validator = Validator::make($request->all(),[
		    'category_id' => 'required|exists:categories,id',
            'package_name' => 'required|unique:service_packages',
            'description' => 'string',
		]);

		if ($validator->fails()) {
			$out = [
		        'success' => false,
		        'message' => $validator->messages()
		    ];
			return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
		}else{

	    	DB::insert("insert into service_packages (category_id, package_name, "
                . " description,status_id, created_at, updated_at, deleted_at) "
                . " values (:category_id, :package_name,:description, "
                . " :status_id, now(), now(), now())", [
                    'category_id'=>$request->get('category_id'),
                    'package_name'=>$request->get('package_name'),
                    'status_id'=>DBStatus::TRANSACTION_PENDING,
                    'description'=>$request->get('description')
                ]
	    	);

	    	$out = [
		        'success' => true,
		        'id'=>DB::getPdo()->lastInsertId(),
		        'message' => 'Service package Created'
		    ];

    		return Response::json($out, HTTPCodes::HTTP_CREATED);
    	}
    }

    /**
     *  curl -i -XPUT -H "content-type:application/json" 
     * --data '{"id":1, "package_name":"Golden Ladies Salon", 
     * "description":"Best salon jab for the old", "new_name":"Golden Ladies Salon 23"}' 
     * 'http://127.0.0.1:8000/api/service-packages/update'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function update(Request $request)
    {
    	
    	$validator = Validator::make($request->all(),[
		    'id' => 'required|integer',
		    'package_name' => 'required|exists:service_packages,package_name|max:255',
            'new_name' => 'unique:service_packages,package_name|max:255',
		    'description' => 'string|max:255'

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
                $update['package_name']  =$request->get('new_name') ;
            }
            if(!empty($request->get('description'))){
                $update['description']  =$request->get('description') ;
            }
	    	DB::table('service_packages')
            ->where('id', $request->get('id'))
            ->update($update);

	    	$out = [
		        'success' => true,
		        'id'=>$request->get('id'),
		        'message' => 'Service Category updated OK'
		    ];

    		return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
    	}
    }

    /**
     * curl -i -XDELETE -H "content-type:application/json" --data 
     * '{"id":4}' 
     * 'http://127.0.0.1:8000/api/service-packages/delete'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function delete(Request $request)
    {
    	
    	$validator = Validator::make($request->all(),[
		    'id' => 'required|exists:service_packages,id'
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
            ->update(['status_id' => DBStatus::TRANSACTION_DELETED]);

	    	$out = [
		        'success' => true,
		        'id'=>$request->get('id'),
		        'message' => 'Service package marked deleted OK'
		    ];

    		return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
    	}
    }

}
