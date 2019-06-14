<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceProvider extends Model
{

    protected $table = "service_providers";

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
}
