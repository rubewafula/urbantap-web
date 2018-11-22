<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        $role = explode(',', $role);
        return $request->user()->role->has_perm($role)
            ? $next($request)
            :  new Response(view('errors.404'));
    }
}
