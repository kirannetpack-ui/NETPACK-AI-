@extends('layouts.app')

@section('title', 'Rider Settings & Fleet Dossier')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header & Badge Bar -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 text-white border border-teal-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30">
                    <i class="fas fa-id-badge text-teal-400"></i> Fleet ID: {{ $profile->rider_code }}
                </span>
                @if($profile->badge_status === 'preferred')
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-400 text-slate-950 uppercase tracking-wider">
                        ★ Preferred Rider
                    </span>
                @elseif($profile->badge_status === 'trusted')
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-400 text-slate-950 uppercase tracking-wider">
                        ✓ Trusted Rider
                    </span>
                @elseif($profile->is_verified)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-400 text-slate-950 uppercase tracking-wider">
                        Verified
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/30 text-amber-300 border border-amber-500/40 uppercase tracking-wider">
                        Pending KYC Verification
                    </span>
                @endif
            </div>
            <h1 class="text-xl md:text-2xl font-black text-white">{{ $rider->name }}</h1>
            <p class="text-xs text-slate-300">
                Independent Delivery Service Provider &bull; Affiliation: <span class="text-teal-300 font-semibold">{{ ucfirst($profile->affiliation ?? 'Independent') }}</span>
            </p>
        </div>

        <div class="flex items-center gap-4 bg-slate-950/60 p-3.5 rounded-xl border border-teal-700/60 text-xs">
            <div class="text-center px-2">
                <p class="text-[10px] uppercase font-bold text-slate-400">Trust Score</p>
                <p class="text-lg font-black text-teal-400 font-mono">{{ $profile->trust_score ?? 100 }}/100</p>
            </div>
            <div class="h-8 w-px bg-slate-800"></div>
            <div class="text-center px-2">
                <p class="text-[10px] uppercase font-bold text-slate-400">Rating</p>
                <p class="text-lg font-black text-amber-400 font-mono">{{ number_format($profile->rating ?? 5.0, 1) }} ★</p>
            </div>
            <div class="h-8 w-px bg-slate-800"></div>
            <div class="text-center px-2">
                <p class="text-[10px] uppercase font-bold text-slate-400">COD Limit</p>
                <p class="text-lg font-black text-emerald-400 font-mono">Rs. {{ number_format($profile->cod_limit) }}</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-400 text-emerald-800 px-4 py-3 rounded-xl text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-xl text-xs">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 1. Availability & Capacity Settings -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <h3 class="text-sm font-bold text-slate-900 border-b pb-2 mb-4 flex items-center gap-2">
            <i class="fas fa-toggle-on text-teal-600"></i> Availability & Carrying Capacity
        </h3>
        <form method="POST" action="{{ route('rider.update-availability') }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Live Status *</label>
                    <select name="availability_status" class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500 font-bold">
                        <option value="online" {{ ($profile->availability_status ?? 'online') === 'online' ? 'selected' : '' }}>🟢 ONLINE (Ready to deliver)</option>
                        <option value="busy" {{ ($profile->availability_status ?? '') === 'busy' ? 'selected' : '' }}>🟡 BUSY (On active delivery)</option>
                        <option value="offline" {{ ($profile->availability_status ?? '') === 'offline' ? 'selected' : '' }}>⚪ OFFLINE (Unavailable)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Service Radius (KM)</label>
                    <input type="number" step="0.5" name="service_radius_km" value="{{ old('service_radius_km', $profile->service_radius_km ?? 10) }}"
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Max Weight (KG)</label>
                    <input type="number" step="0.5" name="max_carrying_weight" value="{{ old('max_carrying_weight', $profile->max_carrying_weight ?? 15) }}"
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Max Active Parcels</label>
                    <input type="number" name="max_active_packages" value="{{ old('max_active_packages', $profile->max_active_packages ?? 5) }}"
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="submit" class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl transition">
                    Save Availability
                </button>
            </div>
        </form>
    </div>

    <!-- 2. Personal & Residence Profile -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <h3 class="text-sm font-bold text-slate-900 border-b pb-2 mb-4 flex items-center gap-2">
            <i class="fas fa-user text-teal-600"></i> Personal & Vehicle Details + KYC Documents
        </h3>

        <form method="POST" action="{{ route('rider.update-profile') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Full Legal Name *</label>
                    <input type="text" name="name" value="{{ old('name', $rider->name) }}" required
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Mobile Phone Number *</label>
                    <input type="tel" name="phone" value="{{ old('phone', $rider->phone) }}" required
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Emergency Contact (Phone & Name) *</label>
                    <input type="text" name="emergency_contact" value="{{ old('emergency_contact', $profile->emergency_contact) }}" placeholder="e.g. 98XXXXXXXX (Father/Spouse)"
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Citizenship Number</label>
                    <input type="text" name="citizenship_number" value="{{ old('citizenship_number', $profile->citizenship_number) }}"
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Street Address</label>
                    <input type="text" name="address" value="{{ old('address', $rider->address) }}"
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Municipality / Rural Municipality</label>
                    <input type="text" name="municipality" value="{{ old('municipality', $profile->municipality) }}" placeholder="e.g. Kathmandu Metropolitan City"
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Ward Number</label>
                    <input type="text" name="ward" value="{{ old('ward', $profile->ward) }}" placeholder="e.g. 10"
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">District / City</label>
                    <input type="text" name="district" value="{{ old('district', $rider->district ?? 'Kathmandu') }}"
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                </div>
            </div>

            <!-- Vehicle & License Sub-section -->
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-3">Vehicle & License Specifications</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Vehicle Type *</label>
                        <select name="vehicle_type" required class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                            <option value="motorcycle" {{ old('vehicle_type', $profile->vehicle_type) === 'motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                            <option value="scooter" {{ old('vehicle_type', $profile->vehicle_type) === 'scooter' ? 'selected' : '' }}>Scooter</option>
                            <option value="bicycle" {{ old('vehicle_type', $profile->vehicle_type) === 'bicycle' ? 'selected' : '' }}>Bicycle</option>
                            <option value="car" {{ old('vehicle_type', $profile->vehicle_type) === 'car' ? 'selected' : '' }}>Car</option>
                            <option value="van" {{ old('vehicle_type', $profile->vehicle_type) === 'van' ? 'selected' : '' }}>Van</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Vehicle Plate / Registration No. *</label>
                        <input type="text" name="vehicle_number" value="{{ old('vehicle_number', $profile->vehicle_number ?? $rider->vehicle_registration_number) }}" required
                               class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Driving License Number *</label>
                        <input type="text" name="license_number" value="{{ old('license_number', $profile->driving_license_number ?? $rider->license_number) }}" required
                               class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">License Expiry Date</label>
                        <input type="date" name="license_expiry_date" value="{{ old('license_expiry_date', $profile->license_expiry_date ? $profile->license_expiry_date->format('Y-m-d') : '') }}"
                               class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                    </div>
                </div>
            </div>

            <!-- KYC Document Uploads & Previews -->
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-3">Verification & KYC Documents</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                        <label class="block font-bold text-slate-700 mb-1">Driving License Photo / PDF</label>
                        <input type="file" name="driving_license_doc" accept="image/*,.pdf"
                               class="w-full text-xs border border-slate-200 rounded-lg p-1.5 bg-white file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-teal-600 file:text-white">
                        @if($profile->driving_license_doc_path)
                            <p class="text-[10px] text-emerald-600 font-bold mt-1">✓ Document currently on file</p>
                        @endif
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                        <label class="block font-bold text-slate-700 mb-1">Vehicle Registration / Bluebook</label>
                        <input type="file" name="vehicle_registration_doc" accept="image/*,.pdf"
                               class="w-full text-xs border border-slate-200 rounded-lg p-1.5 bg-white file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-teal-600 file:text-white">
                        @if($profile->vehicle_registration_doc_path)
                            <p class="text-[10px] text-emerald-600 font-bold mt-1">✓ Document currently on file</p>
                        @endif
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                        <label class="block font-bold text-slate-700 mb-1">Citizenship (Front)</label>
                        <input type="file" name="citizenship_front" accept="image/*,.pdf"
                               class="w-full text-xs border border-slate-200 rounded-lg p-1.5 bg-white file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-teal-600 file:text-white">
                        @if($profile->citizenship_front_path)
                            <p class="text-[10px] text-emerald-600 font-bold mt-1">✓ Document currently on file</p>
                        @endif
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                        <label class="block font-bold text-slate-700 mb-1">Citizenship (Back)</label>
                        <input type="file" name="citizenship_back" accept="image/*,.pdf"
                               class="w-full text-xs border border-slate-200 rounded-lg p-1.5 bg-white file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-teal-600 file:text-white">
                        @if($profile->citizenship_back_path)
                            <p class="text-[10px] text-emerald-600 font-bold mt-1">✓ Document currently on file</p>
                        @endif
                    </div>
                    <div class="md:col-span-2 p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                        <label class="block font-bold text-slate-700 mb-1">Rider Selfie / Profile Photo</label>
                        <input type="file" name="selfie_photo" accept="image/*"
                               class="w-full text-xs border border-slate-200 rounded-lg p-1.5 bg-white file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-teal-600 file:text-white">
                        @if($profile->selfie_photo_path)
                            <p class="text-[10px] text-emerald-600 font-bold mt-1">✓ Profile photo verified</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl transition shadow-sm">
                    <i class="fas fa-save mr-1.5"></i> Update Profile & Documents
                </button>
            </div>
        </form>
    </div>

    <!-- 3. Bank & Digital Remittance Settings -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <h3 class="text-sm font-bold text-slate-900 border-b pb-2 mb-4 flex items-center gap-2">
            <i class="fas fa-building-columns text-teal-600"></i> Payout & Bank Remittance Details
        </h3>
        <form method="POST" action="{{ route('rider.update-bank') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $profile->bank_name ?? $rider->wallet->bank_name ?? '') }}" placeholder="e.g. Nabil Bank Ltd"
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Account Number</label>
                    <input type="text" name="account_number" value="{{ old('account_number', $profile->bank_account_number ?? $rider->wallet->bank_account_number ?? '') }}" placeholder="e.g. 01234567890"
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Account Holder Name</label>
                    <input type="text" name="account_holder_name" value="{{ old('account_holder_name', $profile->bank_account_name ?? $rider->wallet->bank_account_name ?? $rider->name) }}"
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">eSewa ID (Optional)</label>
                    <input type="text" name="esewa_id" value="{{ old('esewa_id', $profile->esewa_id) }}" placeholder="e.g. 98XXXXXXXX"
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Khalti ID (Optional)</label>
                    <input type="text" name="khalti_id" value="{{ old('khalti_id', $profile->khalti_id) }}" placeholder="e.g. 98XXXXXXXX"
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500 font-mono">
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl transition">
                    Save Payout Details
                </button>
            </div>
        </form>
    </div>

    <!-- 4. Password Security -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <h3 class="text-sm font-bold text-slate-900 border-b pb-2 mb-4 flex items-center gap-2">
            <i class="fas fa-lock text-teal-600"></i> Change Account Password
        </h3>
        <form method="POST" action="{{ route('rider.update-password') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Current Password *</label>
                    <input type="password" name="current_password" required
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">New Password *</label>
                    <input type="password" name="new_password" required
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Confirm New Password *</label>
                    <input type="password" name="new_password_confirmation" required
                           class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-teal-500">
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl transition">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>
@endsection