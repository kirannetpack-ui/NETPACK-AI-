@extends('layouts.app')

@section('title', 'Edit User - ' . $user->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6"
     x-data="{
        activeTab: '{{ $errors->any() ? 'functionality' : 'functionality' }}',
        selectedRole: '{{ old('user_type', $user->user_type) }}',

        // Rider reactive state
        trustScore: {{ (int) old('trust_score', $riderProfile?->trust_score ?? 100) }},
        codLevel: '{{ old('cod_level', $riderProfile?->cod_level ?? 'level_1') }}',
        codLimit: '{{ old('cod_limit', $riderProfile?->cod_limit ?? 5000) }}',
        vehicleType: '{{ old('vehicle_type', $riderProfile?->vehicle_type ?? 'motorcycle') }}',
        payoutMethod: '{{ old('payout_method', $riderProfile?->payout_method ?? 'bank') }}',
        serviceRadius: '{{ old('service_radius_km', $riderProfile?->service_radius_km ?? 15) }}',

        // Seller reactive state
        settlementCycle: '{{ old('settlement_cycle', $metadata['settlement_cycle'] ?? 'weekly_monday') }}',
        sellerPayoutType: '{{ old('seller_payout_type', !empty($metadata['esewa_id']) ? 'esewa' : (!empty($metadata['khalti_id']) ? 'khalti' : 'bank')) }}',

        // Client reactive state
        clientCreditLimit: '{{ old('credit_limit', $metadata['credit_limit'] ?? 50000) }}',

        // Helpers
        getTrustBadge() {
            if (this.trustScore >= 90) return { text: 'Preferred Provider', class: 'bg-emerald-500 text-white' };
            if (this.trustScore >= 75) return { text: 'Trusted Provider', class: 'bg-blue-500 text-white' };
            if (this.trustScore >= 50) return { text: 'Verified Provider', class: 'bg-teal-500 text-white' };
            if (this.trustScore >= 25) return { text: 'Probationary', class: 'bg-amber-500 text-white' };
            return { text: 'High Risk / Review', class: 'bg-rose-500 text-white' };
        },

        setCodLimit(amount, level) {
            this.codLimit = amount;
            if (level) this.codLevel = level;
        }
     }">

    <!-- Breadcrumb Navigation -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 mb-4">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-teal-600 transition flex items-center gap-1">
            <i class="fas fa-gauge"></i> Dashboard
        </a>
        <span>/</span>
        <a href="{{ route('admin.users.index') }}" class="hover:text-teal-600 transition">User Management</a>
        <span>/</span>
        <span class="text-slate-800 font-semibold truncate max-w-xs">{{ $user->name }}</span>
    </nav>

    <!-- Executive User Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 border border-slate-800 shadow-xl mb-6 text-white p-6 sm:p-8">
        <div class="absolute -right-12 -top-12 w-64 h-64 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-12 -bottom-12 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-5">
                <!-- Avatar with Verification Ring -->
                <div class="relative shrink-0">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-gradient-to-tr from-teal-500 to-indigo-600 p-0.5 shadow-lg">
                        <div class="w-full h-full bg-slate-900 rounded-[14px] flex items-center justify-center overflow-hidden">
                            @if($user->profile_photo)
                                <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-2xl sm:text-3xl font-black text-teal-400 tracking-wider">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <span class="absolute -bottom-1.5 -right-1.5 p-1 rounded-full text-xs shadow-md
                        {{ $user->verification_status === 'approved' ? 'bg-emerald-500 text-white' :
                           ($user->verification_status === 'pending' ? 'bg-amber-500 text-white' : 'bg-rose-500 text-white') }}"
                          title="Status: {{ ucfirst($user->verification_status) }}">
                        <i class="fas {{ $user->verification_status === 'approved' ? 'fa-check' : ($user->verification_status === 'pending' ? 'fa-clock' : 'fa-ban') }} px-1"></i>
                    </span>
                </div>

                <!-- User Meta & Badges -->
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">{{ $user->name }}</h1>
                        <span class="px-3 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30">
                            {{ $user->user_type_label ?? ucfirst($user->user_type) }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $user->verification_status === 'approved' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' :
                               ($user->verification_status === 'pending' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30') }}">
                            {{ strtoupper($user->verification_status) }}
                        </span>
                    </div>

                    <div class="mt-2 flex items-center gap-4 text-xs sm:text-sm text-slate-300 flex-wrap">
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-envelope text-teal-400"></i> {{ $user->email }}
                        </span>
                        <span class="hidden sm:inline text-slate-600">•</span>
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-phone text-teal-400"></i> {{ $user->phone ?? 'Unset Mobile' }}
                        </span>
                        <span class="hidden sm:inline text-slate-600">•</span>
                        <span class="flex items-center gap-1.5 text-slate-400">
                            <i class="fas fa-calendar-check text-slate-400"></i> Member since {{ $user->created_at->format('M d, Y') }}
                        </span>
                        @if($user->province || $user->district)
                            <span class="hidden sm:inline text-slate-600">•</span>
                            <span class="flex items-center gap-1.5 text-teal-300">
                                <i class="fas fa-location-dot"></i> {{ $user->district ?? $user->province }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Header Quick Actions -->
            <div class="flex items-center gap-3 shrink-0">
                @if($user->verification_status === 'pending')
                    <a href="{{ route('admin.users.verify', $user->id) }}"
                       class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-medium text-xs sm:text-sm transition flex items-center gap-2 shadow-lg shadow-amber-500/20">
                        <i class="fas fa-user-check"></i> Pending Verification
                    </a>
                @endif
                <a href="{{ route('admin.users.show', $user->id) }}"
                   class="px-4 py-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 hover:text-white font-medium text-xs sm:text-sm border border-slate-700 transition flex items-center gap-2">
                    <i class="fas fa-eye"></i> View Profile
                </a>
                <a href="{{ route('admin.users.index') }}"
                   class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-medium text-xs sm:text-sm transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> All Users
                </a>
            </div>
        </div>
    </div>

    <!-- Edit User Form Container -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

        <!-- Form Header Description -->
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
            <div>
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i class="fas fa-user-gear text-teal-600"></i> Edit User Account
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Configure operational capacity, risk controls, credentials, and role-specific functional parameters.
                </p>
            </div>

            <!-- Tab Navigation Bar -->
            <div class="flex items-center bg-slate-200/70 p-1 rounded-xl gap-1 shrink-0 text-xs font-medium">
                <button type="button"
                        @click="activeTab = 'functionality'"
                        :class="activeTab === 'functionality' ? 'bg-white text-teal-700 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3.5 py-1.5 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-cogs"></i>
                    <span>Functionality Console</span>
                    <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                </button>
                <button type="button"
                        @click="activeTab = 'identity'"
                        :class="activeTab === 'identity' ? 'bg-white text-teal-700 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3.5 py-1.5 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-id-card"></i>
                    <span>Account & Identity</span>
                </button>
                <button type="button"
                        @click="activeTab = 'scope'"
                        :class="activeTab === 'scope' ? 'bg-white text-teal-700 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3.5 py-1.5 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-shield-alt"></i>
                    <span>Role & Scope</span>
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.users.update', $user->id) }}" id="userEditForm">
            @csrf
            @method('PUT')

            <!-- Global Validation Alerts -->
            @if($errors->any())
                <div class="m-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800">
                    <div class="flex items-center gap-2 font-bold text-sm mb-2">
                        <i class="fas fa-triangle-exclamation text-rose-600"></i>
                        <span>Validation errors found. Please review the highlighted fields:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1 text-rose-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- TAB 1: FUNCTIONALITY CONSOLE (Role-Adaptive) -->
            <div x-show="activeTab === 'functionality'" class="p-6 sm:p-8 space-y-8">

                <!-- Functionality Module Indicator Banner -->
                <div class="p-4 rounded-xl bg-gradient-to-r from-teal-50 to-indigo-50 border border-teal-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center text-lg shadow-sm">
                            <template x-if="selectedRole === 'rider'">
                                <i class="fas fa-motorcycle"></i>
                            </template>
                            <template x-if="selectedRole === 'seller'">
                                <i class="fas fa-store"></i>
                            </template>
                            <template x-if="selectedRole === 'partner'">
                                <i class="fas fa-truck-fast"></i>
                            </template>
                            <template x-if="selectedRole === 'client' || selectedRole === 'customer'">
                                <i class="fas fa-building"></i>
                            </template>
                            <template x-if="['staff', 'admin', 'super_admin', 'domestic_admin', 'international_admin'].includes(selectedRole)">
                                <i class="fas fa-user-shield"></i>
                            </template>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-slate-900 text-sm sm:text-base">
                                    <span x-text="selectedRole === 'rider' ? 'Independent Delivery Rider Console' :
                                                 (selectedRole === 'seller' ? 'E-Commerce Merchant Logistics Console' :
                                                 (selectedRole === 'partner' ? 'Domestic Linehaul Carrier Console' :
                                                 (selectedRole === 'client' || selectedRole === 'customer' ? 'Corporate Client & Shipper Console' : 'Operations Staff & Hub Controller Console')))">
                                    </span>
                                </h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-600 text-white uppercase tracking-wider">
                                    Active Mode
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 mt-0.5">
                                Specialized operational rules, risk headroom, fleet telemetry, and commercial settlement configuration.
                            </p>
                        </div>
                    </div>

                    <!-- Role Switcher Shortcut -->
                    <div class="flex items-center gap-2 shrink-0">
                        <label class="text-xs font-semibold text-slate-700">Configuring Role:</label>
                        <select x-model="selectedRole"
                                class="bg-white border border-slate-300 text-slate-800 text-xs font-semibold rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            @foreach($manageableUserTypes as $type => $label)
                                <option value="{{ $type }}" {{ old('user_type', $user->user_type) === $type ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="user_type" :value="selectedRole">
                    </div>
                </div>

                <!-- 1. RIDER FUNCTIONALITY COMPONENT -->
                <div x-show="selectedRole === 'rider'" class="space-y-6">

                    <!-- Rider Telemetry Strip -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="text-xs font-medium text-slate-500 block">Trust Score</span>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xl font-extrabold text-slate-900" x-text="trustScore + '/100'"></span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold" :class="getTrustBadge().class" x-text="getTrustBadge().text"></span>
                            </div>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="text-xs font-medium text-slate-500 block">Active Rating</span>
                            <div class="flex items-center gap-1.5 mt-1 text-amber-500 font-extrabold text-xl">
                                <i class="fas fa-star text-sm"></i>
                                <span class="text-slate-900">{{ number_format($riderProfile?->rating ?? 5.0, 1) }}</span>
                                <span class="text-xs text-slate-400 font-normal">/ 5.0</span>
                            </div>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="text-xs font-medium text-slate-500 block">Current COD Liability</span>
                            <div class="mt-1">
                                <span class="text-xl font-extrabold text-slate-900">Rs. {{ number_format($riderProfile?->current_outstanding_cod ?? 0, 0) }}</span>
                                <span class="text-[10px] text-slate-500 block">of Rs. <span x-text="Number(codLimit).toLocaleString()"></span> Limit</span>
                            </div>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="text-xs font-medium text-slate-500 block">Fleet Availability</span>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="w-2.5 h-2.5 rounded-full {{ ($riderProfile?->availability_status ?? 'online') === 'online' ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                <span class="text-sm font-bold text-slate-800 capitalize">{{ $riderProfile?->availability_status ?? 'online' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Fleet Dossier & Vehicle Attributes -->
                    <div class="border border-slate-200 rounded-xl p-5 bg-white">
                        <h4 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-id-badge text-teal-600"></i> Fleet & Vehicle Specifications
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Vehicle Type *</label>
                                <select name="vehicle_type" x-model="vehicleType" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white">
                                    <option value="motorcycle">🏍️ Motorcycle</option>
                                    <option value="scooter">🛵 Scooter</option>
                                    <option value="electric_bike">⚡ Electric Scooter / Bike</option>
                                    <option value="bicycle">🚲 Bicycle</option>
                                    <option value="car">🚗 Car / Micro-Van</option>
                                    <option value="van">🚐 Cargo Van</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Vehicle Plate Number *</label>
                                <input type="text" name="vehicle_number" value="{{ old('vehicle_number', $riderProfile?->vehicle_number ?? $user->vehicle_number ?? 'BA-99-PA-1234') }}"
                                       placeholder="e.g. BA 99 PA 1234" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Driving License Number *</label>
                                <input type="text" name="driving_license_number" value="{{ old('driving_license_number', $riderProfile?->driving_license_number ?? '01-06-00001234') }}"
                                       placeholder="e.g. 01-06-00001234" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">License Expiry Date</label>
                                <input type="date" name="license_expiry_date" value="{{ old('license_expiry_date', $riderProfile?->license_expiry_date ? \Carbon\Carbon::parse($riderProfile?->license_expiry_date)->format('Y-m-d') : '') }}"
                                       class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 pt-4 border-t border-slate-100">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Platform Affiliation (Informational)</label>
                                <select name="affiliation" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white">
                                    <option value="independent" {{ old('affiliation', $riderProfile?->affiliation ?? 'independent') === 'independent' ? 'selected' : '' }}>Autonomous / Independent Provider</option>
                                    <option value="pathao" {{ old('affiliation', $riderProfile?->affiliation ?? '') === 'pathao' ? 'selected' : '' }}>Pathao Fleet (Registered Individually)</option>
                                    <option value="indrive" {{ old('affiliation', $riderProfile?->affiliation ?? '') === 'indrive' ? 'selected' : '' }}>inDrive Partner (Registered Individually)</option>
                                    <option value="parcel" {{ old('affiliation', $riderProfile?->affiliation ?? '') === 'parcel' ? 'selected' : '' }}>Parcel Rider (Registered Individually)</option>
                                    <option value="other" {{ old('affiliation', $riderProfile?->affiliation ?? '') === 'other' ? 'selected' : '' }}>Other Third-Party Network</option>
                                </select>
                                <p class="text-[10px] text-slate-400 mt-1">Platform treats rider as autonomous; external affiliation is purely informational.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Affiliation ID / Tag</label>
                                <input type="text" name="affiliation_reference_id" value="{{ old('affiliation_reference_id', $riderProfile?->affiliation_reference_id ?? '') }}"
                                       placeholder="e.g. PTH-998822" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Emergency Contact (Name & Phone)</label>
                                <input type="text" name="emergency_contact" value="{{ old('emergency_contact', $riderProfile?->emergency_contact ?? $user->emergency_contact ?? '') }}"
                                       placeholder="e.g. Sita Shrestha (9841000000)" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>
                    </div>

                    <!-- Fiduciary COD Risk Controls & Limits -->
                    <div class="border border-slate-200 rounded-xl p-5 bg-white">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <i class="fas fa-sack-dollar text-teal-600"></i> COD Exposure & Fiduciary Risk Limit
                                </h4>
                                <p class="text-xs text-slate-500">Limits cash parcel value in rider's custody before requiring bank/hub deposit.</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                Current Limit: Rs. <span x-text="Number(codLimit).toLocaleString()"></span>
                            </span>
                        </div>

                        <!-- Quick-Fill Preset Chips -->
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-slate-600 mb-2">Quick Selective Presets:</label>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="setCodLimit(5000, 'level_1')"
                                        :class="codLimit == 5000 ? 'bg-teal-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="px-3 py-1.5 rounded-lg text-xs transition border border-transparent">
                                    Level 1: Rs. 5,000 (New Rider)
                                </button>
                                <button type="button" @click="setCodLimit(15000, 'level_2')"
                                        :class="codLimit == 15000 ? 'bg-teal-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="px-3 py-1.5 rounded-lg text-xs transition border border-transparent">
                                    Level 2: Rs. 15,000 (Verified)
                                </button>
                                <button type="button" @click="setCodLimit(30000, 'level_3')"
                                        :class="codLimit == 30000 ? 'bg-teal-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="px-3 py-1.5 rounded-lg text-xs transition border border-transparent">
                                    Level 3: Rs. 30,000 (Trusted)
                                </button>
                                <button type="button" @click="setCodLimit(50000, 'level_4')"
                                        :class="codLimit == 50000 ? 'bg-teal-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="px-3 py-1.5 rounded-lg text-xs transition border border-transparent">
                                    Level 4: Rs. 50,000 (High Volume)
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">COD Risk Tier *</label>
                                <select name="cod_level" x-model="codLevel" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white">
                                    <option value="level_1">Tier 1 (Rs. 5,000 Standard)</option>
                                    <option value="level_2">Tier 2 (Rs. 15,000 Intermediate)</option>
                                    <option value="level_3">Tier 3 (Rs. 30,000 Advanced)</option>
                                    <option value="level_4">Tier 4 (Rs. 50,000 Elite)</option>
                                    <option value="custom">Custom Super-Admin Authorization</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Custom / Authorized COD Limit (NPR) *</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-xs font-bold text-slate-400">Rs.</span>
                                    <input type="number" step="500" name="cod_limit" x-model="codLimit"
                                           class="w-full border rounded-lg pl-9 pr-3 py-2 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-teal-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Outstanding COD Reconciliation Balance</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-xs font-bold text-slate-400">Rs.</span>
                                    <input type="number" step="0.01" name="current_outstanding_cod" value="{{ old('current_outstanding_cod', $riderProfile?->current_outstanding_cod ?? 0) }}"
                                           class="w-full border rounded-lg pl-9 pr-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dispatch, Trust & Capacity Telemetry -->
                    <div class="border border-slate-200 rounded-xl p-5 bg-white">
                        <h4 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-gauge-high text-teal-600"></i> Dispatch Capacity & Trust Scoring Engine
                        </h4>

                        <!-- Trust Score Interactive Slider -->
                        <div class="mb-5 p-4 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                    <i class="fas fa-shield-halved text-teal-600"></i> Dynamic Trust Score (0–100)
                                </label>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-black text-slate-900" x-text="trustScore + ' / 100'"></span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="getTrustBadge().class" x-text="getTrustBadge().text"></span>
                                </div>
                            </div>
                            <input type="range" min="0" max="100" name="trust_score" x-model="trustScore"
                                   class="w-full accent-teal-600 cursor-pointer">
                            <div class="flex justify-between text-[10px] text-slate-400 mt-1 font-medium">
                                <span>0 (Suspended)</span>
                                <span>25 (Probation)</span>
                                <span>50 (Verified)</span>
                                <span>75 (Trusted)</span>
                                <span>100 (Preferred)</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Tier Badge *</label>
                                <select name="badge_status" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white">
                                    <option value="new" {{ old('badge_status', $riderProfile?->badge_status ?? 'new') === 'new' ? 'selected' : '' }}>New Rider</option>
                                    <option value="verified" {{ old('badge_status', $riderProfile?->badge_status ?? '') === 'verified' ? 'selected' : '' }}>Verified Badge</option>
                                    <option value="trusted" {{ old('badge_status', $riderProfile?->badge_status ?? '') === 'trusted' ? 'selected' : '' }}>Trusted Rider</option>
                                    <option value="preferred" {{ old('badge_status', $riderProfile?->badge_status ?? '') === 'preferred' ? 'selected' : '' }}>Preferred Provider</option>
                                    <option value="suspended" {{ old('badge_status', $riderProfile?->badge_status ?? '') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Operating Service Radius (KM)</label>
                                <input type="number" step="0.5" name="service_radius_km" x-model="serviceRadius"
                                       class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Max Active Packages</label>
                                <input type="number" name="max_active_packages" value="{{ old('max_active_packages', $riderProfile?->max_active_packages ?? 10) }}"
                                       class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Max Carrying Weight (KG)</label>
                                <input type="number" step="0.5" name="max_carrying_weight" value="{{ old('max_carrying_weight', $riderProfile?->max_carrying_weight ?? 25.0) }}"
                                       class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>
                    </div>

                    <!-- Remittance & Payout Destination -->
                    <div class="border border-slate-200 rounded-xl p-5 bg-white">
                        <h4 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                            <i class="fas fa-building-columns text-teal-600"></i> Payout Remittance Destination
                        </h4>

                        <!-- Selective Mode Toggles -->
                        <div class="flex gap-2 mb-4">
                            <button type="button" @click="payoutMethod = 'bank'"
                                    :class="payoutMethod === 'bank' ? 'bg-teal-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="px-4 py-2 rounded-lg text-xs transition flex items-center gap-2">
                                <i class="fas fa-bank"></i> Commercial Bank
                            </button>
                            <button type="button" @click="payoutMethod = 'esewa'"
                                    :class="payoutMethod === 'esewa' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="px-4 py-2 rounded-lg text-xs transition flex items-center gap-2">
                                <i class="fas fa-wallet text-green-300"></i> eSewa Wallet
                            </button>
                            <button type="button" @click="payoutMethod = 'khalti'"
                                    :class="payoutMethod === 'khalti' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="px-4 py-2 rounded-lg text-xs transition flex items-center gap-2">
                                <i class="fas fa-mobile-screen text-purple-300"></i> Khalti Wallet
                            </button>
                            <input type="hidden" name="payout_method" :value="payoutMethod">
                        </div>

                        <!-- Dynamic Remittance Fields -->
                        <div x-show="payoutMethod === 'bank'" class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Bank Name</label>
                                <input type="text" name="bank_name" value="{{ old('bank_name', $riderProfile?->bank_name ?? $user->bank_name ?? '') }}"
                                       placeholder="e.g. Nabil Bank" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Account Holder Name</label>
                                <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $riderProfile?->bank_account_name ?? $user->account_holder_name ?? $user->name) }}"
                                       placeholder="Full Name as on Bank" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Account Number</label>
                                <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $riderProfile?->bank_account_number ?? $user->account_number ?? '') }}"
                                       placeholder="Account Number" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white">
                            </div>
                        </div>

                        <div x-show="payoutMethod === 'esewa'" class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-200">
                            <label class="block text-xs font-semibold text-emerald-900 mb-1">eSewa Mobile ID / Number *</label>
                            <div class="max-w-md relative">
                                <span class="absolute left-3 top-2 text-xs font-bold text-emerald-600"><i class="fas fa-wallet"></i></span>
                                <input type="text" name="esewa_id" value="{{ old('esewa_id', $riderProfile?->esewa_id ?? $metadata['esewa_id'] ?? '') }}"
                                       placeholder="e.g. 9841XXXXXX" class="w-full border border-emerald-300 rounded-lg pl-9 pr-3 py-2 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                            </div>
                        </div>

                        <div x-show="payoutMethod === 'khalti'" class="p-4 rounded-xl bg-purple-50/50 border border-purple-200">
                            <label class="block text-xs font-semibold text-purple-900 mb-1">Khalti Mobile ID / Number *</label>
                            <div class="max-w-md relative">
                                <span class="absolute left-3 top-2 text-xs font-bold text-purple-600"><i class="fas fa-mobile-screen"></i></span>
                                <input type="text" name="khalti_id" value="{{ old('khalti_id', $riderProfile?->khalti_id ?? $metadata['khalti_id'] ?? '') }}"
                                       placeholder="e.g. 9801XXXXXX" class="w-full border border-purple-300 rounded-lg pl-9 pr-3 py-2 text-xs focus:ring-2 focus:ring-purple-500 bg-white">
                            </div>
                        </div>
                    </div>

                    <!-- KYC Document Audit Dossier -->
                    @if($riderProfile)
                    <div class="border border-slate-200 rounded-xl p-5 bg-white">
                        <h4 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                            <i class="fas fa-file-shield text-teal-600"></i> KYC Document Verification Dossier
                        </h4>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="p-3 rounded-lg border border-slate-100 bg-slate-50 text-center">
                                <span class="text-[11px] font-semibold text-slate-700 block mb-1">Driving License</span>
                                @if($riderProfile?->driving_license_doc_path)
                                    <a href="{{ route('rider.documents.show', ['rider' => $riderProfile->id, 'field' => 'driving_license_doc_path']) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-xs text-teal-600 hover:text-teal-700 font-bold">
                                        <i class="fas fa-arrow-up-right-from-square text-[10px]"></i> View File
                                    </a>
                                @else
                                    <span class="text-[10px] text-slate-400">Not Uploaded</span>
                                @endif
                            </div>
                            <div class="p-3 rounded-lg border border-slate-100 bg-slate-50 text-center">
                                <span class="text-[11px] font-semibold text-slate-700 block mb-1">Vehicle Bluebook</span>
                                @if($riderProfile?->vehicle_registration_doc_path)
                                    <a href="{{ route('rider.documents.show', ['rider' => $riderProfile->id, 'field' => 'vehicle_registration_doc_path']) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-xs text-teal-600 hover:text-teal-700 font-bold">
                                        <i class="fas fa-arrow-up-right-from-square text-[10px]"></i> View File
                                    </a>
                                @else
                                    <span class="text-[10px] text-slate-400">Not Uploaded</span>
                                @endif
                            </div>
                            <div class="p-3 rounded-lg border border-slate-100 bg-slate-50 text-center">
                                <span class="text-[11px] font-semibold text-slate-700 block mb-1">Citizenship (Front/Back)</span>
                                @if($riderProfile?->citizenship_front_path)
                                    <a href="{{ route('rider.documents.show', ['rider' => $riderProfile->id, 'field' => 'citizenship_front_path']) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-xs text-teal-600 hover:text-teal-700 font-bold">
                                        <i class="fas fa-arrow-up-right-from-square text-[10px]"></i> View File
                                    </a>
                                @else
                                    <span class="text-[10px] text-slate-400">Not Uploaded</span>
                                @endif
                            </div>
                            <div class="p-3 rounded-lg border border-slate-100 bg-slate-50 text-center">
                                <span class="text-[11px] font-semibold text-slate-700 block mb-1">Live Selfie Photo</span>
                                @if($riderProfile?->selfie_photo_path)
                                    <a href="{{ route('rider.documents.show', ['rider' => $riderProfile->id, 'field' => 'selfie_photo_path']) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-xs text-teal-600 hover:text-teal-700 font-bold">
                                        <i class="fas fa-arrow-up-right-from-square text-[10px]"></i> View File
                                    </a>
                                @else
                                    <span class="text-[10px] text-slate-400">Not Uploaded</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- 2. SELLER / MERCHANT FUNCTIONALITY COMPONENT -->
                <div x-show="selectedRole === 'seller'" class="space-y-6">
                    <div class="border border-slate-200 rounded-xl p-5 bg-white">
                        <h4 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-store text-teal-600"></i> Merchant Store & Entity Profile
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Store / Business Name *</label>
                                <input type="text" name="business_name" value="{{ old('business_name', $user->business_name ?? $user->company_name ?? '') }}"
                                       placeholder="e.g. Kathmandu Apparel D2C" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Legal Registered Entity Name</label>
                                <input type="text" name="company_name" value="{{ old('company_name', $user->company_name ?? '') }}"
                                       placeholder="e.g. Kathmandu Apparel Pvt. Ltd." class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">PAN / VAT Registration Number</label>
                                <input type="text" name="pan_number" value="{{ old('pan_number', $metadata['pan_number'] ?? '') }}"
                                       placeholder="e.g. 601234567" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 pt-4 border-t border-slate-100">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Merchant Category</label>
                                <select name="merchant_category" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white">
                                    <option value="fashion" {{ old('merchant_category', $metadata['merchant_category'] ?? '') === 'fashion' ? 'selected' : '' }}>Fashion & Apparel</option>
                                    <option value="electronics" {{ old('merchant_category', $metadata['merchant_category'] ?? '') === 'electronics' ? 'selected' : '' }}>Consumer Electronics</option>
                                    <option value="fmcg_grocery" {{ old('merchant_category', $metadata['merchant_category'] ?? '') === 'fmcg_grocery' ? 'selected' : '' }}>FMCG & Groceries</option>
                                    <option value="health_beauty" {{ old('merchant_category', $metadata['merchant_category'] ?? '') === 'health_beauty' ? 'selected' : '' }}>Health & Cosmetics</option>
                                    <option value="general" {{ old('merchant_category', $metadata['merchant_category'] ?? 'general') === 'general' ? 'selected' : '' }}>General Merchandise</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Primary Contact Person</label>
                                <input type="text" name="contact_person" value="{{ old('contact_person', $user->contact_person ?? '') }}"
                                       placeholder="Operations Manager Name" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Agreed Commission / Platform Margin (%)</label>
                                <input type="number" step="0.1" name="commission_rate" value="{{ old('commission_rate', $metadata['commission_rate'] ?? 3.5) }}"
                                       class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>
                    </div>

                    <!-- Warehouse & Dispatch Locations -->
                    <div class="border border-slate-200 rounded-xl p-5 bg-white">
                        <h4 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-warehouse text-teal-600"></i> Dispatch Depots & Return Logistics
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Default Warehouse / Pickup Address *</label>
                                <input type="text" name="pickup_address" value="{{ old('pickup_address', $metadata['pickup_address'] ?? $user->business_address ?? '') }}"
                                       placeholder="e.g. New Road Wholesale Building, Floor 2, Kathmandu" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Return-to-Origin (RTO) Address</label>
                                <input type="text" name="return_address" value="{{ old('return_address', $metadata['return_address'] ?? $metadata['pickup_address'] ?? '') }}"
                                       placeholder="Address where rejected/undelivered parcels return" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>
                    </div>

                    <!-- Settlement Cycle & Remittance -->
                    <div class="border border-slate-200 rounded-xl p-5 bg-white">
                        <h4 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-money-bill-transfer text-teal-600"></i> COD Settlement Frequency & Remittance Account
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">COD Settlement Schedule *</label>
                                <select name="settlement_cycle" x-model="settlementCycle" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white">
                                    <option value="daily">Daily Settlement (High Volume)</option>
                                    <option value="weekly_monday">Weekly (Every Monday Morning)</option>
                                    <option value="bi_weekly">Bi-Weekly (1st & 15th of Month)</option>
                                    <option value="monthly">Monthly Net 30</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Discount / VIP Shipping Tier</label>
                                <select name="discount_tier" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white">
                                    <option value="standard" {{ old('discount_tier', $metadata['discount_tier'] ?? 'standard') === 'standard' ? 'selected' : '' }}>Standard Merchant Rates</option>
                                    <option value="silver" {{ old('discount_tier', $metadata['discount_tier'] ?? '') === 'silver' ? 'selected' : '' }}>Silver Tier (-5% Base Rate)</option>
                                    <option value="gold" {{ old('discount_tier', $metadata['discount_tier'] ?? '') === 'gold' ? 'selected' : '' }}>Gold Tier (-10% Base Rate)</option>
                                    <option value="platinum" {{ old('discount_tier', $metadata['discount_tier'] ?? '') === 'platinum' ? 'selected' : '' }}>Platinum Volume Partner (-15%)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Bank / Branch Location</label>
                                <input type="text" name="branch" value="{{ old('branch', $user->ifsc_code ?? $metadata['branch'] ?? '') }}"
                                       placeholder="e.g. New Road Branch" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Settlement Bank Name</label>
                                <input type="text" name="bank_name" value="{{ old('bank_name', $user->bank_name ?? '') }}"
                                       placeholder="e.g. Global IME Bank" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Account Holder Name</label>
                                <input type="text" name="account_holder_name" value="{{ old('account_holder_name', $user->account_holder_name ?? $user->name) }}"
                                       placeholder="Exact name on bank" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Bank Account Number</label>
                                <input type="text" name="account_number" value="{{ old('account_number', $user->account_number ?? '') }}"
                                       placeholder="Account Number" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. DOMESTIC PARTNER FUNCTIONALITY COMPONENT -->
                <div x-show="selectedRole === 'partner'" class="space-y-6">
                    <div class="border border-slate-200 rounded-xl p-5 bg-white">
                        <h4 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-handshake text-teal-600"></i> Domestic Line-haul Partner Carrier Profile
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Partner Code *</label>
                                <input type="text" name="partner_code" value="{{ old('partner_code', $domesticPartner?->code ?? $metadata['partner_code'] ?? 'PRT-' . str_pad($user->id, 4, '0', STR_PAD_LEFT)) }}"
                                       placeholder="e.g. PRT-0004" class="w-full border rounded-lg px-3 py-2 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Company Legal Entity *</label>
                                <input type="text" name="company_name" value="{{ old('company_name', $domesticPartner?->company_name ?? $user->company_name ?? $user->name) }}"
                                       placeholder="Registered Carrier Name" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">PAN / VAT Number</label>
                                <input type="text" name="pan_number" value="{{ old('pan_number', $domesticPartner?->pan_number ?? $metadata['pan_number'] ?? '') }}"
                                       placeholder="e.g. 600998877" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 pt-4 border-t border-slate-100">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Carrier Service Model *</label>
                                <select name="service_type" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white">
                                    <option value="hub_to_hub_linehaul" {{ old('service_type', $domesticPartner?->service_type ?? 'hub_to_hub_linehaul') === 'hub_to_hub_linehaul' ? 'selected' : '' }}>Hub-to-Hub Trunk Linehaul</option>
                                    <option value="last_mile_delivery" {{ old('service_type', $domesticPartner?->service_type ?? '') === 'last_mile_delivery' ? 'selected' : '' }}>Last-Mile Regional Distribution</option>
                                    <option value="full_route_network" {{ old('service_type', $domesticPartner?->service_type ?? '') === 'full_route_network' ? 'selected' : '' }}>Full-Route Comprehensive Network</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Partner Margin Percentage (%)</label>
                                <input type="number" step="0.1" name="margin_percentage" value="{{ old('margin_percentage', $domesticPartner?->margin_percentage ?? 10.0) }}"
                                       class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Primary Base Hub / Depot</label>
                                <input type="text" name="base_hub" value="{{ old('base_hub', $metadata['base_hub'] ?? 'Kathmandu Central Hub') }}"
                                       placeholder="e.g. Kathmandu Hub" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 pt-4 border-t border-slate-100">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Carrier API & Webhook Access</label>
                                <select name="api_status" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white">
                                    <option value="active" {{ old('api_status', $metadata['api_status'] ?? 'active') === 'active' ? 'selected' : '' }}>Active Production Webhooks</option>
                                    <option value="sandbox" {{ old('api_status', $metadata['api_status'] ?? '') === 'sandbox' ? 'selected' : '' }}>Sandbox / Testing</option>
                                    <option value="disabled" {{ old('api_status', $metadata['api_status'] ?? '') === 'disabled' ? 'selected' : '' }}>Disabled (Manual Portal Only)</option>
                                </select>
                            </div>
                            <div class="flex items-center pt-6">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="kyc_verified" value="1"
                                           {{ old('kyc_verified', $domesticPartner?->kyc_verified ?? true) ? 'checked' : '' }}
                                           class="w-4 h-4 text-teal-600 rounded focus:ring-teal-500">
                                    <span class="text-xs font-semibold text-slate-800">Carrier Operating KYC Verified & Contract Signed</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. CORPORATE CLIENT / SHIPPER COMPONENT -->
                <div x-show="selectedRole === 'client' || selectedRole === 'customer'" class="space-y-6">
                    <div class="border border-slate-200 rounded-xl p-5 bg-white">
                        <h4 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-building text-teal-600"></i> Corporate Client & Bulk Account Profile
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Company / Organization *</label>
                                <input type="text" name="company_name" value="{{ old('company_name', $user->company_name ?? $user->name) }}"
                                       placeholder="e.g. Nepal Trade Synergy" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">PAN / VAT Number</label>
                                <input type="text" name="pan_number" value="{{ old('pan_number', $metadata['pan_number'] ?? '') }}"
                                       placeholder="e.g. 601998822" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Account Classification</label>
                                <select name="account_category" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white">
                                    <option value="individual" {{ old('account_category', $metadata['account_category'] ?? '') === 'individual' ? 'selected' : '' }}>Individual Regular Shipper</option>
                                    <option value="sme_corporate" {{ old('account_category', $metadata['account_category'] ?? 'sme_corporate') === 'sme_corporate' ? 'selected' : '' }}>SME Corporate Contract</option>
                                    <option value="enterprise_b2b" {{ old('account_category', $metadata['account_category'] ?? '') === 'enterprise_b2b' ? 'selected' : '' }}>Enterprise B2B Account</option>
                                    <option value="government_ngo" {{ old('account_category', $metadata['account_category'] ?? '') === 'government_ngo' ? 'selected' : '' }}>Diplomatic / NGO / Government</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 pt-4 border-t border-slate-100">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Billing & Credit Model</label>
                                <select name="billing_mode" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white">
                                    <option value="cash_on_booking" {{ old('billing_mode', $metadata['billing_mode'] ?? 'cash_on_booking') === 'cash_on_booking' ? 'selected' : '' }}>Prepaid / Cash on Booking</option>
                                    <option value="monthly_net_15" {{ old('billing_mode', $metadata['billing_mode'] ?? '') === 'monthly_net_15' ? 'selected' : '' }}>Credit Terms: Net 15 Days</option>
                                    <option value="monthly_net_30" {{ old('billing_mode', $metadata['billing_mode'] ?? '') === 'monthly_net_30' ? 'selected' : '' }}>Credit Terms: Net 30 Days</option>
                                    <option value="prepaid_wallet" {{ old('billing_mode', $metadata['billing_mode'] ?? '') === 'prepaid_wallet' ? 'selected' : '' }}>Prepaid Digital Balance Wallet</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Approved Credit Limit (NPR)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-xs font-bold text-slate-400">Rs.</span>
                                    <input type="number" step="1000" name="credit_limit" x-model="clientCreditLimit"
                                           class="w-full border rounded-lg pl-9 pr-3 py-2 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-teal-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Dedicated Account Executive</label>
                                <input type="text" name="account_manager" value="{{ old('account_manager', $metadata['account_manager'] ?? '') }}"
                                       placeholder="Account Manager Name" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. OPERATIONS STAFF & ADMIN COMPONENT -->
                <div x-show="['staff', 'admin', 'super_admin', 'domestic_admin', 'international_admin'].includes(selectedRole)" class="space-y-6">
                    <div class="border border-slate-200 rounded-xl p-5 bg-white">
                        <h4 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-shield-halved text-teal-600"></i> Internal Operations & Hierarchy
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Operational Desk / Department *</label>
                                <select name="department" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white">
                                    <option value="hub_dispatch" {{ old('department', $metadata['department'] ?? 'hub_dispatch') === 'hub_dispatch' ? 'selected' : '' }}>Hub & Linehaul Dispatch Desk</option>
                                    <option value="customer_care" {{ old('department', $metadata['department'] ?? '') === 'customer_care' ? 'selected' : '' }}>Customer Care & Exceptions</option>
                                    <option value="finance_settlement" {{ old('department', $metadata['department'] ?? '') === 'finance_settlement' ? 'selected' : '' }}>COD Audits & Merchant Finance</option>
                                    <option value="field_supervision" {{ old('department', $metadata['department'] ?? '') === 'field_supervision' ? 'selected' : '' }}>Fleet & Rider Field Supervision</option>
                                    <option value="it_security" {{ old('department', $metadata['department'] ?? '') === 'it_security' ? 'selected' : '' }}>IT Infrastructure & Compliance</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Base Hub / Duty Terminal</label>
                                <select name="hub_location" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white">
                                    <option value="Kathmandu Central Cargo Complex" {{ old('hub_location', $metadata['hub_location'] ?? 'Kathmandu Central Cargo Complex') === 'Kathmandu Central Cargo Complex' ? 'selected' : '' }}>Kathmandu Central Cargo Complex (KTM)</option>
                                    <option value="Biratnagar Regional Hub" {{ old('hub_location', $metadata['hub_location'] ?? '') === 'Biratnagar Regional Hub' ? 'selected' : '' }}>Biratnagar Regional Hub (Koshi)</option>
                                    <option value="Pokhara Transit Terminal" {{ old('hub_location', $metadata['hub_location'] ?? '') === 'Pokhara Transit Terminal' ? 'selected' : '' }}>Pokhara Transit Terminal (Gandaki)</option>
                                    <option value="Nepalgunj Frontier Hub" {{ old('hub_location', $metadata['hub_location'] ?? '') === 'Nepalgunj Frontier Hub' ? 'selected' : '' }}>Nepalgunj Frontier Hub (Lumbini/Karnali)</option>
                                    <option value="Birgunj Gateway Hub" {{ old('hub_location', $metadata['hub_location'] ?? '') === 'Birgunj Gateway Hub' ? 'selected' : '' }}>Birgunj Gateway Hub (Madhesh)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Operational Designation / Title</label>
                                <input type="text" name="designation" value="{{ old('designation', $metadata['designation'] ?? 'Operations Controller') }}"
                                       placeholder="e.g. Senior Dispatch Controller" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: ACCOUNT & IDENTITY -->
            <div x-show="activeTab === 'identity'" class="p-6 sm:p-8 space-y-8" style="display: none;">

                <!-- Personal Identity Details -->
                <div>
                    <h3 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <i class="fas fa-user-circle text-teal-600"></i> Personal & Contact Credentials
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Full Name *</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                   class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 @error('name') border-rose-500 @enderror">
                            @error('name')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address *</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 @error('email') border-rose-500 @enderror">
                            @error('email')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Phone / Mobile *</label>
                            <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                                   class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 @error('phone') border-rose-500 @enderror">
                            @error('phone')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Gender</label>
                            <select name="gender" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white">
                                <option value="">Not Specified</option>
                                <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Date of Birth</label>
                            <input type="date" name="dob" value="{{ old('dob', $user->dob ? \Carbon\Carbon::parse($user->dob)->format('Y-m-d') : '') }}"
                                   class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>
                </div>

                <!-- Territory & Geography -->
                <div>
                    <h3 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <i class="fas fa-map-location-dot text-teal-600"></i> Nepal Territory Jurisdiction & Addresses
                    </h3>
                    <div class="mt-4 mb-4">
                        <x-nepal-territory-picker
                            provinceName="province"
                            districtName="district"
                            :selectedProvince="$user->province"
                            :selectedDistrict="$user->district"
                            provinceLabel="Assigned Province / Territory"
                            districtLabel="Operating District (Under Province)"
                            idPrefix="admin_edit_geo"
                            helperText="Select from Nepal's 7 Provinces and 77 Districts to define this user's primary operating territory." />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Permanent Address</label>
                            <input type="text" name="permanent_address" value="{{ old('permanent_address', $user->permanent_address) }}"
                                   placeholder="Permanent Municipal/Ward Address" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Temporary / Operating Base Address</label>
                            <input type="text" name="temporary_address" value="{{ old('temporary_address', $user->temporary_address) }}"
                                   placeholder="Current Residence / Base Address" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>
                </div>

                <!-- Password & Security Reset -->
                <div>
                    <h3 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <i class="fas fa-lock text-teal-600"></i> Password & Security Reset
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 mb-3">Leave blank if password reset is not required for this user.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">New Password (Min 12 Characters)</label>
                            <input type="password" name="password"
                                   placeholder="••••••••••••" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 @error('password') border-rose-500 @enderror">
                            @error('password')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Confirm New Password</label>
                            <input type="password" name="password_confirmation"
                                   placeholder="••••••••••••" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: ROLE & OPERATIONAL SCOPE -->
            <div x-show="activeTab === 'scope'" class="p-6 sm:p-8 space-y-8" style="display: none;">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <i class="fas fa-shield-alt text-teal-600"></i> Account & Operational Scope
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                        <!-- Account Role Selector -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Account Role *</label>
                            <select x-model="selectedRole"
                                    class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white font-medium">
                                @foreach($manageableUserTypes as $type => $label)
                                    <option value="{{ $type }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-500 mt-1">
                                Determines access level and dashboard UI. Changing role here updates the active Functionality Console.
                            </p>
                        </div>

                        <!-- Verification Status Selector -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Verification Status *</label>
                            <select name="verification_status" required
                                    class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white font-medium">
                                <option value="pending" {{ old('verification_status', $user->verification_status) === 'pending' ? 'selected' : '' }}>⏳ Pending Verification Review</option>
                                <option value="approved" {{ old('verification_status', $user->verification_status) === 'approved' ? 'selected' : '' }}>✅ Approved & Fully Active</option>
                                <option value="rejected" {{ old('verification_status', $user->verification_status) === 'rejected' ? 'selected' : '' }}>❌ Rejected / KYC Incomplete</option>
                                <option value="suspended" {{ old('verification_status', $user->verification_status) === 'suspended' ? 'selected' : '' }}>⛔ Temporarily Suspended</option>
                            </select>
                            <p class="text-[11px] text-slate-500 mt-1">
                                Controls login authorization and job acceptance permissions.
                            </p>
                        </div>

                        <!-- Operations Staff Service Scope -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Operations Staff Service Scope</label>
                            <select name="service_scope" class="w-full border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 bg-white">
                                <option value="all" {{ old('service_scope', $user->service_scope) === 'all' ? 'selected' : '' }}>👑 All 3 Services (Super Admin Staff — Unrestricted view across all pillars)</option>
                                <option value="international" {{ old('service_scope', $user->service_scope) === 'international' ? 'selected' : '' }}>✈️ International Air Freight Only (Overseas Hubs & Flight MAWBs)</option>
                                <option value="domestic" {{ old('service_scope', $user->service_scope) === 'domestic' ? 'selected' : '' }}>🏔️ Nepal Domestic Logistics Only (7 Provincial Hubs, Manifest Bags & Depots)</option>
                                <option value="ecommerce" {{ old('service_scope', $user->service_scope) === 'ecommerce' ? 'selected' : '' }}>🛵 E-Commerce & Rider Fleet Only (OTP Direct Delivery & Intra-city Fleet)</option>
                            </select>
                            <p class="text-[11px] text-slate-500 mt-1">
                                Restricts internal staff operators to their designated vertical.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Bottom Action Bar -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <i class="fas fa-info-circle text-teal-600"></i>
                    <span>All changes to role functionality will be immediately synchronized across the logistics network.</span>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="{{ route('admin.users.index') }}"
                       class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 hover:bg-slate-100 font-semibold text-xs transition text-center">
                        Cancel
                    </a>
                    <button type="submit"
                            class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition flex items-center justify-center gap-2 shadow-sm shadow-teal-600/30">
                        <i class="fas fa-check"></i> Save User Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
