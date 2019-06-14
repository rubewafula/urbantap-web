<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Booking
 * @package App
 */
class Booking extends Model
{

    /**
     * @var string
     */
    protected $table = "bookings";

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $appends = ["service"];

    /**
     * @var array
     */
    protected $fillable = [
        "provider_service_id",
        "service_provider_id",
        "user_id",
        "booking_time",
        "booking_duration",
        "expiry_time",
        "status_id",
        "booking_type",
        "amount",
        "created_at",
        "location"
    ];

    /**
     * @var array
     */
    protected $casts = [
        'location' => 'json'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function provider()
    {
        return $this->belongsTo('App\ServiceProvider', 'service_provider_id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function providerService()
    {
        return $this->belongsTo('App\ProviderServices');
    }


    /**
     * @return mixed
     */
    public function getServiceAttribute()
    {
        return $this->providerService->service()->first();
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo('App\Status');
    }

}
