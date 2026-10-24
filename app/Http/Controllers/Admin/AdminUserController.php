<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // This is the application's All Users screen.  The previous query
        // silently limited normal views to admin/staff, despite the form
        // allowing customer and client creation.
        if ($request->filled('status')) {
            $query->where('verification_status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get stats - including ALL pending users
        $stats = [
            'total' => User::count(),
            'admin' => User::where('user_type', 'admin')->count(),
            'staff' => User::where('user_type', 'staff')->count(),
            'approved' => User::where('verification_status', 'approved')->count(),
            'pending' => User::where('verification_status', 'pending')->count(), // ALL pending users
            'rejected' => User::where('verification_status', 'rejected')->count(),
            'suspended' => User::where('verification_status', 'suspended')->count(),
            'active' => User::where('verification_status', 'approved')->count(), // ALL approved users
            'inactive' => User::where('verification_status', '!=', 'approved')->count(),
            'all_pending' => User::where('verification_status', 'pending')->count(), // All pending users
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Show the form for creating a new admin user.
     */
    public function create()
    {
        return view('admin.users.create', ['manageableUserTypes' => $this->manageableUserTypes()]);
    }

    /**
     * Store a newly created admin user.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:12|confirmed',
            'user_type' => ['required', Rule::in(array_keys($this->manageableUserTypes()))],
            'phone' => 'nullable|string|max:20',
            'verification_status' => 'required|in:pending,approved,rejected,suspended',
            'gender' => 'nullable|in:male,female,other',
            'dob' => 'nullable|date|before:today',
            'permanent_address' => 'nullable|string',
            'temporary_address' => 'nullable|string',
            'province' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'service_scope' => 'nullable|in:all,international,domestic,ecommerce',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'service_scope' => $request->service_scope ?? 'all',
            'created_by' => $request->user()->id,
            'phone' => $request->phone,
            'verification_status' => $request->verification_status,
            'registration_completed' => $request->verification_status === 'approved',
            'approved_at' => $request->verification_status === 'approved' ? now() : null,
            'approved_by' => $request->verification_status === 'approved' ? $request->user()->id : null,
            'gender' => $request->gender,
            'dob' => $request->dob,
            'permanent_address' => $request->permanent_address,
            'temporary_address' => $request->temporary_address,
            'province' => $request->province,
            'district' => $request->district,
        ]);

        if ($user->user_type === 'partner') {
            \App\Models\DomesticPartner::firstOrCreate(
                ['id' => $user->id],
                [
                    'code' => 'PRT-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                    'name' => $user->name,
                    'company_name' => $user->name,
                    'email' => $user->email,
                    'password' => $user->password,
                    'phone' => $request->phone ?? '9800000000',
                    'province' => $request->province ?? 'Bagmati',
                    'district' => $request->district ?? 'Kathmandu',
                    'city' => $request->district ?? 'Kathmandu',
                ]
            );
        }

        return redirect()->route('admin.users.index')
            ->with('success', "{$user->user_type_label} account created successfully.");
    }

    /**
     * Display the specified admin user.
     */
    public function show(User $user)
    {
        $this->ensureCanManage($user);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $this->ensureCanManage($user);

        $riderProfile = null;
        if ($user->user_type === User::TYPE_RIDER || $user->riderProfile()->exists()) {
            $riderProfile = $user->ensureRiderProfile();
        }

        $domesticPartner = null;
        if ($user->user_type === User::TYPE_PARTNER) {
            $domesticPartner = \App\Models\DomesticPartner::find($user->id);
        }

        $metadata = is_array($user->metadata) ? $user->metadata : [];

        return view('admin.users.edit', [
            'user' => $user,
            'manageableUserTypes' => $this->manageableUserTypes(),
            'riderProfile' => $riderProfile,
            'domesticPartner' => $domesticPartner,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $this->ensureCanManage($user);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'user_type' => ['required', Rule::in(array_keys($this->manageableUserTypes()))],
            'phone' => 'nullable|string|max:20',
            'verification_status' => 'required|in:pending,approved,rejected,suspended',
            'service_scope' => 'nullable|in:all,international,domestic,ecommerce',
            'gender' => 'nullable|in:male,female,other',
            'dob' => 'nullable|date|before:today',
            'permanent_address' => 'nullable|string',
            'temporary_address' => 'nullable|string',
            'province' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:12|confirmed',

            // Rider-specific validations
            'vehicle_type' => 'nullable|string|in:motorcycle,scooter,electric_bike,bicycle,car,van',
            'vehicle_number' => 'nullable|string|max:50',
            'driving_license_number' => 'nullable|string|max:50',
            'license_expiry_date' => 'nullable|date',
            'cod_level' => 'nullable|string|in:level_1,level_2,level_3,level_4,custom',
            'cod_limit' => 'nullable|numeric|min:0',
            'current_outstanding_cod' => 'nullable|numeric|min:0',
            'trust_score' => 'nullable|integer|min:0|max:100',
            'badge_status' => 'nullable|string|in:new,verified,trusted,preferred,suspended',
            'rating' => 'nullable|numeric|min:1|max:5',
            'availability_status' => 'nullable|string|in:online,offline,busy',
            'service_radius_km' => 'nullable|numeric|min:1|max:100',
            'max_carrying_weight' => 'nullable|numeric|min:1|max:1000',
            'max_active_packages' => 'nullable|integer|min:1|max:200',
            'emergency_contact' => 'nullable|string|max:30',
            'affiliation' => 'nullable|string|in:independent,pathao,indrive,parcel,other',
            'affiliation_reference_id' => 'nullable|string|max:100',
            'affiliation_notes' => 'nullable|string|max:500',
            'payout_method' => 'nullable|string|in:bank,esewa,khalti',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:100',
            'esewa_id' => 'nullable|string|max:50',
            'khalti_id' => 'nullable|string|max:50',

            // Seller-specific validations
            'business_name' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'pan_number' => 'nullable|string|max:50',
            'merchant_category' => 'nullable|string|max:100',
            'settlement_cycle' => 'nullable|string|in:daily,weekly_monday,bi_weekly,monthly',
            'pickup_address' => 'nullable|string|max:255',
            'return_address' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:100',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'discount_tier' => 'nullable|string|in:standard,silver,gold,platinum',

            // Partner-specific validations
            'partner_code' => 'nullable|string|max:50',
            'service_type' => 'nullable|string|in:hub_to_hub_linehaul,last_mile_delivery,full_route_network',
            'margin_percentage' => 'nullable|numeric|min:0|max:100',
            'base_hub' => 'nullable|string|max:100',
            'api_status' => 'nullable|string|in:active,sandbox,disabled',

            // Client-specific validations
            'account_category' => 'nullable|string|in:individual,sme_corporate,enterprise_b2b,government_ngo',
            'billing_mode' => 'nullable|string|in:cash_on_booking,monthly_net_15,monthly_net_30,prepaid_wallet',
            'credit_limit' => 'nullable|numeric|min:0',
            'account_manager' => 'nullable|string|max:100',
            'preferred_service' => 'nullable|string|max:100',

            // Staff-specific validations
            'department' => 'nullable|string|max:100',
            'hub_location' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $statusChangedToApproved = $request->verification_status === 'approved' && $user->verification_status !== 'approved';

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'user_type' => $request->user_type,
            'phone' => $request->phone,
            'verification_status' => $request->verification_status,
            'service_scope' => $request->service_scope ?? $user->service_scope ?? 'all',
            'gender' => $request->gender,
            'dob' => $request->dob,
            'permanent_address' => $request->permanent_address,
            'temporary_address' => $request->temporary_address,
            'province' => $request->province,
            'district' => $request->district,
        ];

        // Seller attributes directly on user table
        if ($request->filled('business_name')) {
            $data['business_name'] = $request->business_name;
        } elseif ($request->filled('company_name')) {
            $data['business_name'] = $request->company_name;
        }
        if ($request->filled('pan_number')) {
            $data['pan_number'] = $request->pan_number;
        }
        if ($request->filled('pickup_address')) {
            $data['business_address'] = $request->pickup_address;
        }
        if ($request->filled('bank_name')) {
            $data['bank_name'] = $request->bank_name;
        }
        if ($request->filled('account_number')) {
            $data['account_number'] = $request->account_number;
        }
        if ($request->filled('account_holder_name')) {
            $data['account_holder_name'] = $request->account_holder_name;
        }
        if ($request->filled('branch')) {
            $data['ifsc_code'] = $request->branch;
        }

        // Maintain structured metadata
        $existingMetadata = is_array($user->metadata) ? $user->metadata : [];
        $newMetadata = array_merge($existingMetadata, array_filter([
            'company_name' => $request->company_name,
            'contact_person' => $request->contact_person,
            'pan_number' => $request->pan_number,
            'merchant_category' => $request->merchant_category,
            'settlement_cycle' => $request->settlement_cycle,
            'pickup_address' => $request->pickup_address,
            'return_address' => $request->return_address,
            'commission_rate' => $request->commission_rate,
            'discount_tier' => $request->discount_tier,
            'branch' => $request->branch,
            'partner_code' => $request->partner_code,
            'service_type' => $request->service_type,
            'margin_percentage' => $request->margin_percentage,
            'base_hub' => $request->base_hub,
            'api_status' => $request->api_status,
            'account_category' => $request->account_category,
            'billing_mode' => $request->billing_mode,
            'credit_limit' => $request->credit_limit,
            'account_manager' => $request->account_manager,
            'preferred_service' => $request->preferred_service,
            'department' => $request->department,
            'hub_location' => $request->hub_location,
            'designation' => $request->designation,
            'payout_method' => $request->payout_method,
            'esewa_id' => $request->esewa_id,
            'khalti_id' => $request->khalti_id,
        ], fn($val) => !is_null($val)));

        $data['metadata'] = $newMetadata;

        if ($statusChangedToApproved) {
            $data['registration_completed'] = true;
            $data['approved_at'] = now();
            $data['approved_by'] = auth()->id();
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Sync RiderProfile if user is rider
        if ($user->user_type === User::TYPE_RIDER) {
            $riderProfile = $user->ensureRiderProfile();
            $riderVehicleType = $riderProfile->vehicle_type ?? 'motorcycle';
            if ($request->filled('vehicle_type')) {
                $riderVehicleType = match($request->vehicle_type) {
                    'electric_bike' => 'scooter',
                    default => in_array($request->vehicle_type, ['motorcycle', 'scooter', 'bicycle', 'car', 'van', 'other']) ? $request->vehicle_type : 'motorcycle',
                };
            }

            $riderUpdates = array_filter([
                'vehicle_type' => $riderVehicleType,
                'vehicle_number' => $request->vehicle_number ?? $riderProfile->vehicle_number,
                'driving_license_number' => $request->driving_license_number ?? $riderProfile->driving_license_number,
                'license_expiry_date' => $request->license_expiry_date ?? $riderProfile->license_expiry_date,
                'cod_level' => $request->cod_level ?? $riderProfile->cod_level,
                'cod_limit' => $request->filled('cod_limit') ? $request->cod_limit : $riderProfile->cod_limit,
                'current_outstanding_cod' => $request->filled('current_outstanding_cod') ? $request->current_outstanding_cod : $riderProfile->current_outstanding_cod,
                'trust_score' => $request->filled('trust_score') ? $request->trust_score : $riderProfile->trust_score,
                'badge_status' => $request->badge_status ?? $riderProfile->badge_status,
                'rating' => $request->filled('rating') ? $request->rating : $riderProfile->rating,
                'availability_status' => $request->availability_status ?? $riderProfile->availability_status,
                'service_radius_km' => $request->filled('service_radius_km') ? $request->service_radius_km : $riderProfile->service_radius_km,
                'max_carrying_weight' => $request->filled('max_carrying_weight') ? $request->max_carrying_weight : $riderProfile->max_carrying_weight,
                'max_active_packages' => $request->filled('max_active_packages') ? $request->max_active_packages : $riderProfile->max_active_packages,
                'emergency_contact' => $request->emergency_contact ?? $riderProfile->emergency_contact,
                'affiliation' => $request->affiliation ?? $riderProfile->affiliation,
                'affiliation_reference_id' => $request->affiliation_reference_id ?? $riderProfile->affiliation_reference_id,
                'affiliation_notes' => $request->affiliation_notes ?? $riderProfile->affiliation_notes,
                'bank_name' => $request->bank_name ?? $riderProfile->bank_name,
                'bank_account_number' => $request->bank_account_number ?? $riderProfile->bank_account_number,
                'bank_account_name' => $request->bank_account_name ?? $riderProfile->bank_account_name,
                'esewa_id' => $request->esewa_id ?? $riderProfile->esewa_id,
                'khalti_id' => $request->khalti_id ?? $riderProfile->khalti_id,
                'payout_method' => $request->payout_method ?? $riderProfile->payout_method,
                'verification_status' => $request->verification_status === 'approved' ? 'verified' : ($request->verification_status === 'rejected' ? 'rejected' : ($request->verification_status === 'suspended' ? 'suspended' : 'pending')),
            ], fn($val) => !is_null($val));

            $riderProfile->update($riderUpdates);
        }

        // Sync DomesticPartner if user is partner
        if ($user->user_type === User::TYPE_PARTNER) {
            $partner = \App\Models\DomesticPartner::where('id', $user->id)
                ->orWhere('email', $user->email)
                ->first();

            if (!$partner) {
                $partner = new \App\Models\DomesticPartner();
                $partner->id = $user->id;
                $partner->code = $request->partner_code ?? ('PRT-' . str_pad($user->id, 4, '0', STR_PAD_LEFT));
                $partner->name = $user->name;
                $partner->company_name = $request->company_name ?? $user->name;
                $partner->email = $user->email;
                $partner->password = $user->password;
                $partner->phone = $request->phone ?? '9800000000';
                $partner->address = $user->permanent_address ?? 'Kathmandu Cargo Hub';
                $partner->city = $request->district ?? 'Kathmandu';
                $partner->district = $request->district ?? 'Kathmandu';
                $partner->province = $request->province ?? 'Bagmati';
                $partner->save();
            }

            $partnerServiceType = $partner->service_type ?? 'all';
            if ($request->filled('service_type')) {
                $partnerServiceType = match($request->service_type) {
                    'hub_to_hub_linehaul' => 'standard',
                    'last_mile_delivery' => 'same_day',
                    'full_route_network' => 'all',
                    default => in_array($request->service_type, ['flash', 'same_day', 'standard', 'himalayan', 'all']) ? $request->service_type : 'all',
                };
            }

            $partnerUpdates = array_filter([
                'company_name' => $request->company_name ?? $partner->company_name,
                'code' => $request->partner_code ?? $partner->code,
                'pan_number' => $request->pan_number ?? $partner->pan_number,
                'service_type' => $partnerServiceType,
                'margin_percentage' => $request->filled('margin_percentage') ? $request->margin_percentage : $partner->margin_percentage,
                'kyc_verified' => $request->has('kyc_verified') ? $request->boolean('kyc_verified') : ($request->verification_status === 'approved'),
                'province' => $request->province ?? $partner->province,
                'district' => $request->district ?? $partner->district,
                'city' => $request->district ?? $partner->city,
            ], fn($val) => !is_null($val));

            $partner->update($partnerUpdates);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "{$user->user_type_label} '{$user->name}' updated successfully!");
    }

    /**
     * Remove the specified admin user.
     */
    public function destroy(User $user)
    {
        $this->ensureCanManage($user);
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        // Prevent deleting the last admin
        if ($user->user_type === User::TYPE_SUPER_ADMIN && User::where('user_type', User::TYPE_SUPER_ADMIN)->count() <= 1) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete the last Super Administrator.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Admin user deleted successfully!');
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user)
    {
        $this->ensureCanManage($user);
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot change your own status.');
        }

        $newStatus = $user->verification_status === 'approved' ? 'suspended' : 'approved';
        $user->update(['verification_status' => $newStatus]);

        $message = $newStatus === 'approved' ? 'activated' : 'suspended';
        return redirect()->route('admin.users.index')
            ->with('success', "User {$message} successfully!");
    }

    /**
     * Show verify user page.
     * This shows the pending user details for approval
     */
    public function verify(User $user)
    {
        $this->ensureCanManage($user);
        // Allow verification for any pending user
        if ($user->verification_status !== 'pending') {
            return redirect()->route('admin.users.index')
                ->with('error', 'This user is not pending verification.');
        }
        
        return view('admin.users.verify', compact('user'));
    }

    /**
     * Approve a user (any type).
     */
    public function approve(User $user)
    {
        $this->ensureCanManage($user);
        $user->update([
            'verification_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'registration_completed' => true,
        ]);

        \Log::info('User approved', [
            'user_id' => $user->id,
            'user_type' => $user->user_type,
            'approved_by' => auth()->id()
        ]);

        return redirect()->route('admin.users.index', ['status' => 'pending'])
            ->with('success', "User {$user->name} has been approved successfully!");
    }

    /**
     * Reject a user (any type).
     */
    public function reject(Request $request, User $user)
    {
        $this->ensureCanManage($user);
        $request->validate([
            'rejection_reason' => 'required|string|min:10'
        ]);

        $user->update([
            'verification_status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        \Log::info('User rejected', [
            'user_id' => $user->id,
            'user_type' => $user->user_type,
            'rejected_by' => auth()->id(),
            'reason' => $request->rejection_reason
        ]);

        return redirect()->route('admin.users.index', ['status' => 'pending'])
            ->with('success', "User {$user->name} has been rejected.");
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $this->ensureCanManage($user);
        $request->validate([
            'password' => 'required|string|min:12|confirmed'
        ]);

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Password reset successfully!');
    }

    /**
     * Super administrators and legacy administrators may manage every normal
     * operational role. Domestic administrators are intentionally limited to
     * customer-facing and domestic delivery roles.
     */
    private function manageableUserTypes(): array
    {
        $all = [
            'admin' => 'Administrator',
            User::TYPE_STAFF => User::USER_TYPES[User::TYPE_STAFF],
            User::TYPE_DOMESTIC_ADMIN => User::USER_TYPES[User::TYPE_DOMESTIC_ADMIN],
            User::TYPE_INTERNATIONAL_ADMIN => User::USER_TYPES[User::TYPE_INTERNATIONAL_ADMIN],
            User::TYPE_SELLER => User::USER_TYPES[User::TYPE_SELLER],
            User::TYPE_RIDER => User::USER_TYPES[User::TYPE_RIDER],
            User::TYPE_PARTNER => User::USER_TYPES[User::TYPE_PARTNER],
            User::TYPE_OVERSEAS => User::USER_TYPES[User::TYPE_OVERSEAS],
            User::TYPE_CLIENT => User::USER_TYPES[User::TYPE_CLIENT],
            User::TYPE_CUSTOMER => User::USER_TYPES[User::TYPE_CUSTOMER],
        ];

        if (in_array(auth()->user()->user_type, [User::TYPE_SUPER_ADMIN, 'admin'], true)) {
            return $all;
        }

        return array_intersect_key($all, array_flip([
            User::TYPE_SELLER,
            User::TYPE_RIDER,
            User::TYPE_PARTNER,
            User::TYPE_CLIENT,
            User::TYPE_CUSTOMER,
        ]));
    }

    private function ensureCanManage(User $user): void
    {
        if ($user->is(auth()->user())) {
            return;
        }

        if (array_key_exists($user->user_type, $this->manageableUserTypes())) {
            return;
        }

        abort(403, 'You are not allowed to manage this account type.');
    }
}
