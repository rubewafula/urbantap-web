<?php

namespace App\Http\Controllers;

use App\User;
use App\UserGroup;
use App\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    protected $userGroup;
    protected $user;
    protected $userPermission;
    protected $random_pass;

    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->userGroup = new UserGroup();
        $this->user = new User();
        $userPermission = new UserPermission();
    }

    public function index()
    {

        $users = User::paginate(10);

        return view('users.index', ['users' => $users]);

    }

    function register_user(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'user_group_id' => 'required|max:10',
            'phone_no' => 'required',
            'email' => 'required|email|max:255|unique:users,email'
        ]);

        $this->random_pass = $this->randomPassword();
        $this->user->name = $request->name;
        $this->user->user_group = $request->user_group_id;
        $this->user->email = $request->email;
        $this->user->phone_no = $request->phone_no;
        $this->user->password = bcrypt($this->random_pass);


        DB::transaction(function () {
            if ($data = $this->user->saveOrFail()) {
                $this->user->raw_password = $this->random_pass;
                (new MailerController())->welcome_user($this->user);
                Session::flash("success", "User created Successfully!");

            }
        });

        return redirect('/users');
    }

    function randomPassword() {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }

    function profile($user_id)
    {
        $user = User::find($user_id);
        return view('users.profile')->with('user', $user);
    }

    public function update()
    {
        $request = (object)$_POST;
        $this->user = User::find($request->user_id);

        $this->user->name = $request->name;
        $this->user->email = $request->email;
        $this->user->user_group = $request->user_group;


        DB::transaction(function () {
            if ($data = $this->user->update()) {
                Session::flash("success", "Updated Successfully!");
            }
        });

        return redirect('/users/profile/' . $request->user_id);
    }

    function delete_user($user_id)
    {
        $this->user = User::find($user_id);
        DB::transaction(function () {
            if ($data = $this->user->delete()) {
                Session::flash("success", "Deleted Successfully!");

            }
        });
        return redirect('/users');
    }


}
