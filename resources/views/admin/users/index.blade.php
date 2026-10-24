@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">
                    @if(request()->get('status') === 'pending')
                        Pending Verification
                    @else
                        All Users
                    @endif
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    @if(request()->get('status') === 'pending')
                        Review and verify pending user registrations
                    @else
                        Search, filter, approve, and manage authorised account types.
                    @endif
                </p>
            </div>
            @if(request()->get('status') !== 'pending')
                <a href="{{ route('admin.users.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                    <i class="fas fa-user-plus mr-2"></i> Add User
                </a>
            @endif
        </div>
        
        <div class="p-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                @if(request()->get('status') === 'pending')
                    <!-- Pending Users Stats -->
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-600">Pending Users</p>
                                <p class="text-2xl font-bold text-yellow-600">{{ $stats['all_pending'] ?? 0 }}</p>
                            </div>
                            <i class="fas fa-clock text-yellow-500 text-2xl"></i>
                        </div>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-600">Total Users</p>
                                <p class="text-2xl font-bold text-blue-600">{{ \App\Models\User::count() }}</p>
                            </div>
                            <i class="fas fa-users text-blue-500 text-2xl"></i>
                        </div>
                    </div>
                @else
                    <!-- Admin Stats -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-600">All Users</p>
                                <p class="text-2xl font-bold">{{ $stats['total'] ?? 0 }}</p>
                            </div>
                            <i class="fas fa-users-cog text-blue-500 text-2xl"></i>
                        </div>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-600">Administrators</p>
                                <p class="text-2xl font-bold text-purple-600">{{ $stats['admin'] ?? 0 }}</p>
                            </div>
                            <i class="fas fa-user-shield text-purple-500 text-2xl"></i>
                        </div>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-600">Staff</p>
                                <p class="text-2xl font-bold text-blue-600">{{ $stats['staff'] ?? 0 }}</p>
                            </div>
                            <i class="fas fa-user-tie text-blue-500 text-2xl"></i>
                        </div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-600">Approved</p>
                                <p class="text-2xl font-bold text-green-600">{{ $stats['approved'] ?? 0 }}</p>
                            </div>
                            <i class="fas fa-user-check text-green-500 text-2xl"></i>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Pending Users Alert -->
            @if(request()->get('status') !== 'pending' && ($stats['all_pending'] ?? 0) > 0)
                <div class="bg-yellow-50 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg mb-4 flex justify-between items-center">
                    <div>
                        <i class="fas fa-clock mr-2"></i>
                        <strong>{{ $stats['all_pending'] }}</strong> user(s) pending approval.
                    </div>
                    <a href="{{ route('admin.users.index', ['status' => 'pending']) }}" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition text-sm">
                        Review Now
                    </a>
                </div>
            @endif

            <!-- Filters -->
            <div class="mb-4 flex flex-wrap gap-2 items-center">
                <div class="flex-1 min-w-[200px]">
                    <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-2">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="{{ request()->get('status') === 'pending' ? 'Search pending users...' : 'Search users by name, email, or phone...' }}"
                               class="flex-1 border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        @if(request()->get('status') === 'pending')
                            <input type="hidden" name="status" value="pending">
                        @endif
                        <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                
                @if(request()->get('status') !== 'pending')
                <div class="flex gap-2">
                    <a href="{{ route('admin.users.index') }}" class="px-3 py-1 bg-gray-200 rounded-full text-sm hover:bg-gray-300 {{ !request('user_type') && !request('status') ? 'bg-gray-800 text-white' : '' }}">
                        All
                    </a>
                    <a href="{{ route('admin.users.index', ['user_type' => 'admin']) }}" class="px-3 py-1 bg-gray-200 rounded-full text-sm hover:bg-gray-300 {{ request('user_type') === 'admin' ? 'bg-purple-600 text-white' : '' }}">
                        Admin
                    </a>
                    <a href="{{ route('admin.users.index', ['user_type' => 'staff']) }}" class="px-3 py-1 bg-gray-200 rounded-full text-sm hover:bg-gray-300 {{ request('user_type') === 'staff' ? 'bg-blue-600 text-white' : '' }}">
                        Staff
                    </a>
                    <a href="{{ route('admin.users.index', ['user_type' => 'client']) }}" class="px-3 py-1 bg-gray-200 rounded-full text-sm hover:bg-gray-300 {{ request('user_type') === 'client' ? 'bg-teal-600 text-white' : '' }}">Client</a>
                    <a href="{{ route('admin.users.index', ['user_type' => 'customer']) }}" class="px-3 py-1 bg-gray-200 rounded-full text-sm hover:bg-gray-300 {{ request('user_type') === 'customer' ? 'bg-gray-700 text-white' : '' }}">Customer</a>
                </div>
                @endif

                <div class="flex gap-2">
                    <a href="{{ route('admin.users.index', ['status' => 'pending']) }}" class="px-3 py-1 bg-gray-200 rounded-full text-sm hover:bg-gray-300 {{ request('status') === 'pending' ? 'bg-yellow-600 text-white' : '' }}">
                        <i class="fas fa-clock text-yellow-500 text-xs mr-1"></i> Pending
                        <span class="ml-1 bg-yellow-600 text-white text-xs px-2 py-0.5 rounded-full">{{ $stats['all_pending'] ?? 0 }}</span>
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">User</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Email</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Type</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Created</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            @if($user->profile_photo)
                                                <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full">
                                            @else
                                                <span class="text-gray-600 font-medium">{{ substr($user->name, 0, 2) }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $user->name }}</div>
                                            @if($user->phone)
                                                <div class="text-xs text-gray-500">{{ $user->phone }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-sm">{{ $user->email }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $user->admin_role_badge ?? 'gray' }}-100 text-{{ $user->admin_role_badge ?? 'gray' }}-800">
                                        {{ $user->user_type_label ?? ucfirst($user->user_type) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $user->status_badge ?? 'gray' }}-100 text-{{ $user->status_badge ?? 'gray' }}-800">
                                        {{ $user->status_label ?? ucfirst($user->verification_status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-sm">
                                    {{ $user->created_at->format('M d, Y') }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.users.show', $user->id) }}" class="text-blue-600 hover:text-blue-800" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($user->verification_status === 'pending')
                                            <a href="{{ route('admin.users.verify', $user->id) }}" class="text-yellow-600 hover:text-yellow-800" title="Verify">
                                                <i class="fas fa-check-circle"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="text-teal-600 hover:text-teal-800" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-gray-500">
                                    <i class="fas fa-users text-4xl block mb-2"></i>
                                    @if(request()->get('status') === 'pending')
                                        No pending users found. All users have been verified.
                                    @else
                                        No users match the selected filters.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $users->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
