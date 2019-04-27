<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
<<<<<<< HEAD
        '/mpesa/c2b/confirm',
        '/mpesa/c2b/process',
=======
        '/mpesa/c2b/payment',
        '/mpesa/c2b/process',
        '/api/sms/sendsms',
        '/booking/checkstatus',
>>>>>>> 03433bf731d929cf2f026e10ce019f2c184c5cb8
        '/api/*'
    ];
}
