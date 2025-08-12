<?php

namespace App\Http\Middleware;

use App\Services\SessionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionActivityMiddleware
{
    public function __construct(
        private SessionService $sessionService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only process for authenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        // Skip session expiration check if no last_activity is set (fresh login)
        if (!$request->session()->has('last_activity')) {
            $this->sessionService->updateActivity($request);
            return $next($request);
        }

        // Check if session has expired
        if ($this->sessionService->isSessionExpired($request)) {
            $this->sessionService->handleExpiredSession($request);
            
            return redirect()->route('global.login')
                ->with('status', 'Your session has expired. Please log in again.');
        }

        // Update last activity timestamp
        $this->sessionService->updateActivity($request);

        return $next($request);
    }
}