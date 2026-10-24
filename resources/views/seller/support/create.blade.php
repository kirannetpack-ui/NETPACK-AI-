@extends('layouts.seller')

@section('title', 'Create Support Ticket')
@section('page-title', 'Create Support Ticket')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Create Support Ticket</h1>
                <p class="text-sm text-gray-500 mt-1">Describe your issue and we'll help you resolve it</p>
            </div>
            <a href="{{ route('seller.support') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Tickets
            </a>
        </div>

        <div class="p-6">
            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('seller.support.create') }}">
                @csrf

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Subject <span class="text-red-500">*</span></label>
                        <input type="text" name="subject" value="{{ old('subject') }}" required 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                               placeholder="Brief summary of your issue">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Category <span class="text-red-500">*</span></label>
                        <select name="category" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">Select Category</option>
                            <option value="order">📦 Order Issue</option>
                            <option value="payment">💳 Payment Issue</option>
                            <option value="delivery">🚚 Delivery Issue</option>
                            <option value="product">📦 Product Issue</option>
                            <option value="account">👤 Account Issue</option>
                            <option value="technical">⚙️ Technical Issue</option>
                            <option value="other">📝 Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Priority</label>
                        <select name="priority" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="normal">🟢 Normal</option>
                            <option value="medium">🟡 Medium</option>
                            <option value="high">🔴 High</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Description <span class="text-red-500">*</span></label>
                        <textarea name="description" rows="5" required 
                                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                  placeholder="Please describe your issue in detail">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Attach Order (Optional)</label>
                        <select name="order_id" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">Select related order</option>
                            @foreach($orders ?? [] as $order)
                                <option value="{{ $order->id }}">#{{ $order->order_number }} - {{ $order->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 pt-4 border-t mt-6">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-paper-plane mr-2"></i> Submit Ticket
                    </button>
                    <a href="{{ route('seller.support') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection