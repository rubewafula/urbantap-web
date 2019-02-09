<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  App\Balance;
use Illuminate\Support\Facades\Validator;


class BalancesController extends Controller
{
    //

    public  function  get_all()
    {

       $balances= Balance::get();

       return $balances;

    }


    public  function  store(Request $request)
    {
    

    	$validator= Validator::make($request->all(),[
    		'user_id'=>'required',
    		'balance'=>'required',
    		'bonus'=>'required'
    	]);

    		if($validator ->fails()){

    		$out =[

           'sucess'=> false, 
           'message'=> $validator->messages()

            ];

            return $out;	

    		}



       Balance::create([
       	'user_id'=>$request->user_id,
       	'balance'=>$request->balance,
       	'bonus'=>$request->bonus
       ]);

       return  ['success'=>TRUE];




    }


}
