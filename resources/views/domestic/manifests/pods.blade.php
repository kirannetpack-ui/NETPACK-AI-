@extends('layouts.app')

@section('title', 'Proof of Delivery')
@section('page-title', '📋 Proof of Delivery (POD)')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">📋 Proof of Delivery</h1>
                <p class="text-sm text-gray-500 mt-1">View all delivery confirmations</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('domestic.manifests.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Manifests
                </a>
                <button onclick="window.print()" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                    <i class="fas fa-print mr-2"></i> Print
                </button>
            </div>
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
                <div class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-500">
                    <p class="text-sm text-gray-600">Total PODs</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4 border-l-4 border-yellow-500">
                    <p class="text-sm text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] ?? 0 }}</p>
                </div>
                <div class="bg-blue-100 rounded-lg p-4 border-l-4 border-blue-500">
                    <p class="text-sm text-gray-600">Uploaded</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['uploaded'] ?? 0 }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4 border-l-4 border-green-500">
                    <p class="text-sm text-gray-600">Verified</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['verified'] ?? 0 }}</p>
                </div>
            </div>

            <!-- Search & Filters -->
            <form method="GET" class="flex flex-wrap gap-3 mb-6">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by tracking number, recipient..." 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <select name="status" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="uploaded" {{ request('status') === 'uploaded' ? 'selected' : '' }}>Uploaded</option>
                        <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                           class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" 
                           class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                </div>
                @if(request('search') || request('status') || request('date_from') || request('date_to'))
                    <div>
                        <a href="{{ route('domestic.manifests.pods') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                            <i class="fas fa-undo mr-2"></i> Reset
                        </a>
                    </div>
                @endif
            </form>

            <!-- PODs Table -->
            @if(isset($pods) && $pods->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">#</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Tracking #</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Recipient</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Manifest</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">POD Type</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Status</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Date</th>
                                <th class="text-left py-3 px-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pods as $pod)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="py-3 px-4">{{ $loop->iteration }}</td>
                                    <td class="py-3 px-4 font-mono text-sm">{{ $pod->shipment->tracking_number ?? 'N/A' }}</td>
                                    <td class="py-3 px-4">{{ $pod->recipient_name ?? $pod->shipment->receiver_name ?? 'N/A' }}</td>
                                    <td class="py-3 px-4">{{ $pod->manifest->manifest_number ?? 'N/A' }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                                            {{ $pod->pod_type === 'file' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                            <i class="fas {{ $pod->pod_type === 'file' ? 'fa-file' : 'fa-camera' }} mr-1"></i>
                                            {{ ucfirst($pod->pod_type) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $pod->status_badge }}">
                                            {{ ucfirst($pod->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm">{{ $pod->created_at->format('M d, Y') }}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex gap-2">
                                            <!-- View Button -->
                                            <a href="{{ route('domestic.manifests.pods.show', $pod->id) }}" 
                                               class="text-blue-600 hover:text-blue-800" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <!-- Download Button - if file exists -->
                                            @if($pod->pod_file || $pod->pod_photo)
                                                <a href="{{ route('domestic.manifests.pods.file', ['id' => $pod->id, 'type' => $pod->pod_file ? 'file' : 'photo']) }}"
                                                   target="_blank" class="text-teal-600 hover:text-teal-800" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            @endif
                                            
                                            <!-- Upload Button - if no file exists -->
                                            @if(!$pod->pod_file && !$pod->pod_photo)
                                                <a href="{{ route('domestic.manifests.pods.upload.form', $pod->shipment_id) }}" 
                                                   class="text-green-600 hover:text-green-800" title="Upload POD">
                                                    <i class="fas fa-upload"></i>
                                                </a>
                                            @endif

                                            <!-- Edit Status Button -->
                                            @if(in_array(auth()->user()->user_type, ['super_admin', 'admin', 'domestic_admin'], true) && $pod->status !== 'verified')
                                                <button onclick="openStatusModal({{ $pod->id }}, '{{ $pod->status }}')" 
                                                        class="text-yellow-600 hover:text-yellow-800" title="Update Status">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $pods->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-file-signature text-5xl text-gray-300 mb-4 block"></i>
                    <h3 class="text-lg font-semibold text-gray-700">No PODs Found</h3>
                    <p class="text-gray-500 mt-2">No proof of delivery records available.</p>
                    <p class="text-sm text-gray-400 mt-1">PODs will appear here once deliveries are completed and confirmed.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Update POD Status</h3>
        </div>
        <form id="statusForm" method="POST" class="p-6">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Status</label>
                <select name="status" id="statusSelect" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="pending">Pending</option>
                    <option value="uploaded">Uploaded</option>
                    <option value="verified">Verified</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes (optional)</label>
                <textarea name="notes" rows="3" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200" placeholder="Add any notes..."></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeStatusModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Update Status
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openStatusModal(podId, currentStatus) {
        const modal = document.getElementById('statusModal');
        const form = document.getElementById('statusForm');
        const select = document.getElementById('statusSelect');
        
        // Set form action
        const template = @json(route('domestic.manifests.pods.update-status', ['id' => '__POD_ID__']));
        form.action = template.replace('__POD_ID__', podId);
        
        // Set current status
        select.value = currentStatus;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeStatusModal() {
        const modal = document.getElementById('statusModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Close modal on click outside
    document.getElementById('statusModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeStatusModal();
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeStatusModal();
        }
    });
</script>
@endpush
@endsection
