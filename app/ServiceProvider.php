<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Utilities\Utils;

class ServiceProvider extends Model
{

    protected $table = "service_providers";

    protected $appends = [
        'service_provider_id',
    ];

    protected $casts = [
        'cover_photo' => 'json'
    ];

    public function getServiceProviderIdAttribute(){
        return $this->id;
    }

    public function displayImages() {
        return $this->hasMany('App\ServiceProviderImages');
    }

    public function totalReviews() {
        return $this->hasMany('App\Review')->count();
    }

    public function averageRating() {
        return $this->hasMany('App\Review')->avg('stars');
    }

    public function type()
    {
        return $this->type == 1 ? "Expert" : "Business";
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function getCoverPhotoAttribute($value){
        return Utils::PROVIDER_SERVICES_URL . array_get($value, 'media_url', '2.jpg');
    }



}
