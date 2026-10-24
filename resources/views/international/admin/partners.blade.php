@extends('layouts.app')

@section('title', 'Overseas Partners')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Overseas Partners</h1>
                <p class="text-sm text-gray-500 mt-1">Manage international delivery partners</p>
            </div>
            <a href="{{ route('international.partners.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                <i class="fas fa-plus mr-2"></i> Add Partner
            </a>
        </div>

        <div class="p-6">
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

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Total Partners</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $partners->total() }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Active</p>
                    <p class="text-2xl font-bold text-green-600">{{ $partners->where('verification_status', 'approved')->count() }}</p>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $partners->where('verification_status', 'pending')->count() }}</p>
                </div>
                <div class="bg-red-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Suspended</p>
                    <p class="text-2xl font-bold text-red-600">{{ $partners->where('verification_status', 'suspended')->count() }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">#</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Company</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Contact</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Email</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Country</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($partners as $partner)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="py-3 px-4">{{ $loop->iteration }}</td>
                                <td class="py-3 px-4">
                                    <div class="font-medium">{{ $partner->company_name ?? $partner->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $partner->name }}</div>
                                </td>
                                <td class="py-3 px-4">{{ $partner->contact_person ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm">{{ $partner->email }}</td>
                                <td class="py-3 px-4">{{ $partner->country ?? 'N/A' }}</td>
                                <td class="py-3 px-4">
                                    @if($partner->verification_status === 'approved')
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">
                                            <i class="fas fa-circle text-green-500 text-xs mr-1"></i> Active
                                        </span>
                                    @elseif($partner->verification_status === 'pending')
                                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs font-medium">
                                            <i class="fas fa-circle text-yellow-500 text-xs mr-1"></i> Pending
                                        </span>
                                    @elseif($partner->verification_status === 'suspended')
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">
                                            <i class="fas fa-circle text-red-500 text-xs mr-1"></i> Suspended
                                        </span>
                                    @else
                                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs font-medium">
                                            {{ ucfirst($partner->verification_status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex gap-2">
                                        <a href="{{ route('international.partners.show', $partner->id) }}" class="text-blue-600 hover:text-blue-800" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('international.partners.edit', $partner->id) }}" class="text-teal-600 hover:text-teal-800" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('international.partners.toggle', $partner->id) }}" class="inline" onsubmit="return confirm('{{ $partner->verification_status === 'approved' ? 'Suspend' : 'Activate' }} this partner?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-{{ $partner->verification_status === 'approved' ? 'red' : 'green' }}-600 hover:text-{{ $partner->verification_status === 'approved' ? 'red' : 'green' }}-800" title="{{ $partner->verification_status === 'approved' ? 'Suspend' : 'Activate' }}">
                                                <i class="fas fa-{{ $partner->verification_status === 'approved' ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('international.partners.delete', $partner->id) }}" class="inline" onsubmit="return confirm('Delete this partner? This action cannot be undone.')">
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
                                <td colspan="7" class="text-center py-8 text-gray-500">
                                    <i class="fas fa-handshake text-4xl block mb-2"></i>
                                    No overseas partners found. Click "Add Partner" to create one.
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
