<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

use App\Utilities\Email;
use App\Outbox;

class EmailController extends Controller
{

	public function sendEmail(Request $request){

		Log::info("Got called from email consumer queue", $request->all() ?: ['Nothing on request']);
		if(strlen($request->email_address) < 1){
			Log::info("Request has no valid email addresses");
			return;
		}
		$to = ["email_address"=>$request->email_address];
		$subject = $request->subject;
		$email = $request->email;
		$bcc = [];
		$cc = [];
		$attachments = [];

		$service_provider_id = $request->service_provider_id;
		$user_id = $request->user_id;
		$reference = $request->reference;

		$outbox = new Outbox();
		$outbox->user_id = $user_id;
		$outbox->status_id = 0;
		$outbox->message = "EMAIL";
		$outbox->service_provider_id = $service_provider_id;
		$outbox->reference = $reference;
		$outbox->save();

		$mailerDaemon = new Email();

		Log::info($request->all());

		$mailerDaemon->sendEmail($to, $bcc, $cc, $subject, $email, $attachments);

		Log::info("Email sent successfully to ".$request->email_address." subject ".$subject);
	}
}
?>
