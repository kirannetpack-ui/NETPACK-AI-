<?php

namespace App\Http\Controllers\International;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * Display a listing of International Operations Staff.
     */
    public function index()
    {
        $staff = User::where('user_type', User::TYPE_STAFF)
            ->where(function ($q) {
                $q->where('service_scope', 'international')
                  ->orWhere('created_by', Auth::id());
            })
            ->latest()
            ->paginate(15);

        return view('international.staff.index', compact('staff'));
    }

    /**
     * Show the form for creating a new International Staff member.
     */
    public function create()
    {
        return view('international.staff.create');
    }

    /**
     * Store a newly created International Staff member.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'nullable|string|max:50',
        ]);

        $staff = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => User::TYPE_STAFF,
            'service_scope' => 'international',
            'created_by' => Auth::id(),
            'phone' => $request->phone,
            'role' => $request->role ?? 'international_staff',
            'verification_status' => 'approved',
            'registration_completed' => true,
            'password_changed' => true,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
            'is_active' => true,
        ]);

        return redirect()->route('international.staff.index')
            ->with('success', "International operations staff '{$staff->name}' created successfully. This staff will exclusively access the International section.");
    }

    /**
     * Remove the specified International Staff member.
     */
    public function destroy($id)
    {
        $staff = User::where('user_type', User::TYPE_STAFF)
            ->where(function ($q) {
                $q->where('service_scope', 'international')
                  ->orWhere('created_by', Auth::id());
            })
            ->findOrFail($id);

        $name = $staff->name;
        $staff->delete();

        return redirect()->route('international.staff.index')
            ->with('success', "Staff member '{$name}' deleted successfully.");
    }
}
