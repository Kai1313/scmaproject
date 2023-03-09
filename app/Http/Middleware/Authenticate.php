<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */

    public function handle($request, Closure $next, ...$guards)
    {
        if (Auth::guard('api')->check() && Auth::guard('api')) {
            return $next($request);
        } else {
            return response()->json([
                "result" => false,
                "code" => 401,
                "message" => "Error, User has no Authorization"
            ]);
        }
    }

    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}
