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
        '/mpesa/c2b/confirm',
        '/mpesa/c2b/process',
        '/mpesa/c2b/payment',
        '/api/sms/sendsms',
        '/booking/checkstatus',
	    '/user/checklogin',
        '/api/*',
        '/broadcasting/auth'
    ];
}
