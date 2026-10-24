@extends('layouts.app')

@section('title', 'User Details - ' . $user->name)

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-teal-600">Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.users.index') }}" class="hover:text-teal-600">Users</a>
        <span class="mx-2">/</span>
        <span class="text-gray-700">{{ $user->name }}</span>
    </nav>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <!-- Header with Profile -->
        <div class="bg-gradient-to-r from-teal-600 to-teal-700 px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <!-- Avatar -->
                    <div class="w-24 h-24 rounded-full bg-white/20 backdrop-blur flex items-center justify-center border-4 border-white/50">
                        @if($user->profile_photo)
                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-full">
                        @else
                            <span class="text-3xl font-bold text-white">{{ $user->initials ?? substr($user->name, 0, 2) }}</span>
                        @endif
                    </div>
                    
                    <!-- User Info -->
                    <div class="text-white">
                        <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
                        <div class="flex items-center gap-3 mt-1 flex-wrap">
                            <span class="text-sm text-white/80">
                                <i class="fas fa-envelope mr-1"></i> {{ $user->email }}
                            </span>
                            <span class="text-white/50">|</span>
                            <span class="text-sm text-white/80">
                                <i class="fas fa-phone mr-1"></i> {{ $user->phone ?? 'N/A' }}
                            </span>
                            <span class="text-white/50">|</span>
                            <span class="text-sm text-white/80">
                                <i class="fas fa-calendar-alt mr-1"></i> Joined {{ $user->created_at->format('M d, Y') }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex gap-2">
                    @if($user->verification_status === 'pending')
                        <a href="{{ route('admin.users.verify', $user->id) }}" 
                           class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition flex items-center gap-2">
                            <i class="fas fa-check-circle"></i> Verify
                        </a>
                    @else
                        <a href="{{ route('admin.users.edit', $user->id) }}" 
                           class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition flex items-center gap-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    @endif
                    <a href="{{ route('admin.users.index') }}" 
                       class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <!-- Status Bar -->
        <div class="px-8 py-3 bg-gray-50 border-b flex items-center gap-6 flex-wrap">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Status:</span>
                @if($user->verification_status === 'approved')
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium flex items-center gap-1">
                        <i class="fas fa-circle text-green-500 text-xs"></i> Approved
                    </span>
                @elseif($user->verification_status === 'pending')
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium flex items-center gap-1">
                        <i class="fas fa-circle text-yellow-500 text-xs"></i> Pending
                    </span>
                @elseif($user->verification_status === 'rejected')
                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium flex items-center gap-1">
                        <i class="fas fa-circle text-red-500 text-xs"></i> Rejected
                    </span>
                @else
                    <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium flex items-center gap-1">
                        <i class="fas fa-circle text-gray-500 text-xs"></i> {{ ucfirst($user->verification_status) }}
                    </span>
                @endif
            </div>
            
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Role:</span>
                <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-medium">
                    {{ $user->user_type_label ?? ucfirst($user->user_type) }}
                </span>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">KYC:</span>
                @if($user->kyc_verified)
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                        <i class="fas fa-check-circle text-green-500"></i> Verified
                    </span>
                @else
                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                        <i class="fas fa-times-circle text-red-500"></i> Pending
                    </span>
                @endif
            </div>
            
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Profile:</span>
                @if($user->registration_completed)
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                        <i class="fas fa-check-circle text-green-500"></i> Complete
                    </span>
                @else
                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                        <i class="fas fa-times-circle text-red-500"></i> Incomplete
                    </span>
                @endif
            </div>
            
            @if($user->last_login_at)
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Last Login:</span>
                <span class="text-sm text-gray-700">{{ $user->last_login_at->diffForHumans() }}</span>
            </div>
            @endif
        </div>

        <!-- Content -->
        <div class="p-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Personal Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Personal Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-user text-teal-600"></i>
                            Personal Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">Full Name</p>
                                <p class="text-gray-800 font-medium mt-1">{{ $user->name }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">Email Address</p>
                                <p class="text-gray-800 font-medium mt-1">{{ $user->email }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">Phone Number</p>
                                <p class="text-gray-800 font-medium mt-1">{{ $user->phone ?? 'Not provided' }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">Date of Birth</p>
                                <p class="text-gray-800 font-medium mt-1">{{ $user->dob ? \Carbon\Carbon::parse($user->dob)->format('M d, Y') : 'Not provided' }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">Gender</p>
                                <p class="text-gray-800 font-medium mt-1">{{ $user->gender_label ?? 'Not provided' }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">Nationality</p>
                                <p class="text-gray-800 font-medium mt-1">{{ $user->nationality ?? 'Not provided' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-map-marker-alt text-teal-600"></i>
                            Address Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">Address</p>
                                <p class="text-gray-800 font-medium mt-1">{{ $user->address ?? 'Not provided' }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">City</p>
                                <p class="text-gray-800 font-medium mt-1">{{ $user->city ?? 'Not provided' }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">District</p>
                                <p class="text-gray-800 font-medium mt-1">{{ $user->district ?? 'Not provided' }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">Province</p>
                                <p class="text-gray-800 font-medium mt-1">{{ $user->province ?? 'Not provided' }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">Country</p>
                                <p class="text-gray-800 font-medium mt-1">{{ $user->country ?? 'Not provided' }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">Postal Code</p>
                                <p class="text-gray-800 font-medium mt-1">{{ $user->postal_code ?? 'Not provided' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Business/Rider Info -->
                <div class="space-y-6">
                    <!-- Business Information (for Sellers & Partners) -->
                    @if(in_array($user->user_type, ['seller', 'partner']))
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-store text-teal-600"></i>
                            Business Information
                        </h3>
                        <div class="space-y-3">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">Business Name</p>
                                <p class="text-gray-800 font-medium mt-1">{{ $user->business_name ?? 'Not provided' }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">Business Address</p>
                                <p class="text-gray-800 font-medium mt-1">{{ $user->business_address ?? 'Not provided' }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">PAN Number</p>
                                <p class="text-gray-800 font-medium mt-1">{{ $user->pan_number ?? 'Not provided' }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Rider Information -->
                    @if($user->user_type === 'rider')
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-motorcycle text-teal-600"></i>
                            Rider Information
                        </h3>
                        <div class="space-y-3">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">License Number</p>
                                <p class="text-gray-800 font-medium mt-1">{{ $user->license_number ?? 'Not provided' }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">Vehicle Type</p>
                                <p class="text-gray-800 font-medium mt-1">{{ ucfirst($user->vehicle_type ?? 'Not provided') }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">Vehicle Registration</p>
                                <p class="text-gray-800 font-medium mt-1">{{ $user->vehicle_registration_number ?? 'Not provided' }}</p>
                            </div>
                            @if($user->is_available !== null)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">Availability</p>
                                <p class="text-gray-800 font-medium mt-1">
                                    @if($user->is_available)
                                        <span class="text-green-600"><i class="fas fa-check-circle"></i> Available</span>
                                    @else
                                        <span class="text-red-600"><i class="fas fa-times-circle"></i> Unavailable</span>
                                    @endif
                                </p>
                            </div>
                            @endif
                            @if($user->rating)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">Rating</p>
                                <p class="text-gray-800 font-medium mt-1">
                                    <span class="text-yellow-500">★</span> {{ number_format($user->rating, 1) }} / 5.0
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- System Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-cog text-teal-600"></i>
                            System Information
                        </h3>
                        <div class="space-y-3">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">User ID</p>
                                <p class="text-gray-800 font-medium mt-1">#{{ $user->id }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">Created At</p>
                                <p class="text-gray-800 font-medium mt-1">{{ $user->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">Last Updated</p>
                                <p class="text-gray-800 font-medium mt-1">{{ $user->updated_at->format('M d, Y h:i A') }}</p>
                            </div>
                            @if($user->approved_at)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">Approved At</p>
                                <p class="text-gray-800 font-medium mt-1">{{ $user->approved_at->format('M d, Y h:i A') }}</p>
                            </div>
                            @endif
                            @if($user->approved_by)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 uppercase font-medium">Approved By</p>
                                <p class="text-gray-800 font-medium mt-1">{{ $user->approvedBy->name ?? 'System' }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection