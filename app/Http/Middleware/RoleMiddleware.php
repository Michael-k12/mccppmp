<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        // Make sure user is logged in
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Debug: check what role is loaded
        // dd(Auth::user()->role);

        // If user role doesn’t match, deny access
        if (Auth::user()->role !== $role) {
            abort(403, 'Unauthorized - You must be a '.$role);
        }

        return $next($request);
    }
}
