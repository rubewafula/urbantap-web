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
use App\Utilities\RawPaginate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;


class ServiceProviderPortfoliosController extends Controller{

    private $image_ext = ['jpg', 'jpeg', 'png', 'gif'];
    private $audio_ext = ['mp3', 'ogg', 'mpga', 'iff', 'm3u', 'mpa','wav', 'wma', 'aif'];
    private $video_ext = ['mp4', 'mpeg','3g2','3gp','asf','flv','m4v','mpg','swf','vob', 'wmv'];
 

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

        $filter= '';
        if(!is_null($service_provider_id)){
            $filter = " and p.service_provider_id = '" .$service_provider_id . "' ";
        }

        $rawQuery = "SELECT p.media_data, p.description "
            . " FROM  portfolios p  where 1=1  " . $filter ;

        $results = RawPaginate::paginate($rawQuery, $page=$page, $limit=$limit);

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

    	$validator = Validator::make($request->all(),[
		    'service_provider_id' => 'required|exists:service_providers,id',
            'description' => 'string'
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

            	DB::insert("insert into portfolios (service_provider_id, media_data, "
                    . " description, created_at, updated_at, status_id)  "
                    . " values (:service_provider_id, :media_data,  :description, "
                    . " now(),  now(), :status_id)  ", 
                        [
                            'service_provider_id'=> $request->get('service_provider_id'),
                            'media_data'=>json_encode($stored),
                            'description'=>$request->get('description'),
                            'status_id'=>DBStatus::RECORD_PENDING
                        ]
            	    );

    	    	$out = [
    		        'success' => true,
    		        'id'=>DB::getPdo()->lastInsertId(),
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
                ->update(['status_id' => DBStatus::RECORD_DELETED]);

	    	$out = [
		        'success' => true,
		        'user_id'=>$request->get('id'),
		        'message' => 'Portfolio delete OK'
		    ];

    		return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
    	}
    }

    /**
     * Get all extensions
     * @return array Extensions of all file types
     */
    private function allExtensions()
    {
        return array_merge($this->image_ext, $this->audio_ext, $this->video_ext);
    }


    /**
     * Upload new file and store it
     * @param  Request $request Request with form data: filename and file info
     * @return boolean          True if success, otherwise - false
     */
    public function store(Request $request)
    {
        $max_size = (int)ini_get('upload_max_filesize') * 1000;
        $all_ext = implode(',', $this->allExtensions());

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

        $fullPath = 'public/' . $type . '/' .$name . '.' . $ext;

        if (Storage::putFileAs('public/' . $type . '/', $file, $name . '.' . $ext)) {
            return [
                    'media_url'=>$fullPath,
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

        if (in_array($ext, $this->audio_ext)) {
            return 'audio';
        }

        if (in_array($ext, $this->video_ext)) {
            return 'video';
        }

        return 'unknown';
    }



}


