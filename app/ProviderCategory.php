<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProviderCategory extends Model
{
    public function serviceProvider() {
        return $this->belongsTo('App\ServiceProvider');
    }

    public function category() {
        return $this->belongsTo('App\Category');
    }

    public function businessDetails() {
        return Business::where('service_provider_id',$this->serviceProvider->id)->first();
    }
    public function expertDetails() {
        return Expert::where('service_provider_id',$this->serviceProvider->id)->first();
    }

    // override the toArray function (called by toJson)
    public function toArray() {
        //$data = parent::toArray();
        $data['service_provider_id'] = optional($this->serviceProvider)->id;
        $data['service_provider_name'] = optional($this->serviceProvider)->service_provider_name;
        $data['display_images'] = optional($this->serviceProvider)->displayImages;
        $data['average_rating'] = is_null(optional($this->serviceProvider)->averageRating()) ? "0" : optional($this->serviceProvider)->averageRating();
        $data['total_reviews'] = optional($this->serviceProvider)->totalReviews();
        $data['type'] = optional($this->serviceProvider)->type == 1 ? 'business' : 'expert' ;
        $data['description'] = $this->serviceProvider->type == 1 ? optional($this->businessDetails())->description : optional($this->expertDetails())->business_description ;
        return $data;
    }
}
