<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    public function providerService() {
        return $this->belongsTo('App\ProviderServices', 'provider_services_id');
    }

    public function serviceProvider() {
        return $this->belongsTo('App\ServiceProvider');
    }

    public function customer() {
        return $this->belongsTo('App\User', 'customer_id');
    }

    // override the toArray function (called by toJson)
    public function toArray() {
        $data = parent::toArray();
        $data['service_provider_name'] = $this->serviceProvider->service_provider_name;
        $data['service'] = $this->providerService;
        return $data;
    }
}
