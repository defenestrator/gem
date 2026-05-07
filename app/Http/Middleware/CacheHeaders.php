<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET') && $response->isSuccessful()) {
            if (auth()->check()) {
                $response->headers->set('Cache-Control', 'private, no-store');
            } else {
                $response->headers->set('Cache-Control', 'public, max-age=300, s-maxage=300');
                $response->headers->set('Vary', 'Accept-Encoding');
            }
        }

        return $response;
    }
}
