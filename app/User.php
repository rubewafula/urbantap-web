<?php

namespace App;

use Illuminate\Notifications\HasDatabaseNotifications;
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
    protected $table = 'users';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id', 'first_name', 'last_name', 'email', 'phone_no', 'password', 'verification_code', 'verification_sent', 'phone_verified', 'email_verified', 'status_id', 'confirmation_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function status()
    {
        return $this->BelongsTo('App\Status');
    }

    public function role()
    {
        return $this->belongsTo('App\UserGroup', 'user_group');
    }

    public function get_user_group()
    {
        return $this->belongsTo('App\UserGroup', 'user_group');
    }

    public function roles()
    {
        return $this->BelongsToMany('App\Role');
    }

    /**
     * Create a guest user for auth-ing presence channels
     *
     * @return \App\User
     * @throws \Exception
     */
    public static function makeGuestUser(): User
    {
        $id = random_int(1, 100);
        $static = new static(compact('id'));
        return $static;
    }

}
