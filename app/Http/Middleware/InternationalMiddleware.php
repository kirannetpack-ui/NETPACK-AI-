<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InternationalMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $allowedTypes = ['international_admin', 'overseas', 'super_admin', 'admin'];
        
        if (!in_array($user->user_type, $allowedTypes)) {
            abort(403, 'Unauthorized access. International service access required.');
        }

        return $next($request);
    }
}