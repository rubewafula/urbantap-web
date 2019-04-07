<?php

namespace App\Payments;

use Illuminate\Database\Eloquent\Model;

class MPESATransaction extends Model
{
    
    protected $primaryKey = 'id'; 
    
    protected $table = 'mpesa_transactions';
    
    protected $fillable = ['user_id', 'msisdn', 'transaction_time',
     'amount', 'message', 'account_no', 'mpesa_code', 'names',
      'paybill_no', 'status_id'];
    
}