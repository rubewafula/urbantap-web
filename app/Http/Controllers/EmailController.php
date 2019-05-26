<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;    

use App\Utilities\Email;

class EmailController extends Controller
{

	public function sendEmail(Request $request){

		$to = ["address"=>$request->to];
		$subject = $request->subject;
		$email = $request->email;
		$bcc = [];
		$cc = [];
		$attachments = [];

		$mailerDaemon = new Email();

		$mailerDaemon->sendEmail($to, $bcc, $cc, $subject, $email, $attachments);

		Log::info("Email sent successfully");
	}
}
?>
