<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;


class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'phone_no', 'password','user_group'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function role()
    {
        return $this->belongsTo('App\UserGroup', 'user_group');
    }

    public function get_user_group()
    {
        return $this->belongsTo('App\UserGroup', 'user_group');
    }

    public function  roles()
    {

        return  $this->belongsToMany('App\User');
    }

}
