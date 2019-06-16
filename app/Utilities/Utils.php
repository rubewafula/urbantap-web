<?php
namespace App\Utilities;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use \Exception;

class Utils
{

    const IMAGE_EXT = ['jpg', 'jpeg', 'png', 'gif'];
    const AUDIO_EXT = ['mp3', 'ogg', 'mpga', 'iff', 'm3u', 'mpa','wav', 'wma', 'aif'];
    const VIDEO_EXT = ['mp4', 'mpeg','3g2','3gp','asf','flv','m4v','mpg','swf','vob', 'wmv'];


    /** image append urls **/
    #TODO: This would work well from .env then utils
    const IMAGE_URL               =  'https://urbantap.co.ke:9173/storage/static/image/avatar/';
    const SERVICE_PROVIDERS_URL   =  'https://urbantap.co.ke:9173/storage/static/image/service-providers/';
    const ICONS_URL               =  'https://urbantap.co.ke:9173/storage/static/image/icons/';
    const PROFILE_URL             =  'https://urbantap.co.ke:9173/storage/static/image/profiles/';
    const PROVIDER_SERVICES_URL   =  'https://urbantap.co.ke:9173/storage/static/image/provider-services/';
    const PROVIDER_PORTFOLIOS_URL =  'https://urbantap.co.ke:9173/storage/static/image/portfolios/';
    const SERVICE_IMAGE_URL       =  'https://urbantap.co.ke:9173/storage/static/image/services/';



    static function getType($ext)
    {
        if (in_array($ext, Utils::IMAGE_EXT)) {
            return 'image';
        }

        if (in_array($ext, Utils::AUDIO_EXT)) {
            return 'audio';
        }

        if (in_array($ext, Utils::AUDIO_EXT)) {
            return 'video';
        }

        return 'unknown';
    }

    static function allExtensions()
    {
        return array_merge(Utils::IMAGE_EXT, Utils::AUDIO_EXT, Utils::VIDEO_EXT);
    }


     static  function  upload_media($request, $base_dir, $file_name='file')
    {

        $file = $request->file($file_name);
        if(is_null($file)){
            /** No file uploaded accept and proceeed **/
            return FALSE;
        }
        $max_size = (int)ini_get('upload_max_filesize') * 1000;
        $all_ext = implode(',', Utils::allExtensions());

        
        $ext = $file->getClientOriginalExtension();
        $size = $file->getClientSize();
        $name = preg_replace('/[^A-Za-z0-9\-]/', '-',$file->getClientOriginalName());
        $type = Utils::getType($ext);

        if($type == 'unknown'){
            Log::info("Aborting file upload unknown file type "+ $type);
            return FALSE;
        }

        $fullPath = $name . '.' . $ext;

        $file_path = 'public/static/' . $type . '/' .$base_dir . '/'.$fullPath;

        if (Storage::exists($file_path)) {
            Storage::delete($file_path);
        }

        if (Storage::putFileAs('public/static/' . $type . '/' .$base_dir, $file, $fullPath)) {
            return [
                    'media_url'=>$fullPath,
                    'name' => $name,
                    'type' => $type,
                    'extension' => $ext,
                    'size'=>$size
                ];
        }

        return FALSE;
    }

    static function loadTemplateData($template_data, $data)
    {
        // Remove sms key from data
        $data = Arr::except($data, 'sms');
        Log::info("Data to Load " . print_r($data, 1));
        $dd = [];
        array_walk($data, function ($value, $key) use (&$dd) {
            Log::info("Last ITEM -Walk $key ==> " . print_r($value, 1));
            if (is_array($value)) {
                array_walk($value, function ($val, $k) use (&$dd) {
                    $dd[$k] = $val;
                });
            } else {
                $dd[$key] = $value;
            }
        });
        Log::info("Last after walking  " . print_r($dd, 1));


        $patterns = array_keys($dd);
        $keys = [];
        array_walk($patterns, function ($v, $k) use (&$keys) {
            $keys[] = '/\[\[(\s+)?' . $v . '(\s+)?\]\]/';
        });

        $html = preg_replace($keys, array_values($dd), $template_data);

        $confirm_rex = "/\[\[(\s\+)?[\w-]+(\s\+)?\]\]/";

        if (preg_match_all($confirm_rex, $html)) {
            throw new Exception('Email template not properly completed. ');
        }

        return $html;

    }

    static function generateMPESAOAuthToken(){

        $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $consumer_key = env("PAYBILL_CONSUMER_KEY");
        $consumer_secret = env("PAYBILL_CONSUMER_SECRET");

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        $credentials = base64_encode($consumer_key.':'.$consumer_secret);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic '.$credentials)); //setting a custom header
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $curl_response = curl_exec($curl);
        $response = json_decode($curl_response, true);

        return $response["access_token"];
    }

     static function mpesaGenerateSTKPassword($timestamp){

        $shortCode = env("PAYBILL_NO");
        $passKey = env("PASS_KEY");

        return base64_encode($shortCode.$passKey.$timestamp);
    }

}

?>