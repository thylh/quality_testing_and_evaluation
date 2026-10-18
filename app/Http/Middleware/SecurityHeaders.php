<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $styleNonce = base64_encode(random_bytes(16));
        $request->attributes->set('cspStyleNonce', $styleNonce);

        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
        }

        /** @var Response $response */
        $response = $next($request);

        $response->headers->remove('X-Powered-By');
        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "img-src 'self' data: https://laravel.com",
            "font-src 'self' data:",
            "style-src 'self' 'nonce-{$styleNonce}'",
            "script-src 'self'",
            "connect-src 'self'",
        ]));
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }
}
