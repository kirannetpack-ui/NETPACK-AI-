<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\RiderProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show rider settings page with RiderProfile dossier
     */
    public function index()
    {
        /** @var User $rider */
        $rider = Auth::user();
        $profile = $rider->ensureRiderProfile();

        return view('rider.settings', compact('rider', 'profile'));
    }

    /**
     * Update rider profile & KYC information
     */
    public function updateProfile(Request $request)
    {
        /** @var User $rider */
        $rider = Auth::user();
        $profile = $rider->ensureRiderProfile();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'municipality' => 'nullable|string|max:100',
            'ward' => 'nullable|string|max:10',
            'emergency_contact' => 'nullable|string|max:50',
            'vehicle_type' => 'required|string|in:motorcycle,scooter,bicycle,car,van,other,bike,truck',
            'vehicle_number' => 'nullable|string|max:50',
            'license_number' => 'required|string|max:50',
            'license_expiry_date' => 'nullable|date',
            'citizenship_number' => 'nullable|string|max:50',
            'driving_license_doc' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'vehicle_registration_doc' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'citizenship_front' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'citizenship_back' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'selfie_photo' => 'nullable|image|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $rider->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'district' => $request->district,
            'province' => $request->province,
            'vehicle_type' => $request->vehicle_type,
            'license_number' => $request->license_number,
        ]);

        $profileData = [
            'full_name' => $request->name,
            'mobile' => $request->phone,
            'address' => $request->address,
            'district' => $request->district,
            'province' => $request->province,
            'municipality' => $request->municipality,
            'ward' => $request->ward,
            'emergency_contact' => $request->emergency_contact,
            'vehicle_type' => in_array($request->vehicle_type, ['bike', 'truck']) ? 'motorcycle' : $request->vehicle_type,
            'vehicle_number' => $request->vehicle_number ?: ($profile->vehicle_number ?? $request->license_number),
            'driving_license_number' => $request->license_number,
            'license_expiry_date' => $request->license_expiry_date ?: $profile->license_expiry_date,
            'citizenship_number' => $request->citizenship_number ?: $profile->citizenship_number,
        ];

        // Process file uploads if uploaded
        if ($request->hasFile('driving_license_doc')) {
            $profileData['driving_license_doc_path'] = $request->file('driving_license_doc')->store('kyc/license', 'private');
        }
        if ($request->hasFile('vehicle_registration_doc')) {
            $profileData['vehicle_registration_doc_path'] = $request->file('vehicle_registration_doc')->store('kyc/vehicle', 'private');
        }
        if ($request->hasFile('citizenship_front')) {
            $profileData['citizenship_front_path'] = $request->file('citizenship_front')->store('kyc/citizenship', 'private');
        }
        if ($request->hasFile('citizenship_back')) {
            $profileData['citizenship_back_path'] = $request->file('citizenship_back')->store('kyc/citizenship', 'private');
        }
        if ($request->hasFile('selfie_photo')) {
            $profileData['selfie_photo_path'] = $request->file('selfie_photo')->store('kyc/selfies', 'private');
            $profileData['profile_photo_path'] = $profileData['selfie_photo_path'];
        }

        if (array_intersect(array_keys($profileData), ['driving_license_doc_path', 'vehicle_registration_doc_path', 'citizenship_front_path', 'citizenship_back_path', 'selfie_photo_path'])) {
            $profileData['verification_status'] = 'pending';
            $profileData['verified_at'] = null;
            $profileData['verified_by'] = null;
        }
        $profile->update($profileData);

        return redirect()->route('rider.settings')
            ->with('success', 'Rider profile & KYC documents updated successfully!');
    }

    /**
     * Update rider password
     */
    public function updatePassword(Request $request)
    {
        /** @var User $rider */
        $rider = Auth::user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if (! Hash::check($request->current_password, $rider->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->withInput();
        }

        $rider->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->route('rider.settings')
            ->with('success', 'Password updated successfully!');
    }

    /**
     * Update rider availability status & carrying capacity
     */
    public function updateAvailability(Request $request)
    {
        /** @var User $rider */
        $rider = Auth::user();
        $profile = $rider->ensureRiderProfile();

        $status = $request->input('availability_status', 'offline');
        if (! in_array($status, ['online', 'offline', 'busy'])) {
            $status = $request->has('is_available') ? 'online' : 'offline';
        }

        $rider->update([
            'is_available' => $status === 'online',
            'is_online' => $status === 'online',
        ]);

        $profile->update([
            'availability_status' => $status,
            'service_radius_km' => (float) $request->input('service_radius_km', $profile->service_radius_km ?? 10.0),
            'max_carrying_weight' => (float) $request->input('max_carrying_weight', $profile->max_carrying_weight ?? 15.0),
            'max_active_packages' => (int) $request->input('max_active_packages', $profile->max_active_packages ?? 5),
        ]);

        return redirect()->route('rider.settings')
            ->with('success', 'Availability updated to: '.strtoupper($status));
    }

    /**
     * Update rider bank & digital wallet payout details
     */
    public function updateBank(Request $request)
    {
        /** @var User $rider */
        $rider = Auth::user();
        $profile = $rider->ensureRiderProfile();

        $validator = Validator::make($request->all(), [
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'account_holder_name' => 'nullable|string|max:255',
            'esewa_id' => 'nullable|string|max:50',
            'khalti_id' => 'nullable|string|max:50',
            'bank_address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $profile->update([
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->account_number,
            'bank_account_name' => $request->account_holder_name,
            'esewa_id' => $request->esewa_id,
            'khalti_id' => $request->khalti_id,
        ]);

        $wallet = $rider->wallet;
        if ($wallet) {
            $wallet->update([
                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->account_number,
                'bank_account_name' => $request->account_holder_name,
                'bank_address' => $request->bank_address,
            ]);
        }

        return redirect()->route('rider.settings')
            ->with('success', 'Bank & digital payout accounts updated successfully!');
    }
}
