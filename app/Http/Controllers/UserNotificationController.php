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
        // FIXME: This should fetch the logged in user's notifications
        $user = $request->user();

        return $user->notifications()->latest()->paginate();
    }
}
