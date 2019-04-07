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
        return $this->BelongsTo('App\User');
    }

    public function toArray() {
//        $data = parent::toArray();
        $data['service_provider_id'] = $this->id;
        $data['service_provider_name'] = $this->service_provider_name;
        $data['display_images'] = $this->displayImages;
        $data['reviews'] = $this->totalReviews();
        $data['avg_rating'] = number_format($this->averageRating(), 2, '.', '');
        return $data;
    }
}
