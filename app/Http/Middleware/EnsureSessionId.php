<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionId
{
    /**
     * Handle an incoming request.
     * Generates X-Session-ID if not provided and echoes it back in response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If no X-Session-ID provided, generate one
        if (!$request->header('X-Session-ID')) {
            $sessionId = Str::uuid()->toString();
            $request->headers->set('X-Session-ID', $sessionId);
        }

        $response = $next($request);

        // Echo the session ID back so frontend can persist it
        $response->headers->set('X-Session-ID', $request->header('X-Session-ID'));

        return $response;
    }
}
