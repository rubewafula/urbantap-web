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
        $user = $request->user();
        $resp = $user->notifications()->latest()->paginate();
        $resp->meta([
            'unread' => $user->unreadNotifications()->count()
        ]);
        return $resp;
    }

    /**
     * @param Request $request
     */
    public function update(Request $request)
    {
        $request->user()->unreadNotifications()->updated(['read_at' => new Carbon()]);
    }
}
