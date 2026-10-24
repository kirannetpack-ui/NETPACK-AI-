<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PublicPreview
{
    public function handle(Request $request, Closure $next)
    {
        if (config('deployment.mode') !== 'public-preview') {
            return $next($request);
        }

        if ($request->isMethod('GET') && $request->path() === '/') {
            return response()->view('welcome')->header('Cache-Control', 'no-store');
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Booking and account services are not open yet.'], 503);
        }

        return response()->view('errors.service-unavailable', [], 503)
            ->header('Cache-Control', 'no-store');
    }
}
