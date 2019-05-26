<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;    

use App\Notifications\EmailNotification;
use App\User;

class EmailController extends Controller
{

	public function sendEmail(Request $request){

		$to = $request->to;
		$subject = $request->subject;
		$email = $request->email;

		$client = new User();
		$client->email = $to;

		$client->notify(new EmailNotification($to, $subject, $email));

		Log::info("Email sent successfully");
	}
}
?>