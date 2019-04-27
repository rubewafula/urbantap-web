<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request; 

use PhpAmqpLib\Message\AMQPMessage;

use App\Utilities\RabbitMQConnection;
use App\MpesaTransaction;
use App\Transaction;
use App\User;
use App\ServiceProvider;
use App\Booking;
use App\Status;
use App\Utilities\DBStatus;
use App\Utilities\HTTPCodes;
use App\Utilities\SMS;

class PaymentsController extends Controller
{

	private $rabbitMQConnection;
	private $connection;
	private $channel;


	public function mpesa_register_url(){

		Log::info(" Calling MPESA Register Confirm");

		$url = 'https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl';

		$curl_post_data = array(

				'ShortCode' => env("SHORT_CODE"),
				'ResponseType' => 'Completed',
				'ConfirmationURL' => env("CONFIRMATION_URL"),
				'ValidationURL' => env("VALIDATION_URL")
				);

		$data_string = json_encode($curl_post_data);

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json',
					'Authorization:Bearer clr9eF6kx17kcC7A6E1kZHItUyfC')); //setting custom header
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

		Log::info("Got response from the curl MPESA");

		$curl_response = curl_exec($curl);

		$json_response = json_decode($curl_response);

		Log::info($json_response);

		return $json_response;
	}


	public static function mpesa_generate_token(){

		Log::info("Calling token generation Safaricom API");

		$url = env("TOKEN_URL");

		$consumer_key = env("CONSUMER_KEY");
		$consumer_secret = env("CONSUMER_SECRET");

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		$credentials = base64_encode($consumer_key.':'.$consumer_secret);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic '.$credentials)); //setting a custom header
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$curl_response = curl_exec($curl);

		Log::info($curl_response);

		$decodedJSON = json_decode($curl_response, true);

		return $decodedJSON["access_token"];
	}


	public function receive_mpesa(){

		Log::info("Callback URL for MPESA called");

		$postData = file_get_contents('php://input');

		Log::info("Got post data from Safaricom ".$postData);

		if( $postData != null){

			$rabbitMQConnection = new RabbitMQConnection();
			$connection = $rabbitMQConnection->getConnection();
			$channel = $connection->channel();

			$dataInJSON = json_encode($postData);

			// $channel->queue_declare(env("MPESA_DEPOSITS_QUEUE"), false, false, false, false);

			$msg = new AMQPMessage($postData);
			$publishResult = $channel->basic_publish($msg, '', env("RABBIT_MPESA_QUEUE"));

			echo("Publishing Result ".$publishResult);

			Log::info("Message published to the queue successfully");

			echo '{"ResultCode": 0, "ResultDesc": "Accepted"}';

			$channel->close();
			$connection->close();
		}

	}


	public function receive_mpesa_tips(){

		Log::info("URL for MPESA Tips Called");
		$postData = file_get_contents('php://input');
		Log::info("Money for Tips Received from MPESA ".$postData);
	}

	public function mpesa_payment(){

		$user_id = "";
		$running_balance = 0;

		Log::info("Callback URL from Inbox Consumer called [MPESA Payments]");

		$postData = file_get_contents('php://input');

		Log::info($postData);

		if( $postData != null){

			$decoded = json_decode($postData);

			$transaction_type = $decoded->TransactionType;
			$transaction_id = $decoded->TransID;
			$transaction_time = $decoded->TransTime;
			$transaction_amount = $decoded->TransAmount;
			$business_code = $decoded->BusinessShortCode;
			$bill_ref_no = $decoded->BillRefNumber;
			$invoice_number = $decoded->InvoiceNumber;
			$org_account_balance = $decoded->OrgAccountBalance;
			$third_party_trans_id = $decoded->ThirdPartyTransID;
			$msisdn = $decoded->MSISDN;
			$first_name = $decoded->FirstName;
			$middle_name = $decoded->MiddleName;
			$last_name = $decoded->LastName;

			$name = $first_name. " ".$middle_name." ".$last_name;

			Log::info("Now preparing the query to insert the MPESA Transaction");

                        DB::insert("insert into mpesa_transactions (message,transaction_ref,transaction_time,
				amount,paybill_no,mpesa_code,bill_ref_no,account_no,msisdn,names,status_id) 
				VALUES(:message,:transaction_ref,:transaction_time,:amount,:paybill_no,
				:mpesa_code,:bill_ref_no,:account_no,:msisdn,:names,0)",
				 ['message'=>$transaction_type, 'transaction_ref'=>$transaction_id, 
				 'transaction_time'=>$transaction_time,'amount'=>$transaction_amount,
				 'paybill_no'=>$business_code,'mpesa_code'=>$transaction_id,
				 'bill_ref_no'=>$bill_ref_no,'account_no'=>$bill_ref_no,
				 'msisdn'=>$msisdn,'names'=>$name]);

			$tranID = DB::getPdo()->lastInsertId();

			$user = DB::select(DB::raw("select * from users where phone_no='".$msisdn."'"));

			if(count($user) > 0){

				$user_id = $user[0]->id;
				$running_balance_rs = DB::select(DB::raw("select * from user_balance where 
							user_id='".$user_id."'"));

				if(count($running_balance_rs) > 0){

					$running_balance = $running_balance_rs[0]->balance;
				}

			}else{

				$user_id = DB::table('users')->insertGetId(
						array("first_name"=>$first_name,"last_name"=>$last_name, "user_group"=>4,"phone_no"=>$msisdn,
							"email"=>$msisdn."@urbantap.co.ke","password"=>Hash::make($msisdn))
						);
			}

			$balance = $running_balance+$transaction_amount;

			$transaction = new Transaction();

			$transaction->user_id=$user_id;
			$transaction->transaction_type="CREDIT";
			$transaction->reference=$transaction_id;
			$transaction->amount=$transaction_amount;
			$transaction->running_balance=$balance;
			$transaction->status_id=0;

			$transaction->save();

			if(count($user) > 0){

				DB::update("update user_balance set balance = '".$balance."', transaction_id = '".$transaction_id."' where 
						user_id = '".$user_id."'");
			}else{

				DB::insert("insert into user_balance(user_id,balance,transaction_id,created) 
					    VALUES(:user_id, :balance, :transaction_id, now())",['user_id'=>$user_id,
					    'balance'=>$balance,'transaction_id'=>$transaction->id]);
			}
			DB::update("update mpesa_transactions set status_id = '1' where id = '".$tranID."'");

			$transaction->status_id=1;
			$transaction->save();

			$booking_amount = 0;
			$booking_reference = "";
			$balance = 0;
			$booking_time = "";

			$sms = new SMS();

			$bookingRs = DB::select(DB::raw("select * from bookings where id='".$invoice_number."'"));

			if(count($bookingRs) > 0){

				$booking_amount = $bookingRs[0]->amount;
				$balance = $booking_amount - $transaction_amount;
				$booking_time = $bookingRs[0]->booking_time; 

				$serviceProvider = ServiceProvider::find($bookingRs[0]->service_provider_id);

				Log::info("Service Provider ID is ".$bookingRs[0]->service_provider_id);
				Log::info("User ID  for the Provider is ".$serviceProvider->user_id);

				$providerMsisdn = User::find($serviceProvider->user_id)->phone_no;
			}else{

				Log::info("Booking called back by MPESA Number $bill_ref_no NOT FOUND");
				$out = [
                   			 'status' => 421,
                    			'success' => false,
                    			'message' => 'Booking Not Found'
                		];

				$customerMessage = "Dear $name, you have successfully topped up KSh. $transaction_amount to your URBANTAP account, reference $bill_ref_no. Your can book for any of our service using the float in your account. Thank you.";
	
				$sms->sendSMSMessage($msisdn, $customerMessage, $bill_ref_no);

		                return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
				

			}

			DB::insert("insert into payments(reference, date_received, booking_id, 
		                payment_method, paid_by_name, paid_by_msisdn, amount, 
                                received_payment, balance, status_id, created_at) 
				values(:reference, now(), :booking_id, 
                    		'MPESA', :paid_by_name, :paid_by_msisdn, :amount, 
                    		:received_payment, :balance, :status_id, now())",['reference'=>$transaction_id,
				'booking_id'=>$booking_id,'paid_by_name'=>$name,
				'paid_by_msisdn'=>$msisdn,'amount'=>$booking_amount, 
				'received_payment'=>$transaction_amount,'balance'=>$balance,
				'status_id'=>DBStatus::BOOKING_PAID]);

			DB::insert("insert into booking_trails(booking_id,status_id,description,originator,created_at)
				    values(:booking_id,:status_id,'MPESA TRANSACTION','MPESA', now())",
				    ['booking_id'=>$bill_ref_no,'status_id'=>DBStatus::BOOKING_PAID]);

            DB::update("update bookings set status_id = '".$invoice_number."', updated_at = now()
			 where id = '".DBStatus::BOOKING_PAID."'");

            $customerMessage = "";
            $serviceProviderMessage = "";

            $smsReference = $bill_ref_no;
            $customerMsisdn = $msisdn;

            $halfAmount = ceil($booking_amount/2);
            
            if($balance <= $halfAmount){

                $customerMessage = "Dear $name, you have successfully paid KSh. $transaction_amount for your booking, reference $invoice_number. Your slot has been reserved for $booking_time. Thank you.";

                $serviceProviderMessage = "Dear Service Provider, Booking reference number, $invoice_number has been reserved. Please note the booking time is $booking_time for this request.";

                $sms->sendSMSMessage($customerMsisdn, $customerMessage, $smsReference);
                $sms->sendSMSMessage($providerMsisdn, $serviceProviderMessage, $smsReference);

            }else {

                $amountToBooking =  $halfAmount - $transaction_amount;
                $customerMessage = "Dear $name, you have successfully paid KSh. $transaction_amount for your booking, reference $invoice_number. Please pay at least KSh. $amountToBooking to reserve your booking. Thank you.";

                $sms->sendSMSMessage($customerMsisdn, $customerMessage, $smsReference);
            }

            $out = [
                'status' => 200,
                'success' => true,
                'message' => 'MPESA Payment Received Successfully'
            ];

            return Response::json($out, HTTPCodes::HTTP_ACCEPTED);

        }

    }

    public function booking_status(Request $request){

        $booking_id =  $request->get('booking_id');

        Log::info("Querying Data for Booking with Reference ".$booking_id);

        $booking = Booking::find($booking_id);
        $booking_status = "";
        $out = [];

        if($booking != null){

            $booking_status = $booking->status->description;
            $out = ["status" => 200, "message" => $booking_status];

        }else{

            $out = ["status" => 404, "message" => "Booking Not Found"];
        }

        return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
    }


    public function stkPush(Request $request){

        Log::info("STK Call Done");

        $url = env("SAF_STK_URL");
        $token = PaymentsController::mpesa_generate_token();

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$token)); //setting custom header

        $curl_post_data = array(

          'BusinessShortCode' => env("SHORT_CODE"),
          'Password' => env("SHORT_CODE"),
          'Timestamp' => date('YmdHis'),
          'TransactionType' => 'CustomerPayBillOnline',
          'Amount"' => $request->get('amount'),
          'PartyA' => $request->get('msisdn'),
          'PartyB' => env("SHORT_CODE"),
          'PhoneNumber' => $request->get('msisdn'),
          'CallBackURL' => env("STK_CALLBACK"),
          'AccountReference' => $request->get('reference'),
          'TransactionDesc' => 'Booking Payment'
        );

        $data_string = json_encode($curl_post_data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

        $curl_response = curl_exec($curl);
        Log::info("Got STK Response Data ".$curl_response);
    }

}
