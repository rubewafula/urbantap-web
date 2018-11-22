<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProviderServices extends Model
{
    public function service() {
        return $this->belongsTo('App\Services');
    }

    public function serviceProvider() {
        return $this->belongsTo('App\ServiceProvider');
    }
    // override the toArray function (called by toJson)
    public function toArray() {
//        $data = parent::toArray();
        $data['id'] = $this->id;
        $data['service_provider_id'] = $this->service_provider_id;
        $data['service_id'] = $this->service_id;
        $data['service_name'] = $this->service->service_name;
        $data['description'] = $this->description;
        $data['cost'] = $this->cost;
        $data['duration'] = $this->duration;
        return $data;
    }
}
