<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UrbantapTransaction extends Model
{

    protected $table = 'urbantap_transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'transaction_type', 'transaction_id','reference', 'amount','running_balance','status_id'
    ];

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function transaction() {
        return $this->belongsTo('App\Transaction');
    }

}