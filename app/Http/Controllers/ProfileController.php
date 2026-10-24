<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile.index', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised()],
        ]);

        $request->user()->forceFill([
            'password' => Hash::make($validated['new_password']),
            'password_changed' => true,
        ])->save();

        $request->session()->regenerate();

        return back()->with('success', 'Password changed successfully.');
    }

    public function updateAddress(Request $request)
    {
        $request->validate([
            'permanent_address' => 'required|string',
            'address_lat' => 'nullable|numeric',
            'address_lng' => 'nullable|numeric',
        ]);

        $user = Auth::user();
        $user->permanent_address = $request->permanent_address;
        $user->address_lat = $request->address_lat;
        $user->address_lng = $request->address_lng;
        $user->save();

        return back()->with('success', 'Address updated successfully!');
    }

    public function updateTemporary(Request $request)
    {
        $request->validate([
            'temporary_address' => 'nullable|string',
        ]);

        $user = Auth::user();
        $user->temporary_address = $request->temporary_address;
        $user->save();

        return back()->with('success', 'Temporary address updated successfully!');
    }
}
