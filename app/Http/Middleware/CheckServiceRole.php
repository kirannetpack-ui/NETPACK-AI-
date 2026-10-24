<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckServiceRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        $user = Auth::user();
        
        // Super Admin and primary system Admin have access to everything
        if (in_array($user->user_type, ['super_admin', 'admin'], true)) {
            return $next($request);
        }

        // Staff created by Super Admin (scope = all) can view all services and underneath
        if ($user->user_type === 'staff' && $user->effectiveServiceScope() === 'all') {
            return $next($request);
        }

        // Normalize user type
        $userType = strtolower(trim($user->user_type ?? ''));
        $normalizedRoles = array_map(fn($r) => strtolower(trim($r)), $roles);

        // Service-scoped staff enforcement:
        // International staff can only see international section.
        // Domestic staff can only see domestic section.
        if ($userType === 'staff') {
            $scope = $user->effectiveServiceScope();

            if (in_array('international_admin', $normalizedRoles, true)) {
                if ($scope === 'international') {
                    return $next($request);
                }
                abort(403, 'Unauthorized access. You only have permission to access your assigned service.');
            }

            if (in_array('domestic_admin', $normalizedRoles, true)) {
                if ($scope === 'domestic' || $scope === 'ecommerce') {
                    return $next($request);
                }
                abort(403, 'Unauthorized access. You only have permission to access your assigned service.');
            }

            if (in_array('staff', $normalizedRoles, true)) {
                return $next($request);
            }
        }

        // Check explicit user_type match
        if (in_array($userType, $normalizedRoles, true)) {
            return $next($request);
        }

        // Client and Customer are treated as unified client entities
        if (in_array('client', $normalizedRoles, true) || in_array('customer', $normalizedRoles, true)) {
            if (in_array($userType, ['client', 'customer'], true)) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized access. You do not have permission to access this service.');
    }
}