<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\BookingStatusChanged' => [
            'App\Listeners\BookingStatusChangedListener',
        ],
        'App\Events\BookingCreated'       => [
            'App\Listeners\BookingCreatedListener',
        ],
        'App\Events\BookingPaid'          => [
            'App\Listeners\BookingPaidListener',
        ],
        'App\Events\UserRegistered'          => [
            'App\Listeners\UserRegisteredListener',
        ],
         'App\Events\PasswordResetEvent'          => [
            'App\Listeners\PasswordResetListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
