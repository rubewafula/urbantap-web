<?php

namespace App\Utilities;

use Illuminate\Support\Facades\Log;

class SMS {

	private $clientCode;
	private $shortCode;
	private $key;

	public function __construct(){

		$this->clientCode = env("SMS_CLIENT_CODE");
		$this->shortCode = env("SMS_SHORT_CODE");
		$this->key = env("SMS_KEY");
		$this->callBack = env("SMS_CALL_BACK");
	}

	public function setClientCode($clientCode){

		$this->clientCode = $clientCode;
	}

	public function setShortCode($shortCode){

		$this->shortCode = $shortCode;
	}

	public function setKey($key){

		$this->key = $key;
	}


	public function sendSMSMessage($recipients, $message, $reference){

		$quedSuccess = false;
		Log::info("SMS Endpoint Called");

        if(!is_array($recipients)){

            $recipients = explode(",", $recipients);
        }

		$sms = ["client_code" => $this->clientCode, "recipients"=>$recipients,
                    "short_code" => $this->shortCode, "message" => $message, 
                    "key" => $this->key, "reference" => $reference,
                    "call_back_url"=>""];

        $jsonRequest = json_encode($sms);

        Log::info("Prepared JSON is ".$jsonRequest);

        $httpRequest = curl_init($this->callBack);

        curl_setopt($httpRequest, CURLOPT_NOBODY, true);
        curl_setopt($httpRequest, CURLOPT_POST, true);
        curl_setopt($httpRequest, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
        curl_setopt($httpRequest, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($httpRequest, CURLOPT_POSTFIELDS, "$jsonRequest");
        curl_setopt($httpRequest, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)');

        $results = curl_exec($httpRequest);
        $status_code = curl_getinfo($httpRequest, CURLINFO_HTTP_CODE); //get status code
        curl_close($httpRequest);

        $response = json_decode($results);
        $message = "";

        if($status_code == 200 || $status_code == 201){

            Log::info("SMS Queued Successfully");
            $quedSuccess = true;
        }else{

            Log::info("SMS could not be queued by the server ".$status_code);
	       Log::info("Raw CURL Response ".$results);
        }

        return $quedSuccess;
	}
}