@extends('layouts.app')

@section('title', 'POD Details')
@section('page-title', '📋 POD Details')

@push('styles')
<style>
    .pod-image {
        max-height: 400px;
        object-fit: contain;
    }
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-badge.pending { background: #fef3c7; color: #92400e; }
    .status-badge.uploaded { background: #dbeafe; color: #1e40af; }
    .status-badge.verified { background: #d1fae5; color: #065f46; }
    .status-badge.rejected { background: #fee2e2; color: #991b1b; }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">POD Details</h1>
                <p class="text-sm text-gray-500 mt-1">Proof of Delivery #{{ $pod->id }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('domestic.manifests.pods') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
                @if($pod->pod_file || $pod->pod_photo)
                    <a href="{{ route('domestic.manifests.pods.file', ['id' => $pod->id, 'type' => $pod->pod_file ? 'file' : 'photo']) }}"
                       target="_blank" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-download mr-2"></i> Download
                    </a>
                @endif
            </div>
        </div>

        <div class="p-6">
            <!-- Status Banner -->
            <div class="mb-6 p-4 rounded-lg border 
                {{ $pod->status === 'verified' ? 'bg-green-50 border-green-200' : 
                   ($pod->status === 'pending' ? 'bg-yellow-50 border-yellow-200' : 
                   ($pod->status === 'rejected' ? 'bg-red-50 border-red-200' : 
                   'bg-blue-50 border-blue-200')) }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Current Status</p>
                        <p class="text-xl font-bold">
                            <span class="status-badge {{ $pod->status }}">
                                {{ ucfirst($pod->status) }}
                            </span>
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Delivered At</p>
                        <p class="font-medium">{{ $pod->delivered_at ? $pod->delivered_at->format('M d, Y H:i') : 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- POD Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="border rounded-lg p-4 bg-gray-50">
                    <p class="text-xs text-gray-500 uppercase">Tracking Number</p>
                    <p class="font-mono font-semibold">{{ $pod->shipment->tracking_number ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4 bg-gray-50">
                    <p class="text-xs text-gray-500 uppercase">Manifest</p>
                    <p class="font-semibold">{{ $pod->manifest->manifest_number ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4 bg-gray-50">
                    <p class="text-xs text-gray-500 uppercase">Recipient Name</p>
                    <p class="font-semibold">{{ $pod->recipient_name ?? $pod->shipment->receiver_name ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4 bg-gray-50">
                    <p class="text-xs text-gray-500 uppercase">POD Type</p>
                    <p class="font-semibold">
                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                            {{ $pod->pod_type === 'file' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                            <i class="fas {{ $pod->pod_type === 'file' ? 'fa-file' : 'fa-camera' }} mr-1"></i>
                            {{ ucfirst($pod->pod_type) }}
                        </span>
                    </p>
                </div>
                <div class="border rounded-lg p-4 bg-gray-50">
                    <p class="text-xs text-gray-500 uppercase">Uploaded By</p>
                    <p class="font-semibold">{{ $pod->uploadedBy->name ?? 'N/A' }}</p>
                </div>
                <div class="border rounded-lg p-4 bg-gray-50">
                    <p class="text-xs text-gray-500 uppercase">Uploaded At</p>
                    <p class="font-semibold">{{ $pod->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>

            <!-- Delivery Notes -->
            @if($pod->delivery_notes)
                <div class="border rounded-lg p-4 mb-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">📝 Delivery Notes</h3>
                    <p class="text-gray-600">{{ $pod->delivery_notes }}</p>
                </div>
            @endif

            <!-- Signature -->
            @if($pod->recipient_signature)
                <div class="border rounded-lg p-4 mb-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">✍️ Signature</h3>
                    <a href="{{ route('domestic.manifests.pods.file', ['id' => $pod->id, 'type' => 'signature']) }}" target="_blank" class="text-teal-600 hover:underline">View signature</a>
                </div>
            @endif

            <!-- POD File/Photo -->
            <div class="border rounded-lg p-4 mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas {{ $pod->pod_type === 'file' ? 'fa-file' : 'fa-image' }} mr-2"></i>
                    {{ $pod->pod_type === 'file' ? 'POD File' : 'Delivery Photo' }}
                </h3>
                @if($pod->pod_file || $pod->pod_photo)
                    <div class="flex justify-center">
                        @if($pod->pod_type === 'photo' && $pod->pod_photo)
                            <img src="{{ route('domestic.manifests.pods.file', ['id' => $pod->id, 'type' => 'photo']) }}"
                                 alt="Delivery Photo" 
                                 class="pod-image rounded-lg border shadow-sm">
                        @elseif($pod->pod_type === 'file' && $pod->pod_file)
                            <div class="text-center p-8 bg-gray-50 rounded-lg border w-full">
                                <i class="fas fa-file-pdf text-5xl text-red-500 mb-3 block"></i>
                                <p class="text-gray-600">{{ basename($pod->pod_file) }}</p>
                                <a href="{{ route('domestic.manifests.pods.file', ['id' => $pod->id, 'type' => 'file']) }}"
                                   target="_blank" class="mt-3 inline-block bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
                                    <i class="fas fa-eye mr-2"></i> View File
                                </a>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-file text-4xl block mb-2 text-gray-300"></i>
                        <p>No POD file uploaded</p>
                    </div>
                @endif
            </div>

            <!-- Status Update (Admin Only) -->
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isDomesticAdmin())
                <div class="border-t pt-4">
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Update Status</h3>
                    <form method="POST" action="{{ route('domestic.manifests.pods.update-status', $pod->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <select name="status" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    <option value="pending" {{ $pod->status === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                    <option value="uploaded" {{ $pod->status === 'uploaded' ? 'selected' : '' }}>📤 Uploaded</option>
                                    <option value="verified" {{ $pod->status === 'verified' ? 'selected' : '' }}>✅ Verified</option>
                                    <option value="rejected" {{ $pod->status === 'rejected' ? 'selected' : '' }}>❌ Rejected</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <input type="text" name="notes" placeholder="Status notes..." 
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                                <i class="fas fa-save mr-2"></i> Update Status
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
