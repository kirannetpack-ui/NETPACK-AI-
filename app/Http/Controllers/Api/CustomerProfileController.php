<?php
// app/Http/Controllers/Api/CustomerProfileController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerProfileController extends Controller
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
                'avatar' => $user->profile_photo_url,
                'member_since' => $user->created_at->format('M Y'),
                'total_orders' => $user->shipmentsAsCustomer()->count(),
                'completed_orders' => $user->shipmentsAsCustomer()->where('status', 'delivered')->count()
            ]
        ]);
    }
    
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string',
            'avatar' => 'nullable|image|max:2048'
        ]);
        
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->profile_photo_path = $path;
        }
        
        $user->update($request->only(['name', 'phone']));
        
        return response()->json([
            'success' => true,
            'message' => 'Profile updated',
            'data' => $user
        ]);
    }
    
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed'
        ]);
        
        $user = $request->user();
        
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }
        
        $user->password = Hash::make($request->new_password);
        $user->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }
}