<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        switch (Auth::user()->user_group) {
            case 1:
                //super admin
                return view('dashboard');
                break;
            case 2:
                //admin
                return view('dashboard');
                break;
            case 3:
                //organisation manager,
                return view('dashboard');
                break;
            default:
                //neither
                return view('errors/404');
        }


    }

}
