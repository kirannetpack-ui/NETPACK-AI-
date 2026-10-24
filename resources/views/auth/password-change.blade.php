<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - NetPack Logistics</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="np-system np-legacy-screen bg-gray-50 font-sans">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="flex items-center justify-center gap-2 mb-2">
                    <i class="fas fa-box text-3xl text-teal-600"></i>
                    <h1 class="text-2xl font-bold text-gray-800">NetPack Logistics</h1>
                </div>
                <p class="text-sm text-gray-500">Change your password</p>
            </div>

            @if(session('warning'))
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('warning') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.change.submit') }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required 
                           class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('email') border-red-500 @enderror"
                           placeholder="Enter your email">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Current Password</label>
                    <input type="password" name="current_password" required 
                           class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('current_password') border-red-500 @enderror"
                           placeholder="Enter your current password">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">New Password</label>
                    <input type="password" name="password" required 
                           class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('password') border-red-500 @enderror"
                           placeholder="Enter new password">
                    <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-1">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required 
                           class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
                           placeholder="Confirm new password">
                </div>

                <button type="submit" class="w-full bg-teal-600 text-white py-3 rounded-lg hover:bg-teal-700 transition font-semibold">
                    <i class="fas fa-key mr-2"></i> Change Password
                </button>
            </form>

            <div class="text-center mt-6">
                <p class="text-sm text-gray-600">
                    <a href="{{ route('login') }}" class="text-teal-600 hover:underline">Back to Login</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>