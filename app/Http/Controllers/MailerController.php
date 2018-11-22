<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;

class MailerController extends Controller
{
    public function welcome_user($user)
    {
        Mail::send('mailer.welcome_user', ['user' => $user], function ($message) use ($user) {
            $subject = "Smart Soko Registration";
            $message->to($user->email, $user->name)->subject($subject);
        });
    }

}
