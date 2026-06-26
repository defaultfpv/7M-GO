<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AppStaticToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        $staticToken = env('APP_STATIC_TOKEN');

        if ($token !== $staticToken) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}

