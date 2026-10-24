@extends('layouts.app')

@section('title', 'Add Domestic Partner')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">Add Domestic Partner</h1>
            <p class="text-sm text-gray-500 mt-1">Create a new domestic delivery partner</p>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('domestic.partners.store') }}">
                @csrf

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
                        <label class="block text-sm font-medium mb-1">Partner Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Company Name *</label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Phone *</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Contact Person</label>
                        <input type="text" name="contact_person" value="{{ old('contact_person') }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">City</label>
                        <input type="text" name="city" value="{{ old('city') }}" 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div class="md:col-span-2">
                        <x-nepal-territory-picker 
                            provinceName="province"
                            districtName="district"
                            :selectedProvince="old('province')"
                            :selectedDistrict="old('district')"
                            provinceLabel="Operating Province / Sector"
                            districtLabel="Operating District"
                            helperText="Select province to display strictly its affiliated 77 districts with quick search."
                            :required="false"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Password *</label>
                        <input type="password" name="password" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Confirm Password *</label>
                        <input type="password" name="password_confirmation" required 
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Status</label>
                        <select name="verification_status" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="pending" {{ old('verification_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ old('verification_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="suspended" {{ old('verification_status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex gap-3 pt-4 border-t">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-save mr-2"></i> Create Partner
                    </button>
                    <a href="{{ route('domestic.partners') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection