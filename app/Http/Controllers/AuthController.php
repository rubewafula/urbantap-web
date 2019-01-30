<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\User;
use App\Notifications\SignupActivate;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'phone_no' => 'required',
            'password' => 'required|string|confirmed'
        ],[
            'email.unique' => 'This email is already registered, please log in to continue'
        ]);


        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'phone_no' => $request->phone_no,
            'password' => bcrypt($request->password),
            'verification_code'=> $this->generate_verification(),
            'verification_sends'=>1
        ]);
        $user->save();




        //$user->notify(new SignupActivate($user));

        return response()->json([
            'message' => 'Registration successful. Please login to continue'
        ], 201);
    }


        public  function  resend_verification($phone_no)
          {
          
              if(User::where('phone',$phone_no)->exists())
              {

                 $user = User::where('phone',$phone_no)->first();
                 if($user->verification_sends ==3)
                 {
                             return response()->json([
            'message' => 'Please  try  again  later'
        ], 200);
                 }else{

                    if($user->verification_code  !== NULL)
                    {
                        //  Resend verification code


                    }else{

                        // Send  a new verification  code 
                        $user->verification_code= $this->generate_verification();
                        $user->save();



                    }


                 }





              }else{

                    return response()->json([
            'message' => 'We do not  have  an account  that  corresponds  to that number'
        ], 200);
              }
        


          }


    public  function  verify_phone($phone)
    {




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
