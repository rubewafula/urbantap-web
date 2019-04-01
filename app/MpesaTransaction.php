<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MpesaTransaction extends Model
{


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'msisdn', 'transaction_time', 'message','account_no','mpesa_code',
        'amount','names','paybill_no','status_id','transaction_ref','bill_ref_no'
    ];

    public function user() {
        return $this->belongsTo('App\User');
    }

}