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
        $notifications = $user->notifications()->latest()->paginate();
        return [
            'notifications' => $notifications,
            'unread' => $user->unreadNotifications()->count()
        ];
    }

    /**
     * @param Request $request
     */
    public function update(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => new Carbon()]);
    }
}
