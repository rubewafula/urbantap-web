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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;


class UserPersonalDetailsController extends Controller{

     private $image_ext = ['jpg', 'jpeg', 'png', 'gif'];

	 /**
     * Display the specified service providers.
     * curl -i -XGET -H "content-type:application/json" 
     * http://127.0.0.1:8000/api/user-personal-details/all
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
        //die(print_r($req, 1));
        if(array_key_exists('page', $req)){
             $page = is_numeric($request['page']) ? $request['page'] : 1;
        }
        if(array_key_exists('limit', $req)){
             $limit = is_numeric($request['limit']) ? $request['limit'] : null;
        }
       
    	$validator = Validator::make(['id'=>$user_id],
    		['user_id'=>'integer|exists:users,id|nullable']
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
            $filter = " and u.id = '" .$user_id . "' ";
        }

        $rawQuery = "SELECT d.id_number, d.date_of_birth, d.gender,  d.passport_photo, "
            . " d.home_location work_phone_no, u.name, u.phone_no, u.email "
            . " FROM  user_personal_details  d inner join users u on u.id = d.user_id "
            . " where 1=1  " . $filter ;

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
     * --data '{"user_id":1, "id_number":"66373773",
     *  "date_of_birth":"2019-01-01", "gender":"Male", "passport_photo":@file,
     *  "home_location":"kasarani", "work_phone_no":"0200001010"}' 
     * 'http://127.0.0.1:8000/api/user-personal-details/create'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function create(Request $request)
    {

        $profile_url =  URL::to('/static/image/profiles/');

    	$validator = Validator::make($request->all(),[
		    'user_id' => 'required|exists:users,id|unique:user_personal_details,user_id',
            'id_number' => 'nullable|integer|unique:user_personal_details',
            'date_of_birth' => 'nullable|date|date_format:Y-m-d',
            'gender' =>'in:Male, Female, Un-disclosed|nullable',
            'passport_photo' =>'string',
            'home_location' =>'string|nullable',
            'work_phone_no' =>'string|nullable'
		]);

		if ($validator->fails()) {
			$out = [
		        'success' => false,
		        'message' => $validator->messages()
		    ];
			return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
		}else{
            $stored = $this->store($request);
            if($stored !== false){

            	DB::insert("insert into user_personal_details (user_id, id_number, "
                    . " date_of_birth, gender, passport_photo, home_location, "
                    . " created_at, updated_at)  "
                    . " values (:user_id, :id_number,  :date_of_birth, :gender, "
                    . " :passport_photo, :home_location, now(),  now())  ", 
                        [
                            'user_id'=> $request->get('user_id'),
                            'id_number'=>$request->get('id_number'),
                            'date_of_birth'=>$request->get('date_of_birth'),
                            'gender'=>$request->get('gender'),
                            'home_location'=>$request->get('home_location'),
                            'passport_photo'=>json_encode($stored) 
                        ]
            	    );

    	    	$out = [
    		        'success' => true,
    		        'id'=>DB::getPdo()->lastInsertId(),
                    'profile_photo_url' => $profile_url . $store['media_url'],
    		        'message' => 'Service provider Created'
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
     * curl -i -XPOST -H "content-type:application/json" 
     * --data '{"user_id":1, "id_number":"66373773",
     *  "date_of_birth":"2019-01-01", "gender":"Male", "passport_photo":@file,
     *  "home_location":"kasarani", "work_phone_no":"0200001010"}' 
     * 'http://127.0.0.1:8000/api/user-personal-details/update'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function update(Request $request)
    {
    	
        $profile_url =  URL::to('/static/image/profiles/');

    	$validator = Validator::make($request->all(),[
            'user_id' => 'required|exists:user_personal_details,user_id',
            'id_number' => 'integer|unique:user_personal_details|nullable',
            'date_of_birth' => 'date|date_format:Y-m-d|nullable',
            'gender' =>'in:Male, Female, Un-disclosed|nullable',
            'passport_photo' =>'string|nullable',
            'home_location' =>'string|nullable',
            'work_phone_no' =>'string|nullable'
        ]);

		if ($validator->fails()) {
			$out = [
		        'success' => false,
		        'message' => $validator->messages()
		    ];
			return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
		}else{

            $update = [];
            if(!empty($request->get('id_number')) ){
                $update['id_number']  =$request->get('id_number') ;
            }
            if(!empty($request->get('date_of_birth')) ){
                $update['date_of_birth']  =$request->get('date_of_birth') ;
            }
             if(!empty($request->get('gender')) ){
                $update['gender']  =$request->get('gender') ;
            }
            
            if(!empty($request->get('home_location')) ){
                $update['home_location']  =$request->get('home_location') ;
            }
            if(!empty($request->get('work_phone_no')) ){
                $update['work_phone_no']  =$request->get('work_phone_no') ;
            }

            $stored = $this->store($request);

            if($stored !== false)
                $update['passport_photo'] = json_encode($stored);
           

	    	DB::table('user_personal_details')
                ->where('user_id', $request->get('user_id'))
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
     * Upload new file and store it
     * @param  Request $request Request with form data: filename and file info
     * @return boolean          True if success, otherwise - false
     */
    public function store(Request $request)
    {
        $max_size = (int)ini_get('upload_max_filesize') * 1000;
        $all_ext = implode(',', $this->image_ext);

        $this->validate($request, [
            'name' => 'nullable|unique:files',
            'file' => 'nullable|file|mimes:' . $all_ext . '|max:' . $max_size
        ]);

        $file = $request->file('file');
        if(is_null($file)){
            /** No file uploaded accept and proceeed **/
            return null;
        }
        $ext = $file->getClientOriginalExtension();
        $size = $file->getClientSize();
        $name = preg_replace('/[^A-Za-z0-9\-]/', '-', $request->get('user_id'));
        $type = $this->getType($ext);

        if($type == 'unknown'){
            Log::info("Aborting file upload unknown file type "+ $type);
            return false;
        }

        $file_path = 'public/static/' . $type . '/profiles/'.$name . '.' . $ext;;

        if (Storage::exists($file_path)) {
            Storage::delete($file_path);
        }

        if (Storage::putFileAs('public/static/' . $type .  '/profiles/', $file, $name . '.' . $ext)) {
            return [
                    'media_url'=>$name . '.' . $ext,
                    'name' => $name,
                    'type' => $type,
                    'extension' => $ext,
                    'size'=>$size
                ];
        }

        return false;
    }



    /**
     * Get type by extension
     * @param  string $ext Specific extension
     * @return string      Type
     */
    private function getType($ext)
    {
        if (in_array($ext, $this->image_ext)) {
            return 'image';
        }

        return 'unknown';
    }


}


