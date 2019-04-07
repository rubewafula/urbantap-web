<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{

    protected $table = "bookings";

    protected $primaryKey = 'id';

    protected $fillable = ["provider_service_id", "service_provider_id", "user_id", 
    "booking_time", "booking_duration", "expiry_time", "status_id","booking_type",
     "amount","created_at", "location"];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function service_provider()
    {
        return $this->belongsTo('App\ServiceProvider');
    }

    public function status()
    {
        return $this->belongsTo('App\Status');
    }

}
