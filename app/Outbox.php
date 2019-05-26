<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Outbox extends Model
{
    //

    protected  $table ='outboxes';

    protected  $primarykey='id';

    public $fillable=['user_id','msisdn','network', 'service_provider_id',
     'reference','link_id','status_id','message'];


    public  function  user(){

         return  $this->BelongsTo('App\User');

    }

    public  function  service_provider(){

         return  $this->BelongsTo('App\ServiceProvider');

    }


    public  function  status()
    {
        return $this->BelongsTo('App\Status');

    }

}