@extends('layouts.app')

@section('title', 'Edit Overseas Partner')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">Edit Overseas Partner</h1>
            <p class="text-sm text-gray-500 mt-1">Update international partner information</p>
        </div>
        
        <div class="p-6">
            <form method="POST" action="{{ route('admin.overseas-partners.update', $partner->id) }}">
                @csrf
                @method('PUT')
                
                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Company Name *</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $partner->company_name ?? $partner->name) }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('company_name') border-red-500 @enderror">
                        @error('company_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Contact Person *</label>
                        <input type="text" name="contact_person" value="{{ old('contact_person', $partner->contact_person ?? $partner->name) }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('contact_person') border-red-500 @enderror">
                        @error('contact_person')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Email Address *</label>
                        <input type="email" name="email" value="{{ old('email', $partner->email) }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Phone Number *</label>
                        <input type="tel" name="phone" value="{{ old('phone', $partner->phone) }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('phone') border-red-500 @enderror">
                        @error('phone')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Country *</label>
                        <input type="text" name="country" value="{{ old('country', $partner->country ?? '') }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('country') border-red-500 @enderror">
                        @error('country')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">City</label>
                        <input type="text" name="city" value="{{ old('city', $partner->city ?? '') }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Address</label>
                        <input type="text" name="address" value="{{ old('address', $partner->address ?? '') }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Status *</label>
                        <select name="verification_status" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('verification_status') border-red-500 @enderror">
                            <option value="pending" {{ old('verification_status', $partner->verification_status) === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ old('verification_status', $partner->verification_status) === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ old('verification_status', $partner->verification_status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        @error('verification_status')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Margin Percentage (%)</label>
                        <input type="number" name="margin_percentage" step="0.01" value="{{ old('margin_percentage', $partner->margin_percentage ?? 0) }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">New Password (optional)</label>
                        <input type="password" name="password" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('password') border-red-500 @enderror">
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>
                
                <div class="mt-6 flex gap-3 pt-4 border-t">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-save mr-2"></i> Update Partner
                    </button>
                    <a href="{{ route('admin.overseas-partners.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection