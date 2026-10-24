<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Pending Approval - NetPack Logistics</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .pulse-animation {
            animation: pulse 2s infinite;
        }
    </style>
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="np-system np-legacy-screen bg-gray-50 font-sans">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-teal-600 text-white py-4 shadow-lg">
            <div class="container mx-auto px-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fas fa-box text-2xl"></i>
                    <h1 class="text-xl font-bold">NetPack Logistics</h1>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm bg-teal-700 px-3 py-1 rounded-full">
                        <i class="fas fa-clock mr-1"></i> Pending
                    </span>
                    <a href="{{ route('login') }}" class="text-sm hover:underline">
                        <i class="fas fa-sign-in-alt mr-1"></i> Login
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="flex-1 container mx-auto px-4 py-12">
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                    <!-- Icon -->
                    <div class="w-24 h-24 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6 pulse-animation">
                        <i class="fas fa-clock text-4xl text-yellow-600"></i>
                    </div>

                    <h1 class="text-2xl font-bold text-gray-800 mb-2">Account Pending Approval</h1>
                    <p class="text-gray-600 mb-6">
                        Thank you for registering with <strong>NetPack Logistics</strong>! 
                        Your account is currently being reviewed by our team.
                    </p>

                    <!-- User Info -->
                    @if(session('user'))
                        <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                            <h3 class="font-semibold text-gray-700 mb-2">Registration Details:</h3>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <span class="text-gray-500">Name:</span>
                                <span class="font-medium">{{ session('user')->name }}</span>
                                <span class="text-gray-500">Email:</span>
                                <span class="font-medium">{{ session('user')->email }}</span>
                                <span class="text-gray-500">Type:</span>
                                <span class="font-medium">{{ ucfirst(session('user')->user_type) }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- What happens next -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-left">
                        <h3 class="font-semibold text-blue-800 mb-2">
                            <i class="fas fa-info-circle mr-2"></i> What happens next?
                        </h3>
                        <ul class="text-sm text-blue-700 space-y-2">
                            <li class="flex items-start gap-2">
                                <i class="fas fa-check-circle text-blue-500 mt-1"></i>
                                <span>Our admin team will review your registration details</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-check-circle text-blue-500 mt-1"></i>
                                <span>You will receive an email notification upon approval</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-check-circle text-blue-500 mt-1"></i>
                                <span>Once approved, you can login and complete your profile</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-check-circle text-blue-500 mt-1"></i>
                                <span>You will need to complete KYC verification after login</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Services Info -->
                    <div class="border-t pt-6 mt-6">
                        <h3 class="font-semibold text-gray-700 mb-4">Our Services</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="p-3 bg-gray-50 rounded-lg hover:bg-teal-50 transition">
                                <i class="fas fa-truck text-teal-600 text-2xl block mb-1"></i>
                                <span class="text-xs text-gray-600">Domestic Delivery</span>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg hover:bg-teal-50 transition">
                                <i class="fas fa-globe text-teal-600 text-2xl block mb-1"></i>
                                <span class="text-xs text-gray-600">International Shipping</span>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg hover:bg-teal-50 transition">
                                <i class="fas fa-store text-teal-600 text-2xl block mb-1"></i>
                                <span class="text-xs text-gray-600">E-commerce Solutions</span>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg hover:bg-teal-50 transition">
                                <i class="fas fa-hand-holding-box text-teal-600 text-2xl block mb-1"></i>
                                <span class="text-xs text-gray-600">Grocery Delivery</span>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="mt-6 pt-6 border-t">
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-headset text-teal-600 mr-2"></i>
                            Need help? Contact our support team at 
                            <a href="mailto:support@netpack.com" class="text-teal-600 hover:underline">support@netpack.com</a>
                            or call 
                            <a href="tel:+977-9800000000" class="text-teal-600 hover:underline">+977-9800000000</a>
                        </p>
                    </div>

                    <div class="mt-6 flex gap-3 justify-center">
                        <a href="{{ route('login') }}" class="bg-teal-600 text-white px-6 py-3 rounded-lg hover:bg-teal-700 transition">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Login
                        </a>
                        <a href="/" class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition">
                            <i class="fas fa-home mr-2"></i> Home
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-4">
            <div class="container mx-auto px-4 text-center text-sm">
                <p>&copy; {{ date('Y') }} NetPack Logistics. All rights reserved.</p>
            </div>
        </footer>
    </div>
</body>
</html>