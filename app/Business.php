<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed serviceProvider
 */
class Business extends Model
{

    public function serviceProvider() {
        return $this->belongsTo('App\ServiceProvider');
    }
    
    public function details() {
        $data['business_name'] = $this->business_name;
        $data['service_provider_name'] = $this->serviceProvider->service_provider_name;
        $data['description'] = $this->description;
        $data['location'] = $this->location;
        $data['lat'] = $this->lat;
        $data['lng'] = $this->lng;
        $data['phone_no'] = $this->phone_no;
        $data['facebook'] = $this->facebook;
        $data['instagram'] = $this->instagram;
        $data['reviews'] = $this->serviceProvider->totalReviews();
        $data['avg_rating'] = number_format($this->serviceProvider->averageRating(), 2, '.', '');
        return $data;
    }


    // override the toArray function (called by toJson)
    public function toArray() {
        $data = parent::toArray();
        $data['service_provider'] = $this->serviceProvider;
        return $data;
    }
}
