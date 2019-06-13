<?php
namespace App\Utilities;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use \Exception;

class Utils
{

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
