<?php
// app/Http/Controllers/Agency/StaffAuthController.php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\AgencyStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StaffAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('agency.staff.login');
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        $staff = AgencyStaff::where('email', $request->email)
            ->where('is_active', true)
            ->first();
        
        if ($staff && Hash::check($request->password, $staff->password)) {
            Auth::guard('agency_staff')->login($staff);
            $staff->update(['last_login_at' => now()]);
            
            return redirect()->route('agency.staff.dashboard');
        }
        
        return back()->withErrors(['email' => 'Invalid credentials']);
    }
    
    public function logout()
    {
        Auth::guard('agency_staff')->logout();
        return redirect()->route('agency.staff.login');
    }
}