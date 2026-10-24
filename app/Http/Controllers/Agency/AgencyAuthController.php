<?php
// app/Http/Controllers/Agency/AgencyAuthController.php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AgencyAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('agency.login');
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        $agency = Agency::where('email', $request->email)->first();
        
        if ($agency && Hash::check($request->password, $agency->password)) {
            Auth::guard('agency')->login($agency);
            return redirect()->route('agency.dashboard');
        }
        
        return back()->withErrors(['email' => 'Invalid credentials']);
    }
    
    public function logout()
    {
        Auth::guard('agency')->logout();
        return redirect()->route('agency.login');
    }
}