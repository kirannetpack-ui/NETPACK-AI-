<?php

namespace App\Http\Controllers\Domestic;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    /**
     * Display a listing of Domestic Operations Staff.
     */
    public function index()
    {
        $staff = User::where('user_type', User::TYPE_STAFF)
            ->where(function ($q) {
                $q->whereIn('service_scope', ['domestic', 'ecommerce'])
                  ->orWhere('created_by', Auth::id());
            })
            ->latest()
            ->paginate(15);

        return view('domestic.staff.index', compact('staff'));
    }

    /**
     * Show the form for creating a new Domestic Staff member.
     */
    public function create()
    {
        return view('domestic.staff.create');
    }

    /**
     * Store a newly created Domestic Staff member.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'nullable|string|max:50',
            'service_scope' => 'nullable|in:domestic,ecommerce',
        ]);

        $staff = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => User::TYPE_STAFF,
            'service_scope' => $request->service_scope ?? 'domestic',
            'created_by' => Auth::id(),
            'phone' => $request->phone,
            'role' => $request->role ?? 'domestic_staff',
            'verification_status' => 'approved',
            'registration_completed' => true,
            'password_changed' => true,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
            'is_active' => true,
        ]);

        $section = $staff->service_scope === 'ecommerce' ? 'E-Commerce & Rider' : 'Nepal Domestic';
        return redirect()->route('domestic.staff.index')
            ->with('success', "Domestic operations staff '{$staff->name}' created successfully. This staff will exclusively access the {$section} section.");
    }

    /**
     * Remove the specified Domestic Staff member.
     */
    public function destroy($id)
    {
        $staff = User::where('user_type', User::TYPE_STAFF)
            ->where(function ($q) {
                $q->whereIn('service_scope', ['domestic', 'ecommerce'])
                  ->orWhere('created_by', Auth::id());
            })
            ->findOrFail($id);

        $name = $staff->name;
        $staff->delete();

        return redirect()->route('domestic.staff.index')
            ->with('success', "Staff member '{$name}' deleted successfully.");
    }
}
