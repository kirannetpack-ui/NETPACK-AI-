<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display settings page
     */
    public function index()
    {
        $user = Auth::user();
        return view('seller.settings.index', compact('user'));
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'business_name' => 'nullable|string|max:255',
            'business_address' => 'nullable|string',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'business_name' => $request->business_name,
            'business_address' => $request->business_address,
        ]);

        return redirect()->route('seller.settings')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->with('error', 'Current password is incorrect.');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('seller.settings')
            ->with('success', 'Password changed successfully!');
    }

    /**
     * Update bank details
     */
    public function updateBank(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'bank_name' => 'nullable|string|max:255',
            'account_holder_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'account_type' => 'nullable|in:savings,current',
            'ifsc_code' => 'nullable|string|max:50',
            'esewa_id' => 'nullable|string|max:50',
            'khalti_id' => 'nullable|string|max:50',
            'qr_code' => 'nullable|file|mimes:jpg,jpeg,png|max:3072',
        ]);

        $user->update([
            'bank_name' => $request->bank_name,
            'account_holder_name' => $request->account_holder_name,
            'account_number' => $request->account_number,
            'account_type' => $request->account_type,
            'ifsc_code' => $request->ifsc_code,
        ]);

        $qrPath = null;
        if ($request->hasFile('qr_code')) {
            $qrPath = $request->file('qr_code')->store('seller-qrs', 'public');
        }

        if (class_exists('App\Models\SellerPaymentMethod')) {
            if ($request->bank_name && $request->account_number) {
                \App\Models\SellerPaymentMethod::updateOrCreate(
                    ['user_id' => $user->id, 'method_type' => 'bank'],
                    [
                        'bank_name' => $request->bank_name,
                        'account_name' => $request->account_holder_name ?? $user->name,
                        'account_number' => $request->account_number,
                        'branch' => $request->ifsc_code,
                        'account_type' => $request->account_type ?? 'savings',
                        'is_default' => true,
                        'verification_document' => $qrPath,
                        'metadata' => array_filter([
                            'esewa_id' => $request->esewa_id,
                            'khalti_id' => $request->khalti_id,
                            'qr_path' => $qrPath,
                        ]),
                    ]
                );
            }

            if ($request->esewa_id) {
                \App\Models\SellerPaymentMethod::updateOrCreate(
                    ['user_id' => $user->id, 'method_type' => 'esewa'],
                    [
                        'esewa_id' => $request->esewa_id,
                        'mobile_number' => $request->esewa_id,
                        'account_name' => $request->account_holder_name ?? $user->name,
                    ]
                );
            }

            if ($request->khalti_id) {
                \App\Models\SellerPaymentMethod::updateOrCreate(
                    ['user_id' => $user->id, 'method_type' => 'khalti'],
                    [
                        'khalti_id' => $request->khalti_id,
                        'mobile_number' => $request->khalti_id,
                        'account_name' => $request->account_holder_name ?? $user->name,
                    ]
                );
            }
        }

        return redirect()->route('seller.settings')
            ->with('success', 'Bank & COD Settlement details updated successfully!');
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request)
    {
        $user = Auth::user();
        
        $user->update([
            'email_notifications' => $request->has('email_notifications'),
            'sms_notifications' => $request->has('sms_notifications'),
            'order_updates' => $request->has('order_updates'),
        ]);

        return redirect()->route('seller.settings')
            ->with('success', 'Notification settings updated successfully!');
    }
}