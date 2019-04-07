<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;    

use App\Utilities\SMS;

class SMSController extends Controller
{

    public function send_sms(Request $request){

        $recipients = $request->get('recipients');
        $message = $request->get('message');
        $reference = $request->get('reference');

        $sms = new SMS();
        $smsSent = $sms->sendSMSMessage($recipients, $message, $reference);

        if($smsSent){

            $status_code = 200;
            $message = "SMS Queued Successfully";
        }else{

            $status_code = 421;
            $message = "SMS could not be queued by the server";
        }

        $returned = ["status" => $status_code, "message" => $message];

        return json_encode($returned);

    }

}