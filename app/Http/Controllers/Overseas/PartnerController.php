<?php

namespace App\Http\Controllers\Overseas;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PartnerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display list of overseas partners
     */
    public function index()
    {
        $partners = User::where('user_type', 'overseas')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('overseas.partners.index', compact('partners'));
    }

    /**
     * Show partner details
     */
    public function show($id)
    {
        $partner = User::where('user_type', 'overseas')->findOrFail($id);
        return view('overseas.partners.show', compact('partner'));
    }

    /**
     * Show create partner form
     */
    public function create()
    {
        return view('overseas.partners.create');
    }

    /**
     * Store new partner
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'company_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $partner = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'user_type' => 'overseas',
            'verification_status' => 'pending',
            'country' => $request->country,
            'city' => $request->city,
            'address' => $request->address,
            'company_name' => $request->company_name,
            'registration_completed' => true,
        ]);

        return redirect()->route('overseas.partners.index')
            ->with('success', 'Overseas partner created successfully!');
    }

    /**
     * Show edit partner form
     */
    public function edit($id)
    {
        $partner = User::where('user_type', 'overseas')->findOrFail($id);
        return view('overseas.partners.edit', compact('partner'));
    }

    /**
     * Update partner
     */
    public function update(Request $request, $id)
    {
        $partner = User::where('user_type', 'overseas')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'company_name' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'country' => $request->country,
            'city' => $request->city,
            'address' => $request->address,
            'company_name' => $request->company_name,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $partner->update($data);

        return redirect()->route('overseas.partners.index')
            ->with('success', 'Overseas partner updated successfully!');
    }

    /**
     * Delete partner
     */
    public function destroy($id)
    {
        $partner = User::where('user_type', 'overseas')->findOrFail($id);
        $partner->delete();

        return redirect()->route('overseas.partners.index')
            ->with('success', 'Overseas partner deleted successfully!');
    }

    /**
     * Toggle partner status
     */
    public function toggleStatus($id)
    {
        $partner = User::where('user_type', 'overseas')->findOrFail($id);
        
        $newStatus = $partner->verification_status === 'approved' ? 'suspended' : 'approved';
        $partner->update(['verification_status' => $newStatus]);

        return redirect()->route('overseas.partners.index')
            ->with('success', 'Partner status updated successfully!');
    }
}