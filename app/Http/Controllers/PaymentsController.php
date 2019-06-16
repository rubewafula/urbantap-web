<?php

namespace App\Http\Controllers;

use App\Booking;
use App\Events\BookingPaid;
use App\ServiceProvider;
use App\Status;
use App\Transaction;
use App\CustomerTransaction;
use App\ProviderTransaction;
use App\UrbantapTransaction;
use App\User;
use App\Utilities\DBStatus;
use App\Utilities\HTTPCodes;
use App\Utilities\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Exception;

/**
 * Class PaymentsController
 * @package App\Http\Controllers
 */
class PaymentsController extends Controller
{
    /**
     * @var
     */
    private $rabbitMQConnection;
    /**
     * @var
     */
    private $connection;
    /**
     * @var
     */
    private $channel;

    /**
     * @param Request $request
     */
    public function baeKopokopo(Request $request)
    {

        Log::info("Called from the inbox consumer IP Address: ");
        $payload = ['msisdn' => $request->msisdn, 'business_number' => $request->business_number, 'amount' => $request->amount, 'reference' => $request->reference];
        Log::info(print_r($payload, 1));
        $data = json_encode($payload);

        $httpRequest = curl_init('http://139.162.142.202:9000/confirm');

        curl_setopt($httpRequest, CURLOPT_NOBODY, true);
        curl_setopt($httpRequest, CURLOPT_POST, true);
        curl_setopt($httpRequest, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
        curl_setopt($httpRequest, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($httpRequest, CURLOPT_POSTFIELDS, "$data");
        curl_setopt($httpRequest, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data)));
        curl_setopt($httpRequest, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)');

        $result = curl_exec($httpRequest);

        Log::info("Got results from BAE Payment");

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function MpesaPayment(Request $request)
    {

        $user_id = "";
        $email = null;
        $booking_balance = 0;
        $running_balance = 0;
        $provider_running_balance = 0;

        Log::info("Callback URL from Inbox Consumer called [MPESA Payments] "
            . " ==> " . var_export($request->all(), 1));

        if (!empty($request->all())) {

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

            $name = $first_name . " " . $middle_name . " " . $last_name;

            $transactionCheck = DB::select(
                DB::raw("select mpesa_code from mpesa_transactions 
             				where mpesa_code='" . $transaction_id . "'"));

            if (!empty($transactionCheck)) {

                throw new Exception("Duplicate Transaction reference");
            }

            Log::info("Now preparing the query to insert the MPESA Transaction");

            //Run this in transaction :P
            try {
                DB::beginTransaction();
                DB::insert("insert into mpesa_transactions (message,transaction_ref,transaction_time,
					amount,paybill_no,mpesa_code,bill_ref_no,account_no,msisdn,names,status_id) 
					VALUES(:message,:transaction_ref,:transaction_time,:amount,:paybill_no,
					:mpesa_code,:bill_ref_no,:account_no,:msisdn,:names,:trx_status)",
                    [
                        'message'          => "Mpesa deposit",
                        'transaction_ref'  => $invoice_number,
                        'transaction_time' => $transaction_time,
                        'amount'           => $transaction_amount,
                        'paybill_no'       => $business_code,
                        'mpesa_code'       => $transaction_id,
                        'bill_ref_no'      => $bill_ref_no,
                        'account_no'       => $bill_ref_no,
                        'msisdn'           => $msisdn,
                        'names'            => $name,
                        'trx_status'       => DBStatus::TRANSACTION_COMPLETE
                    ]
                );
                $user = DB::select(
                    DB::raw("select u.id, if(ub.available_balance is null, 0, ub.available_balance) as balance, email, phone_no, "
                        . " b.service_provider_id from users u inner join bookings b on u.id = b.user_id  "
                        . " left join user_balance ub on u.id =ub.user_id  "
                        . " where b.id = ?"), [$bill_ref_no]);
                
                $provider = null;

                if (!empty($user)) {
                    $user_id = $user[0]->id;
                    $running_balance = $user[0]->balance;
                    $email = $user[0]->email;
                    if ($user[0]->phone_no !== $msisdn) {
                        DB::table('users')->insert(
                            [
                                "first_name" => $name,
                                "user_group" => 4,
                                "phone_no"   => $msisdn,
                                "email"      => $msisdn . "@urbantap.co.ke",
                                "password"   => Hash::make($msisdn)
                            ]
                        );
                    }

                    $provider = DB::select(
                        DB::raw("select * from user_balance ub "
                            . " where ub.user_id = ?"), [$user[0]->service_provider_id]);

                    $provider_running_balance = $provider[0]->balance;

                } else {
                    // Log::error("Booking not found", $request->all());
                    // throw new Exception("Booking not found.");

                    $user = DB::select(
                        DB::raw("select * from users where phone_no = ?"), [$msisdn]);

                    if (!empty($user)) {

                        $user_id = $user[0]->id;
                    } else {

                        $user_id = DB::table('users')->insertGetId(
                            [
                                "first_name" => $name,
                                "user_group" => 4,
                                "phone_no"   => $msisdn,
                                "email"      => $msisdn . "@urbantap.co.ke",
                                "password"   => Hash::make($msisdn)
                            ]
                        );
                    }
                }

                $running_balance = $running_balance + $transaction_amount;

                $transaction = new Transaction();
                $transaction->user_id = $user_id;
                $transaction->transaction_type = "CREDIT";
                $transaction->reference = $transaction_id;
                $transaction->amount = $transaction_amount;
                $transaction->running_balance = $running_balance;
                $transaction->status_id = DBstatus::TRANSACTION_COMPLETE;

                $transaction->save();

                $customerTransaction = new CustomerTransaction();
                $customerTransaction->user_id = $user_id;
                $customerTransaction->transaction_type = "CREDIT";
                $customerTransaction->reference = $transaction_id;
                $customerTransaction->amount = $transaction_amount;
                $customerTransaction->transaction_id = $transaction->id;
                $customerTransaction->running_balance = $running_balance;
                $customerTransaction->status_id = DBstatus::TRANSACTION_COMPLETE;

                $customerTransaction->save();

                DB::insert("insert into user_balance set user_id='" . $user_id . "', 
                        balance='" . $transaction_amount . "', available_balance='" . $transaction_amount . "',"
                    . " transaction_id='" . $transaction->id . "',created=now() on duplicate key "
                    . " update available_balance = available_balance + $transaction_amount "
                );

                DB::commit();
            } catch (\Exception $exception) {
                Log::info("Error message", ['error' => $exception->getMessage()]);
                DB::rollBack();

                return Response::json([
                    'status'  => 500,
                    'success' => false,
                    'message' => 'Failed to process payment'
                ], HTTPCodes::HTTP_INTERNAL_SERVER_ERROR);
            }

            if(empty($user)){

                Log::info("Booking called back by MPESA Number $bill_ref_no NOT FOUND");

                $out = [
                    'status'  => 202,
                    'success' => false,
                    'message' => 'Booking Not Found'
                ];

                return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
            }
            
            try {
                DB::beginTransaction();

                $booking_amount = 0;
                $booking_reference = "";
                $booking_time = "";

                $bookingRs = DB::select(
                    DB::raw("select * from bookings where id='" . $bill_ref_no . "'")
                );

                if (count($bookingRs) > 0) {

                    $booking_amount = $bookingRs[0]->amount;
                    $booking_balance = $booking_amount - $transaction_amount;
                    $booking_time = $bookingRs[0]->booking_time;

                    $running_balance = $running_balance - $transaction_amount;
                    $provider_running_balance = $provider_running_balance + $transaction_amount;

                    $transaction = new Transaction();
                    $transaction->user_id = $user_id;
                    $transaction->transaction_type = "DEBIT";
                    $transaction->reference = $transaction_id;
                    $transaction->amount = $transaction_amount;
                    $transaction->running_balance = $running_balance;
                    $transaction->status_id = DBstatus::TRANSACTION_COMPLETE;

                    $transaction->save();

                    $providerTransaction = new ProviderTransaction();
                    $providerTransaction->user_id = $user_id;
                    $providerTransaction->transaction_type = "CREDIT";
                    $providerTransaction->reference = $transaction_id;
                    $providerTransaction->amount = $transaction_amount;
                    $providerTransaction->transaction_id = $transaction->id;
                    $providerTransaction->running_balance = $provider_running_balance;
                    $providerTransaction->status_id = DBstatus::TRANSACTION_COMPLETE;

                    $providerTransaction->save();

                    DB::insert("insert into user_balance set user_id='" . $bookingRs[0]->service_provider_id . "',
                         balance='" . $transaction_amount . "', available_balance='0',"
                        . " transaction_id='" . $transaction->id . "',created=now() on duplicate key "
                        . " update balance = balance + $transaction_amount "
                    );

                    DB::insert("insert into booking_trails set booking_id='" . $bill_ref_no . "', 
                        status_id='" . DBStatus::BOOKING_PAID . "',transaction_id = '".$transaction->id."',
                        description='MPESA TRANSACTION', originator='MPESA', created_at=now()");

                    DB::update("update bookings set status_id = '" . DBStatus::BOOKING_PAID . "', updated_at = now()
                     where id = '" . $bill_ref_no . "'");

                    DB::insert("insert into payments set reference='" . $transaction_id . "', date_received=now(),
                        booking_id='" . $bill_ref_no . "', payment_method='MPESA', paid_by_name='" . $name . "',
                        paid_by_msisdn='" . $msisdn . "', amount='" . $booking_amount . "', 
                        received_payment='" . $transaction_amount . "', balance='" . $booking_balance . "',
                        status_id='" . DBStatus::TRANSACTION_COMPLETE . "', created_at=now()");

                } else {

                    Log::info("Booking called back by MPESA Number $bill_ref_no NOT FOUND");

                    $out = [
                        'status'  => 202,
                        'success' => false,
                        'message' => 'Booking Not Found'
                    ];

                }

                DB::commit();
            } catch (\Exception $exception) {
                Log::info("Error message", ['error' => $exception->getMessage()]);
                DB::rollBack();

                return Response::json([
                    'status'  => 500,
                    'success' => false,
                    'message' => 'Failed to process payment'
                ], HTTPCodes::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Notify user / service provider
            $booking = Booking::with([
                'user',
                'provider',
                'service',
                'providerService'
            ])->find($bill_ref_no);
            broadcast(
                new BookingPaid($booking, [
                    'amount'          => $transaction_amount,
                    'ref'             => $transaction_id,
                    'booking_amount'  => $booking_amount,
                    'running_balance' => $running_balance,
                    'balance'         => $balance,
                ])
            );

            $out = [
                'status'  => 201,
                'success' => true,
                'message' => 'MPESA Payment Received Successfully'
            ];

            return Response::json($out, HTTPCodes::HTTP_ACCEPTED);

        }
    }

    public function stkPush(Request $request)
    {

        Log::info("Logging the request");
        Log::info($request->all());
        $booking_id = $request->booking_id;
        $amount = $request->amount;
        $msisdn = $request->msisdn;

        Log::info("Data as read from the request BOOKING ID " . $booking_id . " AMOUNT " . $amount . " MSISDN " . $msisdn);

        $url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $token = Utils::generateMPESAOAuthToken();

        Log::info("Generated access token " . $token);

        $timestamp = date("YmdHis");

        $apiPassword = Utils::mpesaGenerateSTKPassword($timestamp);

        Log::info("Generated Password " . $apiPassword);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $token)); //setting custom header


        $curl_post_data = array(
          
          'BusinessShortCode' => env("PAYBILL_NO"),
          'Password' => $apiPassword,
          'Timestamp' => $timestamp,
          'TransactionType' => 'CustomerPayBillOnline',
          'Amount' => $amount,
          'PartyA' => $msisdn,
          'PartyB' => env("PAYBILL_NO"),
          'PhoneNumber' => $msisdn,
          'CallBackURL' => 'https://urbantap.co.ke/mpesa/c2b/payment',
          'AccountReference' => $booking_id,
          'TransactionDesc' => 'Booking Payment at UrbanTap'
        );

        $data_string = json_encode($curl_post_data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

        $curl_response = curl_exec($curl);
        print_r($curl_response);

        echo $curl_response;
    }

}