<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCsrfTokenForAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip CSRF verification for admin API routes
        if ($request->is('api/admin/*')) {
            return $next($request);
        }

        // For regular web requests, apply CSRF verification
        return app(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)->handle($request, $next);
    }
}
