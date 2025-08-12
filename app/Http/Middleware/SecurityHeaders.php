<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        // Strict Transport Security (only in production with HTTPS)
        if (app()->environment('production') && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Content Security Policy for auth pages
        if ($this->isAuthRoute($request)) {
            $csp = //"default-src 'self'; " .
                //    "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
                //    "style-src 'self' 'unsafe-inline'; " .
                //    "img-src 'self' data: https:; " .
                //    "font-src 'self' data:; " .
                //    "connect-src 'self'; " .
                   "frame-ancestors 'none'; " .
                   "base-uri 'self'; " .
                   "form-action 'self'";
            
            $response->headers->set('Content-Security-Policy', $csp);
        }

        return $response;
    }

    /**
     * Check if the current route is an authentication route.
     */
    private function isAuthRoute(Request $request): bool
    {
        $authRoutes = [
            'global.login',
            'global.register',
            'global.password.request',
            'global.password.reset',
            'login',
            'register',
            'password.request',
            'password.reset',
        ];

        $currentRoute = $request->route()?->getName();
        
        return in_array($currentRoute, $authRoutes) || 
               str_contains($request->path(), 'login') ||
               str_contains($request->path(), 'register') ||
               str_contains($request->path(), 'password');
    }
}