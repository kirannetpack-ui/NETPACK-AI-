<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        $user = Auth::user();
        
        // Super Admin and Admin have full administrative access.
        if (in_array($user->user_type, ['super_admin', 'admin'], true)) {
            return $next($request);
        }

        // Staff created by Super Admin (scope = all) can view all services and underneath.
        if ($user->user_type === 'staff' && $user->effectiveServiceScope() === 'all') {
            return $next($request);
        }

        abort(403, 'Unauthorized access. Admin privileges required.');
    }
}
