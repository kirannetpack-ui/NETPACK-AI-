<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomesticPartner;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PartnerController extends Controller
{
    public function index()
    {
        $partners = DomesticPartner::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.partners.index', compact('partners'));
    }

    public function create()
    {
        return view('admin.partners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|unique:domestic_partners',
            'phone' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'district' => 'required|string',
            'province' => 'required|string',
            'margin_percentage' => 'required|numeric|min:0|max:100',
            'password' => 'required|string|min:6|confirmed'
        ]);

        $partner = DomesticPartner::create([
            'name' => $request->company_name,
            'code' => 'DP' . strtoupper(uniqid()),
            'company_name' => $request->company_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'district' => $request->district,
            'province' => $request->province,
            'contact_person' => $request->contact_person,
            'margin_percentage' => $request->margin_percentage,
            'is_active' => true,
            'kyc_verified' => false,
            'verification_status' => 'pending'
        ]);

        // Create user record
        $user = User::create([
            'name' => $request->contact_person,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'user_type' => 'partner',
            'verification_status' => 'pending'
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'user_type' => 'partner',
            'balance' => 0
        ]);

        return redirect()->route('admin.partners.index')->with('success', 'Partner created successfully!');
    }

    public function show($id)
    {
        $partner = DomesticPartner::findOrFail($id);
        return view('admin.partners.show', compact('partner'));
    }

    public function edit($id)
    {
        $partner = DomesticPartner::findOrFail($id);
        return view('admin.partners.edit', compact('partner'));
    }

    public function update(Request $request, $id)
    {
        $partner = DomesticPartner::findOrFail($id);

        $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|unique:domestic_partners,email,' . $id,
            'phone' => 'required|string',
            'margin_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $partner->update($request->all());

        return redirect()->route('admin.partners.index')->with('success', 'Partner updated successfully!');
    }

    public function destroy($id)
    {
        $partner = DomesticPartner::findOrFail($id);
        $partner->delete();
        return redirect()->route('admin.partners.index')->with('success', 'Partner deleted successfully!');
    }

    public function verify($id)
    {
        $partner = DomesticPartner::findOrFail($id);
        return view('admin.partners.verify', compact('partner'));
    }

    public function approve($id)
    {
        $partner = DomesticPartner::findOrFail($id);
        $partner->update([
            'verification_status' => 'approved',
            'kyc_verified' => true,
            'is_active' => true
        ]);
        return redirect()->route('admin.partners.index')->with('success', 'Partner approved successfully!');
    }

    public function reject(Request $request, $id)
    {
        $partner = DomesticPartner::findOrFail($id);
        $partner->update([
            'verification_status' => 'rejected',
            'is_active' => false
        ]);
        return redirect()->route('admin.partners.index')->with('success', 'Partner rejected.');
    }
}