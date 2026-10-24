<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\RiderProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    /**
     * Show the registration form
     */
    public function showRegistrationForm(Request $request)
    {
        $userType = $request->get('type', 'client');
        $allowedTypes = ['client', 'customer', 'seller', 'rider', 'partner'];

        if (! in_array($userType, $allowedTypes)) {
            $userType = 'client';
        }

        return view('auth.register', compact('userType'));
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        // Validate only the fields that exist in the form
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();

        // Handle uploaded KYC files
        if ($request->hasFile('citizenship_front')) {
            $data['citizenship_front_path'] = $request->file('citizenship_front')->store('kyc/citizenship', 'private');
        }
        if ($request->hasFile('citizenship_back')) {
            $data['citizenship_back_path'] = $request->file('citizenship_back')->store('kyc/citizenship', 'private');
        }
        if ($request->hasFile('driving_license_doc')) {
            $data['driving_license_doc_path'] = $request->file('driving_license_doc')->store('kyc/license', 'private');
        }
        if ($request->hasFile('vehicle_registration_doc')) {
            $data['vehicle_registration_doc_path'] = $request->file('vehicle_registration_doc')->store('kyc/vehicle', 'private');
        }
        if ($request->hasFile('selfie_photo')) {
            $data['selfie_photo_path'] = $request->file('selfie_photo')->store('kyc/selfies', 'private');
        }

        $user = $this->create($data);

        // Log the registration
        \Log::info('New user registered', [
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => $user->user_type,
        ]);

        return redirect()->route('registration.pending')
            ->with('success', 'Registration successful! Please wait for admin approval.')
            ->with('user', $user);
    }

    /**
     * Get a validator for an incoming registration request.
     */
    protected function validator(array $data)
    {
        // Base rules for all users
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'dob' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'gender' => ['required', 'in:male,female,other'],
            'nationality' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'user_type' => ['required', 'in:client,customer,seller,rider,partner'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'terms' => ['required', 'accepted'],
        ];

        // Additional validation based on user type
        if (isset($data['user_type'])) {
            if (in_array($data['user_type'], ['seller', 'partner'])) {
                $rules['business_name'] = ['required', 'string', 'max:255'];
                $rules['business_address'] = ['required', 'string', 'max:500'];
                $rules['pan_number'] = ['nullable', 'string', 'max:50'];
            }

            if ($data['user_type'] === 'rider') {
                $rules['license_number'] = ['required', 'string', 'max:50'];
                $rules['vehicle_type'] = ['required', 'string', 'max:50'];
                $rules['vehicle_registration_number'] = ['required', 'string', 'max:50'];
                $rules['license_expiry_date'] = ['nullable', 'date'];
                $rules['emergency_contact'] = ['nullable', 'string', 'max:50'];
                $rules['municipality'] = ['nullable', 'string', 'max:100'];
                $rules['ward'] = ['nullable', 'string', 'max:10'];
            }
        }

        // Custom error messages
        $messages = [
            'name.required' => 'Full name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'phone.required' => 'Phone number is required.',
            'dob.required' => 'Date of birth is required.',
            'dob.before' => 'Date of birth must be before today.',
            'dob.after' => 'Invalid date of birth.',
            'gender.required' => 'Gender is required.',
            'nationality.required' => 'Nationality is required.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'user_type.required' => 'User type is required.',
            'user_type.in' => 'Invalid user type selected.',
            'terms.required' => 'You must accept the terms and conditions.',
            'terms.accepted' => 'You must accept the terms and conditions.',
            'business_name.required' => 'Business name is required.',
            'business_address.required' => 'Business address is required.',
            'license_number.required' => 'License number is required.',
            'vehicle_type.required' => 'Vehicle type is required.',
            'vehicle_registration_number.required' => 'Vehicle registration number is required.',
        ];

        return Validator::make($data, $rules, $messages);
    }

    /**
     * Create a new user instance after a valid registration.
     */
    protected function create(array $data)
    {
        $userData = [
            'name' => $data['name'],
            'username' => Str::slug($data['name']).'-'.rand(1000, 9999),
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'],
            'user_type' => $data['user_type'],
            'role' => in_array($data['user_type'] ?? '', ['rider', 'seller', 'partner', 'domestic_admin', 'international_admin', 'admin']) ? $data['user_type'] : 'customer',
            'verification_status' => 'pending',
            'registration_completed' => true,

            // Personal Information
            'dob' => $data['dob'] ?? null,
            'gender' => $data['gender'] ?? null,
            'nationality' => $data['nationality'] ?? 'Nepali',

            // Address Information
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'district' => $data['district'] ?? null,
            'province' => $data['province'] ?? null,
            'country' => 'Nepal',
        ];

        // Add business fields for sellers and partners
        if (in_array($data['user_type'], ['seller', 'partner'])) {
            $userData['business_name'] = $data['business_name'] ?? null;
            $userData['business_address'] = $data['business_address'] ?? null;
            $userData['pan_number'] = $data['pan_number'] ?? null;
        }

        // Add rider fields
        if ($data['user_type'] === 'rider') {
            $userData['license_number'] = $data['license_number'] ?? null;
            $userData['vehicle_type'] = $data['vehicle_type'] ?? null;
            $userData['vehicle_registration_number'] = $data['vehicle_registration_number'] ?? null;
            $userData['is_available'] = true;
            $userData['rating'] = 5.00;
        }

        $user = User::create($userData);

        // Automatically create detailed RiderProfile for riders
        if ($data['user_type'] === 'rider') {
            $riderCode = 'RDR-'.date('Y').'-'.str_pad((string) $user->id, 4, '0', STR_PAD_LEFT);
            $vehicleType = in_array($data['vehicle_type'] ?? '', ['motorcycle', 'scooter', 'bicycle', 'car', 'van'])
                ? $data['vehicle_type']
                : (($data['vehicle_type'] ?? '') === 'bike' ? 'motorcycle' : 'motorcycle');

            RiderProfile::create([
                'user_id' => $user->id,
                'rider_code' => $riderCode,
                'full_name' => $user->name,
                'mobile' => $user->phone ?? '',
                'email' => $user->email,
                'dob' => $user->dob,
                'gender' => $user->gender,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'address' => $user->address,
                'province' => $user->province,
                'district' => $user->district,
                'municipality' => $data['municipality'] ?? null,
                'ward' => $data['ward'] ?? null,
                'citizenship_number' => $data['citizenship_number'] ?? null,
                'citizenship_front_path' => $data['citizenship_front_path'] ?? null,
                'citizenship_back_path' => $data['citizenship_back_path'] ?? null,
                'profile_photo_path' => $data['selfie_photo_path'] ?? null,
                'selfie_photo_path' => $data['selfie_photo_path'] ?? null,
                'verification_status' => 'pending',
                'vehicle_type' => $vehicleType,
                'vehicle_number' => $data['vehicle_registration_number'] ?? null,
                'vehicle_registration_doc_path' => $data['vehicle_registration_doc_path'] ?? null,
                'driving_license_number' => $data['license_number'] ?? null,
                'driving_license_doc_path' => $data['driving_license_doc_path'] ?? null,
                'license_expiry_date' => $data['license_expiry_date'] ?? null,
                'has_other_platform_affiliation' => ! empty($data['affiliation']) && $data['affiliation'] !== 'none',
                'affiliation' => $data['affiliation'] ?? 'none',
                'affiliation_reference_id' => $data['affiliation_reference_id'] ?? null,
                'affiliation_notes' => $data['affiliation_notes'] ?? null,
                'cod_level' => 'level_0',
                'cod_limit' => 0.00,
                'current_outstanding_cod' => 0.00,
                'trust_score' => 100,
                'badge_status' => 'new',
                'rating' => 5.00,
                'agreement_accepted' => true,
                'agreement_accepted_at' => now(),
            ]);
        }

        return $user;
    }

    /**
     * Show pending approval page
     */
    public function showPendingPage()
    {
        return view('auth.pending-approval');
    }

    /**
     * Show approval status for a specific user
     */
    public function showApprovalStatus($id)
    {
        $user = User::findOrFail($id);

        if ($user->verification_status === 'approved') {
            return redirect()->route('login')
                ->with('success', 'Your account has been approved! Please login.');
        }

        if ($user->verification_status === 'rejected') {
            return redirect()->route('login')
                ->with('error', 'Your account has been rejected. Reason: '.($user->rejection_reason ?? 'Please contact support.'));
        }

        return view('auth.pending-approval', compact('user'));
    }
}
