<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Utilities\HTTPCodes;
use App\Utilities\DBStatus;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\User;
use App\Notifications\SignupActivate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;    
use  App\Rules\UserTelephone;
use  App\Outbox;



class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'phone_no' => 'required|min:9',
            'password' => 'required|string|min:5',
            'role_id'=>'required|numeric',
            'phone_no'=> new UserTelephone
        ]);

         if ($validator->fails()) {
            $out = [
                'success' => FALSE,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }


        if($request->role_id == 8 || $request->role_id == 9)
        {
            // continue;

        }else{

            return Response::json([ 'success' => FALSE,'message'=>' The  Role of the  user  is not  allowed',HTTPCodes::HTTP_BAD_REQUEST]);
        } 
        
         preg_match("/^(?:\+?254|0)?(7\d{8})/", "254726986944", $matches);
         $phone= '254' . $matches[1];
  
          $user = User::Create([
            'first_name' => $request->first_name,
            'last_name'=>$request->last_name,
            'email' => $request->email,
            'phone_no' => $phone,
            'password' => bcrypt($request->password),
            'verification_code'=> $this->generate_verification(),
            'confirmation_token'=> $this->generate_verification(),
            'verification_sends'=>1,
            'status_id'=>DBStatus::USER_NEW

        ]);

        $user->save();

        //$user->save();

         $user->roles()->attach($request->role_id);

        //  Send  SMS  to verify  phone  number 

      Outbox::Create([
        'user_id'=>$user->id,
        'msisdn'=>$phone,
        'message'=>$user->verification_code,
        'status_id'=>DBStatus::SMS_NEW
      ]);

        //  Send  Email to verify  Email 

          $user->notify(new SignupActivate($user));

           $out = [
                'success' => TRUE,
              'message' => 'Registration successful. Please login to continue'

            ];
            return Response::json($out, HTTPCodes::HTTP_CREATED);

        // return response()->json([
        //     'message' => 'Registration successful. Please login to continue'
        // ], 201);
    }


        public  function  resend_verification(Request  $request)
          {

                    $validator = Validator::make($request->all(),[
            'phone_no' => 'required|min:9',
        ]);

         if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

             $phone_no= '254'.substr($request->phone_no,-9);


              if(User::where('phone_no',$phone_no)->exists())
              {



                 $user = User::where('phone_no',$phone_no)->first();

                                            //  dd($phone_no);

                 if($user->verification_sends ==3)
                 {
                             return response()->json([
            'message' => 'Please  try  again  later',
            'success' => false,

        ], 201);
                 }else{

                    if($user->verification_code  !== NULL)
                    {
                        //  Resend verification code
                        Outbox::Create([
                        'user_id'=>$user->id,
                        'msisdn'=>$phone_no,
                        'message'=>$user->verification_code,
                        'status_id'=>DBStatus::SMS_NEW
                        ]);

                           return response()->json([
            'message' => ' Successful',
            'success' => true,

        ], 201);

                    }else{
                        // Send  a new verification  code 
                        $user->verification_code= $this->generate_verification();
                        $user->save();

                              Outbox::Create([
                        'user_id'=>$user->id,
                        'msisdn'=>$phone_no,
                        'message'=>$user->verification_code,
                        'status_id'=>DBStatus::SMS_NEW
                        ]);
                              return response()->json([
            'message' => ' message resent',
            'success' => TRUE,

        ], 201);

       
                    }

                 }

              }else{

                    return response()->json([
            'message' => 'We do not  have  an account  that  corresponds  to that number',
            'success'=>false
        ], 201);
              }
        


          }


    public  function  verify_code(Request  $request)
    {
      $validator = Validator::make($request->all(),[
            'verification_code' => 'required|min:4|max:4',
        ]);

      if ($validator->fails()) {
            $out = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return Response::json($out, HTTPCodes::HTTP_PRECONDITION_FAILED);
        }

         $user= User::where('verification_code',$request->verification_code)->first();

         if(!empty($user))
         {
            $user->phone_verified=1;
            $user->status_id= DBStatus::USER_ACTIVE;
            $user->verification_code= NULL;
            $user->save();

              $out = [
                'success' => TRUE,
                'message' =>'Phone number  verified'
            ];
            return Response::json($out, HTTPCodes::HTTP_OK);

         } else{

             $out = [
                'success' => FALSE,
                'message' =>'invalid verification code'
            ];
            return Response::json($out, HTTPCodes::HTTP_NO_CONTENT);


         }

      // $results = DB::select( 
      //       DB::raw("SELECT  *  FROM  users where verification_code=".$request->verification_code." ") 
      //   );
      
      //   if(empty($results)){
      //           return Response::json(['message'=>'The verification code does not  exist','success'=>FALSE], HTTPCodes::HTTP_NO_CONTENT );
      //   }





        //return Response::json($results, HTTPCodes::HTTP_OK);
    
   


    }


    public  function  generate_verification()
    {
      $number =  rand(1000,9999);

        if(User::where('verification_code',$number)->exists())
        {
            while (User::where('verification_code',$number)->exists() == TRUE) {
                $number= rand(1000,9999);
            } 
          return  $number;
        }else{
         return  $number;
        }

    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);
        $credentials = request(['email', 'password']);
//        $credentials['active'] = 1;
//        $credentials['deleted_at'] = null;

        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Invalid credentials, please check your email and password'
            ], 401);
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
//        if ($request->remember_me)
//            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();
        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
            'user' => $request->user()
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

}