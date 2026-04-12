<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $origin = $request->header('Origin');
        $domain = config('app.domain');

        if ($origin && preg_match('/^https?:\/\/[a-z0-9\-]+\.' . preg_quote($domain, '/') . '$/', $origin)) {
            $response->header("Access-Control-Allow-Origin", $origin);
        } else {
            $response->header("Access-Control-Allow-Origin", config('app.protocol') . '://' . $domain);
        }

        $response->header("Access-Control-Allow-Methods", "GET, PUT, POST, DELETE, OPTIONS");
        $response->header("Access-Control-Allow-Credentials", "true");
        $response->header("Access-Control-Allow-Headers", "Authorization, Content-Type, Accept");

        return $response;
    }
}
