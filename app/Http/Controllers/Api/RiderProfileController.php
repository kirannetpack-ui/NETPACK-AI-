<?php
// app/Http/Controllers/Api/RiderProfileController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RiderProfileController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'vehicle_type' => $user->vehicle_type,
                'license_number' => $user->license_number,
                'is_available' => $user->is_available,
                'current_latitude' => $user->current_latitude,
                'current_longitude' => $user->current_longitude,
                'total_deliveries' => $user->shipmentsAsRider()->count(),
                'completed_deliveries' => $user->shipmentsAsRider()->where('status', 'delivered')->count(),
                'rating' => $user->rating ?? 5.0
            ]
        ]);
    }
    
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string',
            'vehicle_type' => 'sometimes|in:bike,van,car'
        ]);
        
        $user->update($request->only(['name', 'phone', 'vehicle_type']));
        
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
    }
    
    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'is_available' => 'boolean'
        ]);
        
        $user = $request->user();
        $user->current_latitude = $request->latitude;
        $user->current_longitude = $request->longitude;
        
        if ($request->has('is_available')) {
            $user->is_available = $request->is_available;
        }
        
        $user->save();
        
        // Broadcast location update via WebSocket
        // broadcast(new RiderLocationUpdated($user))->toOthers();
        
        return response()->json([
            'success' => true,
            'message' => 'Location updated'
        ]);
    }
}