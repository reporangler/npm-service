<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->header("Access-Control-Allow-Origin", '*.'.config('app.domain'));
        $response->header("Access-Control-Allow-Methods", "GET, PUT, POST, DELETE, OPTIONS");
        $response->header("Access-Control-Allow-Credentials", "true");
        $response->header("Access-Control-Allow-Headers", "*");

        return $response;
    }
}
