<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - COURIER with NETPACK Agency</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="np-system np-legacy-screen bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-xl p-8 max-w-md w-full mx-4">
            <div class="text-center mb-6">
                <i class="fas fa-user-tie text-teal-600 text-5xl mb-3"></i>
                <h1 class="text-2xl font-bold">Agency Staff Login</h1>
                <p class="text-gray-500">Sign in to manage shipments</p>
            </div>
            
            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ $errors->first() }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('agency.staff.login.submit') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Email</label>
                    <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Password</label>
                    <input type="password" name="password" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                <button type="submit" class="w-full bg-teal-600 text-white py-2 rounded-lg hover:bg-teal-700">
                    Login
                </button>
            </form>
        </div>
    </div>
</body>
</html>
