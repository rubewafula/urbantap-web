<?php

namespace App\Http\Controllers;

use App\Events\BookingPaid;
use App\ServiceProvider;
use App\Transaction;
use App\User;
use App\Utilities\DBStatus;
use App\Utilities\HTTPCodes;
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
                        'trx_status'       => DBStatus::COMPLETE
                    ]
                );
                $user = DB::select(
                    DB::raw("select u.id, if(ub.balance is null, 0, ub.balance) as balance, email, phone_no "
                        . " from users u inner join bookings b on u.id = b.user_id  "
                        . " left join user_balance ub on u.id =ub.user_id  "
                        . " where b.id = ?"), [$bill_ref_no]);
                $email = null;
                if (!empty($user)) {
                    $user_id = $user[0]->id;
                    $running_balance = $user[0]->balance;
                    $email = $user[0]->email;
                    if ($user[0]->phone_no !== $msisdn) {
                        DB::table('users')->insert(
                            [
                                "name"       => $name,
                                "user_group" => 4,
                                "phone_no"   => $msisdn,
                                "email"      => $msisdn . "@urbantap.co.ke",
                                "password"   => Hash::make($msisdn)
                            ]
                        );
                    }
                } else {
                    Log::error("Booking not found", $request->all());
                    throw new Exception("Booking not found.");
                }
                $balance = $running_balance + $transaction_amount;

                $transaction = new Transaction();
                $transaction->user_id = $user_id;
                $transaction->transaction_type = "CREDIT";
                $transaction->reference = $transaction_id;
                $transaction->amount = $transaction_amount;
                $transaction->running_balance = $balance;
                $transaction->status_id = DBstatus::COMPLETE;

                $transaction->save();

                DB::insert("insert into user_balance set user_id='" . $user_id . "', balance='" . $balance . "',"
                    . " transaction_id='" . $transaction->id . "',created=now() on duplicate key "
                    . " update balance = balance + $balance "
                );

                $booking_amount = 0;
                $booking_reference = "";
                $balance = 0;
                $booking_time = "";

                $bookingRs = DB::select(
                    DB::raw("select * from bookings where id='" . $bill_ref_no . "'")
                );

                if (count($bookingRs) > 0) {
                    $booking_amount = $bookingRs[0]->amount;
                    $balance = $booking_amount - $transaction_amount;
                    $booking_time = $bookingRs[0]->booking_time;

                    $serviceProvider = ServiceProvider::find($bookingRs[0]->service_provider_id);

                    Log::info("Service Provider ID is " . $bookingRs[0]->service_provider_id);
                    Log::info("User ID  for the Provider is " . $serviceProvider->user_id);

                    $providerMsisdn = User::find($serviceProvider->user_id)->phone_no;
                } else {

                    Log::info("Booking called back by MPESA Number $invoice_number NOT FOUND");

                    $out = [
                        'status'  => 202,
                        'success' => false,
                        'message' => 'Booking Not Found'
                    ];

                    return Response::json($out, HTTPCodes::HTTP_ACCEPTED);
                }

                DB::insert("insert into payments set reference='" . $transaction_id . "', date_received=now(),
					booking_id='" . $bill_ref_no . "', payment_method='MPESA', paid_by_name='" . $name . "',
					paid_by_msisdn='" . $msisdn . "', amount='" . $booking_amount . "', 
					received_payment='" . $transaction_amount . "', balance='" . $balance . "',
					status_id='" . DBStatus::COMPLETE . "', created_at=now()");

                DB::insert("insert into booking_trails set booking_id='" . $bill_ref_no . "', 
					    status_id='" . DBStatus::BOOKING_PAID . "', 
					    description='MPESA TRANSACTION', originator='MPESA', created_at=now()");

                DB::update("update bookings set status_id = '" . DBStatus::BOOKING_PAID . "', updated_at = now()
				 where id = '" . $bill_ref_no . "'");
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
            broadcast(
                new BookingPaid(
                    [
                        'booking_id'      => $bill_ref_no,
                        'amount'          => $transaction_amount,
                        'running_balance' => $running_balance,
                        'balance'         => $balance,
                        'name'            => $name,
                        'first_name'      => $first_name,
                        'booking_amount'  => $booking_amount,
                        'transaction_id'  => $transaction_id,
                        'booking_time'    => $booking_time
                    ],
                    new User(
                        array_merge(
                            [
                                'id'       => $user_id,
                                'email'    => $email,
                                'phone_no' => $msisdn,
                            ],
                            compact('first_name', 'last_name', 'middle_name')
                        )
                    )
                )
            );

            return Response::json([
                'status'  => 201,
                'success' => true,
                'message' => 'MPESA Payment Received Successfully'
            ], HTTPCodes::HTTP_ACCEPTED);

        }

    }

}