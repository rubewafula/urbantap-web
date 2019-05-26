<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  App\Expert;
use  App\ServiceProvider;


class WafController extends Controller
{
    //

    public function  waf()
    {
    //	echo 'waf';

    	$experts= Expert::get();
    	$providers= Serviceprovider::get();

    	return  view('experts.expert',compact('experts','providers'));



    }


}
