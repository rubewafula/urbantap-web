<?php

namespace App\Utilities;

use Illuminate\Support\Facades\Log;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email {

    private $mail;

	public function __construct(){

        $this->mail = new PHPMailer(true);

        $mail->SMTPDebug = 2;                                      
        $mail->isSMTP();                                            
        $mail->Host       = env("MAIL_HOST");  
        $mail->SMTPAuth   = true;                                   
        $mail->Username   = env("MAIL_USERNAME");                     
        $mail->Password   = env("MAIL_PASSWORD");                               
        $mail->SMTPSecure = env("MAIL_ENCRYPTION");                                  
        $mail->Port       = env("MAIL_PORT");
	}


	public function sendEmail($to, $bcc, $cc, $subject, $email, $attachments){

		Log::info("Email Endpoint Called");

        try{

            $mail->setFrom(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"));

            foreach ($to as $recipient) {
                $mail->addAddress($recipient["address"], $recipient["name"]);
            }

            foreach ($bcc as $bcced) {
                $mail->addBCC($bcced["address"]);
            }

            foreach ($cc as $cced) {
                $mail->addCC($bcced["address"]);
            }

            foreach ($attachments as $attachment) {
                $mail->addAttachment($attachment["filename"]);
            }

    		$mail->isHTML(true);                                 
            $mail->Subject = $subject;
            $mail->Body    = $email;

            $mail->send();

        }catch (Exception $e) {
            Log::info("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
	}
}