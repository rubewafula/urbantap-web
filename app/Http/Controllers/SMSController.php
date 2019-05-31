<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;   
use App\Outbox; 

use App\Utilities\SMS;

class SMSController extends Controller
{

    public function send_sms(Request $request){

        $recipients = $request->get('recipients');
        $message = $request->get('message');
        $reference = $request->get('reference');

        $service_provider_id = $request->service_provider_id;
        $user_id = $request->user_id;
        if(empty($recipients)){
            return json_encode(["status" => 400, "message" => 'Missing recipients ']);
        }
        $outbox = new Outbox();
        $outbox->user_id = $user_id;
        $outbox->status_id = 0;
        $outbox->service_provider_id = $service_provider_id;
        $outbox->reference = $reference;
        $outbox->message = $message;
        $outbox->msisdn = implode("|",$recipients);
        $outbox->short_code = env("SMS_SHORT_CODE");
        $outbox->save();

        $sms = new SMS();
        $smsSent = $sms->sendSMSMessage($recipients, $message, $outbox->id);

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
