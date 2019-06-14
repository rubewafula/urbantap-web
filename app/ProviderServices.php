<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Utilities\Utils;

class ProviderServices extends Model
{

    protected $casts = [
        'media_url' => 'json'
    ];

    public function service() {
        return $this->belongsTo('App\Services');
    }

    public function serviceProvider() {
        return $this->belongsTo('App\ServiceProvider');
    }


    public function getMediaUrlAttribute($value){
        return Utils::SERVICE_IMAGE_URL . array_get($value, 'media_url', '2.jpg');
    }

}
