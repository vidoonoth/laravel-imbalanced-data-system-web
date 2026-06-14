<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = env('ML_API_KEY');

        if (!$apiKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'API key is not configured on the server.'
            ], 500);
        }

        $token = $request->bearerToken();

        if (!$token || $token !== $apiKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Invalid or missing API key.'
            ], 401);
        }

        return $next($request);
    }
}
