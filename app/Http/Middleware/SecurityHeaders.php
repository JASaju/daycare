<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->remove('X-Powered-By');

        // Logged-in pages (children, health, billing) must not be cached or shown via the Back button after logout.
        if ($request->user()) {
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Start in report-only mode; tighten once you've confirmed nothing breaks.
        // Add your CDN hosts (Bootstrap etc.) to script-src / style-src.
        $response->headers->set(
            'Content-Security-Policy-Report-Only',
            "default-src 'self'; img-src 'self' data: https://res.cloudinary.com; " .
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
            "frame-ancestors 'none'; base-uri 'self'; form-action 'self'"
        );

        return $response;
    }
}
