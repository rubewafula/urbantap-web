<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed serviceProvider
 */
class Expert extends Model
{
    public function serviceProvider() {
        return $this->belongsTo('App\ServiceProvider');
    }

    public function details() {
        $data['service_provider_name'] = optional($this->serviceProvider)->service_provider_name;
        $data['description'] = $this->business_description;
        $data['work_location'] = $this->work_location;
        $data['work_lat'] = $this->work_lat;
        $data['work_lng'] = $this->work_lng;
        $data['work_phone_no'] = $this->work_phone_no;
//        $data['facebook'] = $this->facebook;
//        $data['instagram'] = $this->instagram;
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
