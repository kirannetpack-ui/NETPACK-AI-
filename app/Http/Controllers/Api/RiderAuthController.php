<?php
// app/Http/Controllers/Api/RiderAuthController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RiderAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_token' => 'nullable|string'
        ]);
        
        $user = User::where('email', $request->email)->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        
        if ($user->user_type !== 'rider') {
            return response()->json([
                'success' => false,
                'message' => 'Account is not registered as a rider'
            ], 403);
        }
        
        // Update device token for push notifications
        if ($request->device_token) {
            $user->device_token = $request->device_token;
            $user->save();
        }
        
        $token = $user->createToken('rider-token')->plainTextToken;
        
        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'user_type' => $user->user_type
            ]
        ]);
    }
    
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'phone' => 'required|string',
            'vehicle_type' => 'required|in:bike,van,car',
            'license_number' => 'required|string'
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'user_type' => 'rider',
            'vehicle_type' => $request->vehicle_type,
            'license_number' => $request->license_number,
            'is_available' => true
        ]);
        
        // Create wallet for rider
        Wallet::create([
            'user_id' => $user->id,
            'user_type' => 'rider',
            'balance' => 0,
            'total_earned' => 0
        ]);
        
        $token = $user->createToken('rider-token')->plainTextToken;
        
        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user
        ], 201);
    }
    
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}