<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // For now, allow all authenticated users
        // In production, check for 'admin' role
        if (!auth()->check()) {
            return redirect('login');
        }

        return $next($request);
    }
}
