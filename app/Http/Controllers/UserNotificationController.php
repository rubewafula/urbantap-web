<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = User::query()->findOrFail($request->get('user_id'), ['id']);
        return $user->notifications()->latest()->paginate();
    }
}
