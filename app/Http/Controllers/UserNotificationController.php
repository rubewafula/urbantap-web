<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
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

    /**
     * @param Request $request
     */
    public function update(Request $request)
    {
        $request->user()->unreadNotifications()->updated(['read_at' => new Carbon()]);
    }
}
