<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    public function get_permission()
    {
        return $this->belongsTo('App\Permission', 'permission');
    }
}
