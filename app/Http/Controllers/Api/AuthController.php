<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();

        if ($user->verification_status !== 'approved') {
            Auth::logout();

            return response()->json([
                'success' => false,
                'message' => 'Your account is not approved for access.',
            ], 403);
        }

        if (!$user->password_changed) {
            Auth::logout();

            return response()->json([
                'success' => false,
                'message' => 'You must change your temporary password before using the API.',
            ], 409);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
            'user_type' => 'required|in:customer,seller,rider',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'verification_status' => 'pending',
            'registration_completed' => true,
        ]);

        return response()->json([
            'success' => true,
            'user' => $user,
            'message' => 'Registration successful! Please wait for admin approval.'
        ], 202);
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user()
        ]);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        $request->user()->forceFill([
            'password' => Hash::make($validated['password']),
            'password_changed' => true,
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    public function changeTemporaryPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (
            !$user
            || $user->verification_status !== 'approved'
            || $user->password_changed
            || !Hash::check($validated['current_password'], $user->password)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'The temporary-password change request is invalid.',
            ], 422);
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'password_changed' => true,
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully. You may now sign in.',
        ]);
    }
}
