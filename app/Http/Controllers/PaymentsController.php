<?php

namespace App\Http\Controllers;

use App\Booking;
use App\Events\BookingPaid;
use App\Events\BookingNotFoundEvent;
use App\MpesaTransaction;
use App\ServiceProvider;
use App\Status;
use App\Transaction;
use App\User;
use App\UserBalance;
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

            $booking = Booking::with([
                'user',
                'provider',
                'providerService'
            ])->find($bill_ref_no);
            $cost = null;
            $user_id = null;
            $bookingBalance = null;
            $debitAmount = 0;

            Log::info("Now preparing the query to insert the MPESA Transaction");

            //Run this in transaction :P
            try {
                DB::beginTransaction();

                $mpesaTransactionData = [
                    'message'          => "Mpesa deposit",
                    'msisdn'           => $msisdn,
                    'transaction_time' => $transaction_time,
                    'account_no'       => $bill_ref_no,
                    'mpesa_code'       => $transaction_id,
                    'amount'           => $transaction_amount,
                    'names'            => $name,
                    'paybill_no'       => $business_code,
                    'bill_ref_no'      => $bill_ref_no,
                    'transaction_ref'  => $invoice_number,
                    'status_id'        => DBStatus::TRANSACTION_SUSPENDED,
                ];
                if (!$booking) {
                    Log::error("Booking not found", $request->all());

                    $user_id = optional(User::where('phone_no', $msisdn)->first(['id']))->id;
                    $bookingBalance = -$transaction_amount;

                    if (!$user_id) {
                        MpesaTransaction::create($mpesaTransactionData);

                        DB::commit();

                        return Response::json([
                            'status'  => 202,
                            'success' => false,
                            'message' => 'User Not Found'
                        ], HTTPCodes::HTTP_ACCEPTED);
                    }
                }
                $user_id = $user_id ?: $booking->user_id;
                $userBalance = UserBalance::firstOrNew(compact('user_id'));

                MpesaTransaction::create(
                    array_merge($mpesaTransactionData, ['status_id' => DBStatus::TRANSACTION_COMPLETE, 'user_id' => $user_id])
                );
                $data = [
                    [
                        'user_id'          => $user_id,
                        'transaction_type' => 'CREDIT',
                        'reference'        => $transaction_id,
                        'amount'           => $transaction_amount,
                        'running_balance'  => $userBalance->balance + $transaction_amount,
                        'status_id'        => DBStatus::TRANSACTION_COMPLETE
                    ],
                ];

                if ($booking) {
                    $cost = $booking->balance ?? $booking->amount;
                    $bookingBalance = $cost - $transaction_amount;
                    array_push($data,
                        [
                            'user_id'          => $user_id,
                            'transaction_type' => 'DEBIT',
                            'reference'        => $transaction_id,
                            'amount'           => $debitAmount = ($bookingBalance <= 0 ? $cost : $transaction_amount),
                            'running_balance'  => ($userBalance->balance + $transaction_amount) - $debitAmount,
                            'status_id'        => DBStatus::TRANSACTION_COMPLETE
                        ]
                    );

                    $booking->fill([
                        'status_id' => $bookingBalance > 0 ? DBStatus::BOOKING_PARTIALLY_PAID : DBStatus::BOOKING_PAID,
                        'balance'   => $bookingBalance <= 0 ? 0 : $bookingBalance
                    ])->save();
                }

                Transaction::insert($data);

                if ($bookingBalance < 0) {
                    $bookingBalance = abs($bookingBalance);
                    DB::insert("insert into user_balance set user_id= :user_id, balance=:balance, available_balance= :available,created=now() on duplicate key "
                        . " update available_balance = available_balance + $bookingBalance, "
                        . " balance = balance + $bookingBalance",
                        [
                            'user_id' => $user_id,
                            'balance' => $bookingBalance,
                            'available' => $bookingBalance,
                        ]
                    );
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

            $data = [
                'amount'         => $transaction_amount,
                'ref'            => $transaction_id,
                'booking_amount' => $cost,
                'msisdn'         => $msisdn,
                'name'           => $name,
                'debit_amount'   => $debitAmount
            ];
            if ($booking)
                broadcast(
                    new BookingPaid($booking, $data)
                );
            else
                broadcast(
                    new BookingNotFoundEvent(new User(['id' => $user_id, 'phone_no' => $msisdn]), $data)
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
            'Password'          => $apiPassword,
            'Timestamp'         => $timestamp,
            'TransactionType'   => 'CustomerPayBillOnline',
            'Amount'            => $amount,
            'PartyA'            => $msisdn,
            'PartyB'            => env("PAYBILL_NO"),
            'PhoneNumber'       => $msisdn,
            'CallBackURL'       => 'https://urbantap.co.ke/mpesa/c2b/payment',
            'AccountReference'  => $booking_id,
            'TransactionDesc'   => 'Booking Payment at UrbanTap'
        );

        $data_string = json_encode($curl_post_data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

        $curl_response = curl_exec($curl);
        //print_r($curl_response);

        //echo $curl_response;

        $responseArray = json_decode($curl_response, true);
        $status = 200;
        $success = true;
        $message = "STK Request Success"
        $httpCode = HTTPCodes::HTTP_OK;

        if(array_key_exists("errorCode", $responseArray)){

            $status = 400;
            $success = false;
            $message = $responseArray["errorMessage"];
            $httpCode = HTTPCodes::HTTP_BAD_REQUEST;
        }

        $out = [
                'status'  => $status,
                'success' => $success,
                'message' => $message
            ];

        return Response::json($out, $httpCode);
    }

}