<?php
/** ServiceCategoryDetailsController 
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
use Illuminate\Support\Facades\Storage;


class ServicePackageDetailsController extends Controller{

    /** Common media Files allowed **/

    private $image_ext = ['jpg', 'jpeg', 'png', 'gif'];
    private $audio_ext = ['mp3', 'ogg', 'mpga', 'iff', 'm3u', 'mpa','wav', 'wma', 'aif'];
    private $video_ext = ['mp4', 'mpeg','3g2','3gp','asf','flv','m4v','mpg','swf','vob', 'wmv'];
 

	/**
     * Display the specified service package details.
     * curl -i -XGET -H "content-type:application/json" 
     * http://127.0.0.1:8000/api/service-package-details/all
     *
     * @param  \App\Category $category
     *
     * @return JSON 
     */

    public function get($package_id=null)
    {
        
        $validator = Validator::make(['service_package_id'=>$package_id],[
            'service_package_id' => 'integer|exists:service_packages,id|nullable',
        ]);
        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }
        $filter = '';
        if(!is_null($package_id)){
            $filter = " and service_package_id = '" .$package_id . "' ";
        }

        $results = DB::select( 
        	DB::raw("SELECT spd.id, sp.package_name, spd.description, spd.media_data,  sp.created_at, sp.updated_at FROM service_package_details spd inner join service_packages sp on sp.id = spd.service_package_id where spd.status_id not in (" . DBStatus::RECORD_DELETED . ") " . $filter . " limit 100") 
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
     * --data '{"service_package_id":1, "media_data":"JSON",
     *  "description":"Cared full women hair do"}' 
     * 'http://127.0.0.1:8000/api/service-package-details/create'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function create(Request $request)
    {
    
    	$validator = Validator::make($request->all(),[
		    'service_package_id' => 'required|exists:service_packages,id',
            'description' => 'string|required|max:255',
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

    	    	DB::insert("insert into service_package_details (service_package_id,"
                    . " description, media_data,status_id, created_at, updated_at, deleted_at) "
                    . " values (:service_package_id, :description,:media_data, "
                    . " :status_id, now(), now(), now())", [
                        'service_package_id'=>$request->get('service_package_id'),
                        'description'=>$request->get('description'),
                        'status_id'=>DBStatus::RECORD_PENDING,
                        'media_data'=>json_encode($stored)
                    ]
    	    	);

    	    	$out = [
    		        'success' => true,
    		        'id'=>DB::getPdo()->lastInsertId(),
    		        'message' => 'Service package detail Created OK'
    		    ];
                return Response::json($out, HTTPCodes::HTTP_CREATED);

            }else{
                return Response::json(['success'=>false, 'message'=>'Failed to upload file'], HTTPCodes::HTTP_UNPROCESSABLE_ENTITY);
            }
    		
    	}
    }

    /**
     *  curl -i -XPUT -H "content-type:application/json" 
     * --data '{"id":1, "package_name":"Golden Ladies Salon", 
     * "description":"Best salon jab for the old", "new_name":"Golden Ladies Salon 23"}' 
     * 'http://127.0.0.1:8000/api/service-package-details/update'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function update(Request $request)
    {
    	
    	$validator = Validator::make($request->all(),[
            'id' => 'required|exists:service_package_details',
            'description' => 'string|required|max:255',
        ]);

		if ($validator->fails()) {
			$out = [
		        'success' => false,
		        'message' => $validator->messages()
		    ];
			return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
		}else{

            $update = [];
           
            if(!empty($request->get('description'))){
                $update['description']  =$request->get('description') ;
            }
            
            $stored = $this->store($request);

            if($stored !== false){
                $update['media_data'] = json_encode($stored);

    	    	DB::table('service_package_details')
                ->where('id', $request->get('id'))
                ->update($update);

    	    	$out = [
    		        'success' => true,
    		        'id'=>$request->get('id'),
    		        'message' => 'Service package details updated OK'
    		    ];

        		return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
            }else{
                return Response::json(['success'=>false, 'message'=>'Failed to upload file'], HTTPCodes::HTTP_UNPROCESSABLE_ENTITY);

            }
    	}
    }

    /**
     * curl -i -XDELETE -H "content-type:application/json" --data 
     * '{"id":4}' 
     * 'http://127.0.0.1:8000/api/service-package-details/delete'
     *  @param  Illuminate\Http\Request $request
     *  @return JSON
     *
    ***/
    public function delete(Request $request)
    {
    	
    	$validator = Validator::make($request->all(),[
		    'id' => 'required|exists:service_package_details,id'
		]);

		if ($validator->fails()) {
			$out = [
		        'success' => false,
		        'message' => $validator->messages()
		    ];
			return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
		}else{
	    	DB::table('service_package_details')
            ->where('id', $request->get('id'))
            ->update(['status_id' => DBStatus::RECORD_DELETED]);

	    	$out = [
		        'success' => true,
		        'id'=>$request->get('id'),
		        'message' => 'Service package detail  marked deleted OK'
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
        $name = preg_replace('/[^A-Za-z0-9\-]/', '-', $request->get('description'));
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
