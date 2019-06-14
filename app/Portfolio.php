<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    //
    protected $casts = [
    	'media_data' => 'json'
    ];

     public function getMediaDataAttribute($value){
        return Utils::PROVIDER_PORTFOLIOS_URL . array_get($value, 'media_url', '2.jpg');
    }

}
