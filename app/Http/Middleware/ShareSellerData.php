<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShareSellerData
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            
            // Only share seller data if user is a seller
            if ($user->user_type === 'seller') {
                // Share seller specific data
                view()->share('sellerData', [
                    'seller' => $user,
                    'total_orders' => \App\Models\Order::where('seller_id', $user->id)->count(),
                    'pending_orders' => \App\Models\Order::where('seller_id', $user->id)->where('status', 'pending')->count(),
                ]);
            } elseif ($user->user_type === 'rider') {
                // Share rider specific data
                view()->share('riderData', [
                    'rider' => $user,
                    'total_deliveries' => \App\Models\Delivery::where('rider_id', $user->id)->count(),
                    'active_deliveries' => \App\Models\Delivery::where('rider_id', $user->id)
                        ->whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery'])
                        ->count(),
                ]);
            } else {
                // Share default user data
                view()->share('userData', [
                    'user' => $user,
                ]);
            }
        }

        return $next($request);
    }
}