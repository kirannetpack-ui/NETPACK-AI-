<?php
// app/Http/Controllers/Partner/StaffController.php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\PartnerStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StaffController extends Controller
{
    public function index()
    {
        $staff = PartnerStaff::where('partner_id', Auth::guard('partner')->id())->get();
        return view('partner.staff.index', compact('staff'));
    }
    
    public function create()
    {
        return view('partner.staff.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:partner_staff',
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
            'phone' => 'required|string',
            'position' => 'required|string',
            'role' => 'required|in:admin,scanner,delivery_boy,dispatcher',
            'can_scan_arrival' => 'boolean',
            'can_scan_departure' => 'boolean',
            'can_scan_delivery' => 'boolean',
        ]);
        
        PartnerStaff::create([
            'partner_id' => Auth::guard('partner')->id(),
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'position' => $request->position,
            'role' => $request->role,
            'can_scan_arrival' => $request->can_scan_arrival ?? false,
            'can_scan_departure' => $request->can_scan_departure ?? false,
            'can_scan_delivery' => $request->can_scan_delivery ?? false,
            'can_add_notes' => $request->can_add_notes ?? true,
            'is_active' => true
        ]);
        
        return redirect()->route('partner.staff.index')->with('success', 'Staff member added successfully');
    }
    
    public function edit(PartnerStaff $staff)
    {
        if ($staff->partner_id !== Auth::guard('partner')->id()) {
            abort(403);
        }
        return view('partner.staff.edit', compact('staff'));
    }
    
    public function update(Request $request, PartnerStaff $staff)
    {
        if ($staff->partner_id !== Auth::guard('partner')->id()) {
            abort(403);
        }
        
        $staff->update($request->except('password'));
        
        if ($request->filled('password')) {
            $staff->update(['password' => Hash::make($request->password)]);
        }
        
        return redirect()->route('partner.staff.index')->with('success', 'Staff updated successfully');
    }
    
    public function destroy(PartnerStaff $staff)
    {
        if ($staff->partner_id !== Auth::guard('partner')->id()) {
            abort(403);
        }
        $staff->delete();
        return back()->with('success', 'Staff deleted successfully');
    }
}
