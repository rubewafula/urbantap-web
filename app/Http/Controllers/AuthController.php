<?php

namespace App\Http\Controllers;

use App\Events\PasswordResetEvent;
use App\Events\UserRegistered;
use App\Http\Requests\ForgotPasswordRequest;
use App\Outbox;
use App\ServiceProvider;
use App\User;
use App\UserPersonalDetail;
use App\Utilities\DBStatus;
use App\Utilities\HTTPCodes;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

/**
 * Class AuthController
 * @package App\Http\Controllers
 */
class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string',
            'username' => [function ($attribute, $value, $fail) {
                //valid phone
                $valid_phone = preg_match("/^(?:\+?254|0)?(7\d{8})/", $value, $p_matches);
                //Valid email
                $valid_email = preg_match("/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}/", $value, $e_matches);
                //preg_match() returns 1 if the pattern matches given subject, 0 if it does not, or FALSE if an error occurred. 
                if ($valid_phone != 1 && $valid_email != 1) {

                    $fail(':attribute should be valid email of phone number!');
                }

                $exists = $user = User::where('phone_no', $value)
                    ->orWhere('email', $value)->first();

                if ($exists) {
                    $fail(':attribute already taken, kindly use a different value!');
                }
            }],
            // 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => FALSE,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }


        $valid_phone = preg_match("/^(?:\+?254|0)?(7\d{8})/", $request->get('username'), $p_matches);

        $username = $valid_phone != 1 ? $request->get('username') : '254' . $p_matches[1];


        $email = $valid_phone != 1 ? $request->get('username') : null;
        $phone = $valid_phone == 1 ? '254' . $p_matches[1] : null;

        $code = $this->generate_code();
        $token_hash = substr(md5(uniqid(rand(), true)), 0, 128);

        $user = User::Create([
            'first_name'         => $request->get('name'),
            'email'              => $email,
            'phone_no'           => $phone,
            'password'           => bcrypt($request->get('password')),
            'verification_code'  => $code,
            'confirmation_token' => $token_hash,
            'verification_sends' => 1,
        ]);

        $user->save();

        // Fire event
        broadcast(new UserRegistered(array_merge(
            compact('token_hash', 'code'),
            [
                'message' => "Dear " . $request->get('name') . "," . PHP_EOL . " Use $code to verify your URBANTAP account. STOP *456*9*5#",
                'subject' => sprintf('Welcome to %s', config('app.name'))
            ]
        ), $user));

        $out = [
            'success'   => TRUE,
            'is_mobile' => !is_null($phone),
            'user_id'   => $user->id,
            'message'   => 'Registration successful'
        ];
        return Response::json($out, HTTPCodes::HTTP_CREATED);

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkLoginStatus()
    {

        $user = Auth::user();
        $results = [];
        if ($user == null) {
            $results = ["status" => 0];
        } else {
            $results = ["status" => 1, "user_id" => $user->id];
        }

        return Response::json($results, HTTPCodes::HTTP_OK);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function resend_verification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => [function ($attribute, $value, $fail) {
                //valid phone
                $valid_phone = preg_match("/^(?:\+?254|0)?(7\d{8})/", $value, $p_matches);
                //Valid email
                $valid_email = preg_match("/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}/", $value, $e_matches);
                //preg_match() returns 1 if the pattern matches given subject, 0 if it does not, or FALSE if an error occurred. 
                if ($valid_phone != 1 && $valid_email != 1) {

                    $fail(':attribute should be valid email of phone number!');
                }
            }],
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

        $valid_phone = preg_match("/^(?:\+?254|0)?(7\d{8})/", $request->get('username'), $p_matches);

        $username = $valid_phone != 1 ? $request->get('username') : '254' . $p_matches[1];

        $user = User::where('phone_no', $username)->orWhere('email', $username)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'success' => false,

            ], HTTPCodes::HTTP_NOT_FOUND);
        }


        $valid_phone = preg_match("/^(?:\+?254|0)?(7\d{8})/", $request->get('username'), $p_matches);

        $username = $valid_phone != 1 ? $request->get('username') : '254' . $p_matches[1];


        $email = $valid_phone != 1 ? $request->get('username') : null;
        $phone = $valid_phone == 1 ? '254' . $p_matches[1] : null;


        $sms_message = "Dear " . $user->first_name . "," . PHP_EOL . " Use " . $user->verification_code . " to verify your URBANTAP account. STOP *456*9*5#";

        $base_url = env('APP_URL', 'http:127.0.0.1:8000/');

        $email_message = "Dear " . $user->first_name . "," . PHP_EOL . PHP_EOL
            . " Thank you for signing up with URBANTAP. From now on you will be able to order for our services on the fly. Feel free to peruse through the profiles on URBANTAP and identify the best service providers you can order from. " . PHP_EOL . PHP_EOL
            . " Click on the below link to get your account verified and start tapping to freedom " . PHP_EOL
            . $base_url . "auth/account/verify/" . $user->confirmation_token . " " . PHP_EOL . PHP_EOL
            . " Cheers " . PHP_EOL
            . " URBANTAP - Tap to Freedom ";

        $message = is_null($phone) ? $email_message : $sms_message;
        $recipients = is_null($phone) ? $email : $phone;

        //  Send  SMS  to verify  phone  number 
        $outbox = Outbox::Create([
            'user_id'   => $user->id,
            'msisdn'    => $phone,
            'email'     => $email,
            'network'   => is_null($phone) ? 'EMAIL' : 'SAFARICOM',
            'message'   => $message,
            'status_id' => DBStatus::SMS_NEW
        ]);

        //Send message over API
        $payload = array(
            'reference'  => $outbox->id,
            'message'    => $message,
            'recipients' => [$recipients]
        );
        $sms_url = env('SEND_SMS_URL', 'http://172.104.224.221:9173/api/sms/sendsms');
        $email_url = env('SEND_EMAIL_URL', 'http://172.104.224.221:9173/api/sms/sendemail');

        // Send Email/SMS via urbantap API
        $api_url = is_null($phone) ? $email_url : $sms_url;
        try {
            $client = new Client();

            $res = $client->request('POST', $api_url, [
                'form_params' => $payload
            ]);

            if ($res->getStatusCode() == 200) { // 200 OK
                $response_data = $res->getBody()->getContents();
            }
        } catch (Exception $ex) {
            //Do nothing until titus bring it on

        }

        $out = [
            'success'   => TRUE,
            'is_mobile' => !is_null($phone),
            'user_id'   => $user->id,
            'message'   => 'Verification resend success'
        ];
        return Response::json($out, HTTPCodes::HTTP_CREATED);

    }


    /**
     * @param null $hash
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify_code($hash = null, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verification_code' => 'string|min:4|max:12',
            'username'          => [function ($attribute, $value, $fail) {
                //valid phone
                $valid_phone = preg_match("/^(?:\+?254|0)?(7\d{8})/", $value, $p_matches);
                //Valid email
                $valid_email = preg_match("/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}/", $value, $e_matches);
                //preg_match() returns 1 if the pattern matches given subject, 0 if it does not, or FALSE if an error occurred. 
                if ($valid_phone != 1 && $valid_email != 1) {

                    $fail(':attribute should be valid email of phone number!');
                }
            }],
        ]);


        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

        $valid_phone = preg_match("/^(?:\+?254|0)?(7\d{8})/", $request->get('username'), $p_matches);
        $phone = -1;
        if ($valid_phone == 1) {
            $phone = '254' . $p_matches[1];
        }


        $user = User::where('phone_no', $phone)
            ->orWhere('email', $request->get('username'))->first();


        if (!empty($user) && ($user->verification_code == $request->get('verification_code') ||
                $user->confirmation_token == $hash)) {
            $user->phone_verified = 1;
            $user->status_id = DBStatus::USER_ACTIVE;
            #$user->verification_code= NULL;
            $user->save();

            $user->details = UserPersonalDetail::where('user_id', $user->id)->first();

            if ($user->details && $user->details->passport_photo == null) {
                $user->details->passport_photo =
                    [
                        'media_type' => 'image',
                        'media_url'  => env('API_URL', 'http://127.0.0.1:8000') . '/static/images/avatar/default-avatar.jpg'
                    ];
            }

            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->token;
            $token->save();

            $out = [
                'success'      => TRUE,
                'access_token' => $tokenResult->accessToken,
                'token_type'   => 'Bearer',
                'expires_at'   => Carbon::parse(
                    $tokenResult->token->expires_at
                )->toDateTimeString(),
                'user'         => $user
            ];

            return Response::json($out, HTTPCodes::HTTP_OK);

        } else {

            $out = [
                'success' => FALSE,
                'message' => 'Verification failed, please check supplied hash/code'
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }


    }


    /**
     * @return int
     */
    public function generate_code()
    {
        $number = rand(1000, 9999);

        if (User::where('verification_code', $number)->exists()) {
            while (User::where('verification_code', $number)->exists() == TRUE) {
                $number = rand(1000, 9999);
            }
            return $number;
        } else {
            return $number;
        }

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = $request->validate([
            'username'    => [function ($attribute, $value, $fail) {
                //valid phone
                $valid_phone = preg_match("/^(?:\+?254|0)?(7\d{8})/", $value, $p_matches);
                //Valid email
                $valid_email = preg_match("/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}/", $value, $e_matches);
                //preg_match() returns 1 if the pattern matches given subject, 0 if it does not, or FALSE if an error occurred. 
                if ($valid_phone != 1 && $valid_email != 1) {

                    $fail(':attribute should be valid email of phone number!');
                }
            }],
            'password'    => 'required|string',
            'remember_me' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }


        $valid_phone = preg_match("/^(?:\+?254|0)?(7\d{8})/", $request->get('username'), $p_matches);

        if ($valid_phone != 1) {
            $credentials = ['email' => $request->get('username'), 'password' => $request->get('password')];

        } else {
            $phone = '254' . $p_matches[1];
            $credentials = ['phone_no' => $phone, 'password' => $request->get('password')];
        }

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials, please check your username and password'
            ], HTTPCodes::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $user->service_provider = ServiceProvider::where('user_id', $user->id)->first();

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->remember_me) {
            $token->expires_at = Carbon::now()->addWeeks(1);
        }
        $token->save();

        return response()->json([
            'success'      => true,
            'access_token' => $tokenResult->accessToken,
            'token_type'   => 'Bearer',
            'expires_at'   => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
            'user'         => $user,
            'user_details' => $user->details
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }


    public function reset_password(Request $request)
    {

        $validator = Validator::make($request->all(),
            [
                'verification_code' => 'required|integer',
                'password'          => 'required|string|min:6',
                'confirm_password'  => 'required|string|min:6',
            ]);

        if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

        $results = DB::select(
            DB::raw("select email from password_resets where token=:token order by created_at desc "),
            ['token' => $request->verification_code]);
        $email = "";
        if (!empty($results)) {
            $email = $results[0]->email;
        } else {
            return Response::json(
                [
                    'success' => false,
                    'message' => ['token' => 'Invalid verification code']
                ], HTTPCodes::HTTP_PRECONDITION_FAILED);

        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return Response::json(
                [
                    'success' => false,
                    'message' => ['token' => 'Invalid verification code']
                ], HTTPCodes::HTTP_PRECONDITION_FAILED);

        }

        $user->password = bcrypt($request->get('password'));
        $user->save();

        $out = [
            'success' => true,
            'message' => 'User password rest success'
        ];

        return Response::json($out, HTTPCodes::HTTP_OK);

    }


    /**
     * @param ForgotPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function forgot_password(ForgotPasswordRequest $request)
    {
        $token_hash = random_int(pow(10, 3), pow(10, 4) - 1);

        DB::table('password_resets')->insert(
            ['email' => $request->username, 'token' => $token_hash, 'created_at' => new Carbon()]
        );


        $user = User::where('email', $request->username)->orWhere('phone_no', $request->username)->first();

        if ($user) {
            $sms = "Use code %s to reset your password";
            broadcast(new PasswordResetEvent($user, [
                'username' => $request->username,
                'token'    => $token_hash,
                'message'  => sprintf($sms, $token_hash),
                'subject'  => 'Password reset request'
            ]));

            $out = [
                'success' => true,
                'message' => ['username' => 'User reset account notification send to ' . $request->username]
            ];

            return Response::json($out, HTTPCodes::HTTP_OK);
        } else {
            $out = [
                'success' => false,
                'message' => ['username' => "Username not found"]
            ];

            return Response::json($out, HTTPCodes::HTTP_BAD_REQUEST);
        }


    }
}
