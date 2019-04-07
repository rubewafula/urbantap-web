<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'transaction_type', 'reference', 'amount','running_balance','status_id'
    ];

    public function user() {
        return $this->belongsTo('App\User');
    }

}