<?php

namespace App\Payments;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    
    protected $primaryKey = 'id'; 
    
    protected $table = 'transactions';
   
    protected $fillable = ['user_id', 'transaction_type', 'reference',
     'amount', 'running_balance', 'status_id'];
    
}