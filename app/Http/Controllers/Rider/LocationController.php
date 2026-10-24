<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Update rider's current location
     */
    public function update(Request $request)
    {
        $rider = Auth::user();

        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid location data',
                'errors' => $validator->errors()
            ], 422);
        }

        $rider->update([
            'current_latitude' => $request->latitude,
            'current_longitude' => $request->longitude,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully',
            'data' => [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]
        ]);
    }

    /**
     * Get rider's current location
     */
    public function getLocation(Request $request)
    {
        $rider = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'latitude' => $rider->current_latitude,
                'longitude' => $rider->current_longitude,
                'last_updated' => $rider->updated_at,
            ]
        ]);
    }

    /**
     * Get nearby riders
     */
    public function getNearby(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters',
                'errors' => $validator->errors()
            ], 422);
        }

        $radius = $request->radius ?? 10; // Default 10km radius

        // Get nearby riders
        $riders = User::where('user_type', 'rider')
            ->where('is_available', true)
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->get();

        // Filter by distance
        $nearbyRiders = $riders->filter(function ($rider) use ($request, $radius) {
            $distance = $this->calculateDistance(
                $request->latitude,
                $request->longitude,
                $rider->current_latitude,
                $rider->current_longitude
            );
            return $distance <= $radius;
        });

        return response()->json([
            'success' => true,
            'data' => $nearbyRiders->map(function ($rider) {
                return [
                    'id' => $rider->id,
                    'name' => $rider->name,
                    'phone' => $rider->phone,
                    'rating' => $rider->rating,
                    'vehicle_type' => $rider->vehicle_type,
                    'latitude' => $rider->current_latitude,
                    'longitude' => $rider->current_longitude,
                    'distance' => $this->calculateDistance(
                        request()->latitude,
                        request()->longitude,
                        $rider->current_latitude,
                        $rider->current_longitude
                    ),
                ];
            })
        ]);
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}