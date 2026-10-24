@extends('layouts.app')

@section('title', 'Domestic Partners')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Domestic Partners</h1>
                <p class="text-sm text-gray-500 mt-1">Manage your domestic delivery partners</p>
            </div>
            <a href="{{ route('admin.partners.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                <i class="fas fa-plus mr-2"></i> Add Partner
            </a>
        </div>
        
        <div class="p-6">
            <!-- Filters -->
            <div class="mb-4 flex flex-wrap gap-2">
                <a href="{{ route('admin.partners.index') }}" class="px-3 py-1 bg-gray-200 rounded-full text-sm hover:bg-gray-300 {{ !request('status') ? 'bg-gray-800 text-white' : '' }}">
                    All
                </a>
                <a href="{{ route('admin.partners.index', ['status' => 'active']) }}" class="px-3 py-1 bg-gray-200 rounded-full text-sm hover:bg-gray-300 {{ request('status') === 'active' ? 'bg-green-600 text-white' : '' }}">
                    Active
                </a>
                <a href="{{ route('admin.partners.index', ['status' => 'inactive']) }}" class="px-3 py-1 bg-gray-200 rounded-full text-sm hover:bg-gray-300 {{ request('status') === 'inactive' ? 'bg-red-600 text-white' : '' }}">
                    Inactive
                </a>
                <a href="{{ route('admin.partners.index', ['status' => 'pending']) }}" class="px-3 py-1 bg-gray-200 rounded-full text-sm hover:bg-gray-300 {{ request('status') === 'pending' ? 'bg-yellow-600 text-white' : '' }}">
                    Pending KYC
                </a>
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
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Company</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Contact</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Email</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Phone</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Margin</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">KYC</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($partners as $partner)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="py-3 px-4">
                                    <div class="font-medium">{{ $partner->company_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $partner->city }}, {{ $partner->province }}</div>
                                </td>
                                <td class="py-3 px-4">{{ $partner->contact_person }}</td>
                                <td class="py-3 px-4 text-sm">{{ $partner->email }}</td>
                                <td class="py-3 px-4 text-sm">{{ $partner->phone }}</td>
                                <td class="py-3 px-4 font-medium">{{ $partner->margin_percentage }}%</td>
                                <td class="py-3 px-4">
                                    @if($partner->is_active)
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Active</span>
                                    @else
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($partner->kyc_verified)
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Verified</span>
                                    @else
                                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs font-medium">Pending</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.partners.show', $partner->id) }}" class="text-blue-600 hover:text-blue-800" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.partners.edit', $partner->id) }}" class="text-teal-600 hover:text-teal-800" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if(!$partner->kyc_verified)
                                            <a href="{{ route('admin.partners.verify', $partner->id) }}" class="text-yellow-600 hover:text-yellow-800" title="Verify">
                                                <i class="fas fa-check-circle"></i>
                                            </a>
                                        @endif
                                        <form method="POST" action="{{ route('admin.partners.destroy', $partner->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this partner?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-gray-500">
                                    <i class="fas fa-building text-4xl block mb-2"></i>
                                    No partners found. Click "Add Partner" to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $partners->links() }}
            </div>
        </div>
    </div>
</div>
@endsection