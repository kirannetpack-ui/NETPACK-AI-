<?php
// app/Http/Controllers/Partner/AuthController.php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\DomesticPartner;
use App\Models\PartnerStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('partner.auth.login');
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        // Check partner login
        $partner = DomesticPartner::where('email', $request->email)->first();
        
        if ($partner && Hash::check($request->password, $partner->password)) {
            Auth::guard('partner')->login($partner);
            return redirect()->route('partner.dashboard');
        }
        
        // Check staff login
        $staff = PartnerStaff::where('email', $request->email)
            ->where('is_active', true)
            ->first();
        
        if ($staff && Hash::check($request->password, $staff->password)) {
            Auth::guard('partner_staff')->login($staff);
            $staff->update(['last_login_at' => now()]);
            return redirect()->route('partner.staff.dashboard');
        }
        
        return back()->withErrors(['email' => 'Invalid credentials']);
    }
    
    public function logout()
    {
        if (Auth::guard('partner')->check()) {
            Auth::guard('partner')->logout();
        }
        if (Auth::guard('partner_staff')->check()) {
            Auth::guard('partner_staff')->logout();
        }
        return redirect()->route('partner.login');
    }
}