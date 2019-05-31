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

class PaymentController extends Controller
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

			$rabbitMQConnection = new RabbitMQ();


			// $channel->queue_declare(env("MPESA_DEPOSITS_QUEUE"), false, false, false, false);

			$publishResult = $rabbitMQConnection->publish($postData, env("RABBIT_MPESA_QUEUE"), '');

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

	public function mpesa_payment(Request $request){

		$user_id = "";
		
		Log::info("Callback URL from Inbox Consumer called [MPESA Payments] "
			. " ==> ". var_export($request->all(), 1) );

		if( !empty($request->all())){

			$transaction_type = $request->TransactionType;
			$transaction_id = $request->TransID;
			$transaction_time = $request->TransTime;
			$transaction_amount = $request->TransAmount;
			$business_code = $request->BusinessShortCode;
			$bill_ref_no = $request->BillRefNumber;
			$invoice_number = $request->InvoiceNumber;
			$org_account_balance = $request->OrgAccountBalance;
			$third_party_trans_id = $request->ThirdPartyTransID;
			$msisdn = $request->MSISDN;
			$first_name = $request->FirstName;
			$middle_name = $request->MiddleName;
			$last_name = $request->LastName;

			$name = $first_name. " ".$middle_name." ".$last_name;

			Log::info("Now preparing the query to insert the MPESA Transaction");

			//Run this in transaction :P
			$reuslt = DB::transaction(function(){

                DB::insert("insert into mpesa_transactions (message,transaction_ref,transaction_time,
					amount,paybill_no,mpesa_code,bill_ref_no,account_no,msisdn,names,status_id) 
					VALUES(:message,:transaction_ref,:transaction_time,:amount,:paybill_no,
					:mpesa_code,:bill_ref_no,:account_no,:msisdn,:names,:trx_status)",
					[
						'message'=>$message, 
						'transaction_ref'=>$transaction_ref, 
						'transaction_time'=>$transaction_time,
						'amount'=>$amount,
						'paybill_no'=>$paybill_no,
						'mpesa_code'=>$mpesa_code,
						'bill_ref_no'=>$bill_ref_no,
						'account_no'=>$bill_ref_no,
						'msisdn'=>$msisdn,
						'names'=>$name,
						'trx_status':DBStatus::COMPLETE
					]
				);
				$user = DB::select(
					DB::raw("select u.id, if(ub.balance is null, 0, ub.balance) as balance "
						. " from users u left join user_balance ub u.id =ub.user_id  "
						. " where phone_no='".$msisdn."'"));
				$running_balance = 0;
				if(!empty($user)){
						$user_id = $user[0]->id;
						$running_balance = $user[0]->balance
				}else{
					$user_id = DB::table('users')->insertGetId(
						["name"=>$name, 
						"user_group"=>4,
						"phone_no"=>$msisdn,
						"email"=>$msisdn."@urbantap.co.ke",
						"password"=>Hash::make($msisdn)]
					);
				}
				$balance = $running_balance+$transaction_amount;

				$transaction = new Transaction();
				$transaction->user_id=$user_id;
				$transaction->transaction_type="CREDIT";
				$transaction->reference=$transaction_id;
				$transaction->amount=$transaction_amount;
				$transaction->running_balance=$balance;
				$transaction->status_id= DBstatus::COMPLETE;

				$transaction->save();

				DB::insert("insert into user_balance set user_id='".$user_id."', balance='".$balance."',"
					. " transaction_id='".$transaction->id."',created=now() on duplicate key "
					. " update balance = balance + $balance "
				);

				$booking_amount = 0;
				$booking_reference = "";
				$balance = 0;
				$booking_time = "";

				$bookingRs = DB::select(
					DB::raw("select * from bookings where id='".$invoice_number."'")
				);

				if(count($bookingRs) > 0){

					$booking_amount = $bookingRs[0]->amount;
					$balance = $booking_amount - $transaction_amount;
					$booking_time = $bookingRs[0]->booking_time; 

					$serviceProvider = ServiceProvider::find($bookingRs[0]->service_provider_id);

					Log::info("Service Provider ID is ".$bookingRs[0]->service_provider_id);
					Log::info("User ID  for the Provider is ".$serviceProvider->user_id);

					$providerMsisdn = User::find($serviceProvider->user_id)->phone_no;
				}else{

					Log::info("Booking called back by MPESA Number $invoice_number NOT FOUND");

					$out = [
						'status' => 202,
						'success' => false,
						'message' => 'Booking Not Found'
					];

					return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
				}

				DB::insert("insert into payments (reference='".$transaction_id."', date_received=now(),
					booking_id='".$booking_id."', payment_method='MPESA', paid_by_name='".$name."',
					paid_by_msisdn='".$msisdn."', amount='".$booking_amount."', 
					received_payment='".$transaction_amount."', balance='".$balance."',
					status_id='".DBStatus::COMPLETE."', created_at=now())");

				DB::insert("insert into booking_trails (booking_id='".$invoice_number."', 
					    status_id='".DBStatus::BOOKING_PAID."', 
					    description='MPESA TRANSACTION', originator='MPESA', created_at=now())");

	            DB::update("update bookings set status_id = '".DBStatus::BOOKING_PAID."', updated_at = now()
				 where id = '".$invoice_number."'");

	        });

            $customerMessage = "";
            $serviceProviderMessage = "";

            $smsReference = $invoice_number;
            $customerMsisdn = $msisdn;

            $halfAmount = ceil($booking_amount/2);

            $sms = new SMS();

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
                'status' => 201,
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
