<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Symfony\Component\HttpFoundation\Response;

class AuthUser extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // check auth param
        $authKey = $request->header("auth");
        $validApiKey = "98934a45-430b-4077-adc2-a5b1764a1171";

        if ($validApiKey !== $authKey) {
            return response()->json(['error' => '未帶入 auth key'], 401);
        }

        return $next($request);
    }
}
