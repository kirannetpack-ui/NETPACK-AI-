<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShareRiderData
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            if ($user->user_type === 'rider') {
                view()->share([
                    'riderDepositBalance' => $user->rider_deposit_balance ?? 0,
                    'riderOnline' => $user->is_online ?? false,
                    'riderAvailable' => $user->is_available ?? false,
                ]);
            }
        }

        return $next($request);
    }
}