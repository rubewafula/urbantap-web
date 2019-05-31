<?php

namespace App\Utilities;

use Illuminate\Support\Facades\Log;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email {

    private $mail;

	public function __construct(){

        $this->mail = new PHPMailer(true);

        $this->mail->SMTPDebug = 2;                                      
        $this->mail->isSMTP();                                            
        $this->mail->Host       = env("MAIL_HOST");  
        $this->mail->SMTPAuth   = true;                                   
        $this->mail->Username   = env("MAIL_USERNAME");                     
        $this->mail->Password   = env("MAIL_PASSWORD");                               
        $this->mail->SMTPSecure = env("MAIL_ENCRYPTION");                                  
        $this->mail->Port       = env("MAIL_PORT");
	}


	public function sendEmail($to, $bcc, $cc, $subject, $email, $attachments){

		Log::info("Email Endpoint Called");

        try{

            $this->mail->setFrom(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"));

            if(!is_array($to)){

                $to = [$to];
            }

            foreach ($to as $recipient) {
                $this->mail->addAddress($recipient["email"], $recipient["name"]);
            }

            if(!is_array($bcc)){

                $bcc = [$bcc];
            }
            foreach ($bcc as $bcced) {
                $this->mail->addBCC($bcced["email"]);
            }

            if(!is_array($cc)){

                $cc = [$cc];
            }
            foreach ($cc as $cced) {
                $this->mail->addCC($bcced["email"]);
            }

            foreach ($attachments as $attachment) {
                $this->mail->addAttachment($attachment["filename"]);
            }

    		$this->mail->isHTML(true);                                 
            $this->mail->Subject = $subject;
            $this->mail->Body    = $email;

            $this->mail->send();

        }catch (Exception $e) {
            Log::info("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
        }
	}
}