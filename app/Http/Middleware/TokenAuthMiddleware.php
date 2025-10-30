<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $expected = (string) config('files.api_token', 'artificially-token');

        if (!is_string($token) || !hash_equals($expected, $token)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid or missing token.'
            ], 401);
        }

        return $next($request);
    }
}
