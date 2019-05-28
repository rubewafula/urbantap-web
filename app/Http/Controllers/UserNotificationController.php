<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

/**
 * Class UserNotificationController
 * @package App\Http\Controllers
 */
class UserNotificationController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        return $request->user()->notifications()->latest()->paginate();
    }
}
