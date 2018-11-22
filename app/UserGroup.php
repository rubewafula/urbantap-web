<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserGroup extends Model
{
    public function getUsers()
    {
        return $this->hasMany('App\User', 'user_group');
    }

    public function permissions()
    {
        return $this->belongsToMany('App\Permission');
    }

    public function has_perm(Array $permissions_array, $role_id = false)
    {
        if (!$role_id)
            $role_id = Auth::user()->user_group;
        if ($role_id == 1)
            return true;
        $available = DB::table('user_permissions')
            ->select('id')
            ->where('user_group', '=', $role_id)
            ->whereIn('permission', $permissions_array)
            ->get();

        return count($available) > 0;
    }

    public function has_perm_users(Array $permissions_array)
    {
        $users = DB::table('user_permissions')
            ->join('user_groups', 'user_groups.id', '=', 'user_permissions.user_group')
            ->join('users', 'users.user_group', '=', 'user_groups.id')
            ->select('users.*')
            ->whereIn('user_permissions.permission', $permissions_array)
            ->get();
        return $users;
    }
}
