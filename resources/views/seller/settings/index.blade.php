@extends('layouts.seller')

@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b">
            <h1 class="text-xl font-semibold text-gray-800">⚙️ Settings</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your account settings and preferences</p>
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

            <!-- Profile Settings -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">👤 Profile Settings</h3>
                <form method="POST" action="{{ route('seller.settings.update-profile') }}">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Business Name</label>
                            <input type="text" name="business_name" value="{{ old('business_name', $user->business_name) }}" 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">Business Address</label>
                            <textarea name="business_address" rows="2" 
                                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">{{ old('business_address', $user->business_address) }}</textarea>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                            <i class="fas fa-save mr-2"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Settings -->
            <div class="mb-8 border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">🔑 Change Password</h3>
                <form method="POST" action="{{ route('seller.settings.update-password') }}">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Current Password <span class="text-red-500">*</span></label>
                            <input type="password" name="current_password" required 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div></div>
                        <div>
                            <label class="block text-sm font-medium mb-1">New Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password" required 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Confirm Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password_confirmation" required 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                            <i class="fas fa-key mr-2"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- COD Settlement & Payout Accounts (Bank, QR, eSewa, Khalti) -->
            <div class="mb-8 border-t pt-6">
                <div class="flex items-center justify-between border-b pb-2 mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">🏦 COD Settlement & Payout Accounts</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Configure your Bank Account, Banking QR, eSewa, or Khalti for automatic Cash on Delivery settlements.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider">
                        Settlement Destination
                    </span>
                </div>

                <form method="POST" action="{{ route('seller.settings.update-bank') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-4">
                        <!-- 1. Commercial Bank Details -->
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3 flex items-center gap-1.5">
                                <i class="fas fa-building-columns text-emerald-600"></i>
                                <span>Commercial Bank Account</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">Bank Name</label>
                                    <input type="text" name="bank_name" value="{{ old('bank_name', $user->bank_name) }}" 
                                           class="w-full text-xs px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                           placeholder="e.g. Nabil Bank, Global IME, NIC Asia">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">Account Holder Name</label>
                                    <input type="text" name="account_holder_name" value="{{ old('account_holder_name', $user->account_holder_name) }}" 
                                           class="w-full text-xs px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                           placeholder="Name as registered on bank">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">Account Number</label>
                                    <input type="text" name="account_number" value="{{ old('account_number', $user->account_number) }}" 
                                           class="w-full text-xs px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                           placeholder="Bank account number">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">Account Type & Branch</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <select name="account_type" class="text-xs px-2 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                            <option value="savings" {{ old('account_type', $user->account_type) === 'savings' ? 'selected' : '' }}>Savings</option>
                                            <option value="current" {{ old('account_type', $user->account_type) === 'current' ? 'selected' : '' }}>Current</option>
                                        </select>
                                        <input type="text" name="ifsc_code" value="{{ old('ifsc_code', $user->ifsc_code) }}" 
                                               class="text-xs px-2 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                               placeholder="Branch / Swift">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Digital Wallets & QR Code -->
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3 flex items-center gap-1.5">
                                <i class="fas fa-qrcode text-teal-600"></i>
                                <span>Digital Wallets & Banking QR</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">
                                        <span class="text-emerald-600 font-bold">eSewa ID</span> / Mobile Number
                                    </label>
                                    <input type="text" name="esewa_id" value="{{ old('esewa_id', optional(\App\Models\SellerPaymentMethod::where('user_id', $user->id)->where('method_type', 'esewa')->first())->esewa_id) }}" 
                                           class="w-full text-xs px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                           placeholder="eSewa 98XXXXXXXX">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">
                                        <span class="text-purple-600 font-bold">Khalti ID</span> / Mobile Number
                                    </label>
                                    <input type="text" name="khalti_id" value="{{ old('khalti_id', optional(\App\Models\SellerPaymentMethod::where('user_id', $user->id)->where('method_type', 'khalti')->first())->khalti_id) }}" 
                                           class="w-full text-xs px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                           placeholder="Khalti 98XXXXXXXX">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">
                                        <span>Upload Banking / Wallet QR</span>
                                    </label>
                                    <input type="file" name="qr_code" accept=".jpg,.jpeg,.png"
                                           class="w-full text-xs px-2 py-1.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white">
                                    <p class="text-[10px] text-slate-400 mt-0.5">JPG, PNG (Max 3MB)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="bg-teal-600 text-white px-6 py-2.5 rounded-xl hover:bg-teal-700 font-bold text-xs shadow-sm transition">
                            <i class="fas fa-save mr-1.5"></i> Save Settlement & Payout Details
                        </button>
                    </div>
                </form>
            </div>

            <!-- Notification Settings -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">🔔 Notification Settings</h3>
                <form method="POST" action="{{ route('seller.settings.update-notifications') }}">
                    @csrf
                    @method('PUT')
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-700">Email Notifications</p>
                                <p class="text-sm text-gray-500">Receive order updates via email</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="email_notifications" value="1" 
                                       {{ old('email_notifications', $user->email_notifications ?? true) ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-700">SMS Notifications</p>
                                <p class="text-sm text-gray-500">Receive order updates via SMS</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="sms_notifications" value="1" 
                                       {{ old('sms_notifications', $user->sms_notifications ?? false) ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-700">Order Status Updates</p>
                                <p class="text-sm text-gray-500">Get notified when order status changes</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="order_updates" value="1" 
                                       {{ old('order_updates', $user->order_updates ?? true) ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600"></div>
                            </label>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition">
                            <i class="fas fa-save mr-2"></i> Update Notification Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection