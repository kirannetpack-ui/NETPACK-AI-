{{-- resources/views/auth/verification-pending.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Pending - COURIER with NETPACK</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="np-system np-legacy-screen bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12">
        <div class="bg-white rounded-2xl shadow-xl p-8 max-w-2xl w-full mx-4">
            <div class="text-center">
                <!-- Animated Icon -->
                <div class="w-24 h-24 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-clock text-yellow-600 text-5xl animate-pulse"></i>
                </div>
                
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Application Under Review</h1>
                <p class="text-gray-600 mb-6">Thank you for registering with COURIER with NETPACK</p>
                
                <!-- Professional Message Box -->
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6 text-left">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-yellow-500 mr-3 mt-1"></i>
                        <div>
                            <h3 class="font-semibold text-yellow-800">Application Status: Pending Verification</h3>
                            <p class="text-sm text-yellow-700 mt-1">
                                Your application has been received and is currently under review by our verification team.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- What to expect -->
                <div class="bg-gray-50 rounded-xl p-6 mb-6 text-left">
                    <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-list-check text-teal-600"></i>
                        <span>What to expect next:</span>
                    </h3>
                    <ul class="space-y-3 text-sm text-gray-600">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                            <span><strong>Document Verification:</strong> Our team will review your submitted documents (24-48 hours)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-phone-alt text-blue-500 mt-0.5"></i>
                            <span><strong>Verification Call:</strong> You may receive a call from our representative for verification</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-building text-purple-500 mt-0.5"></i>
                            <span><strong>Office Visit (if required):</strong> For business partners, we may schedule an office visit</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-envelope text-teal-500 mt-0.5"></i>
                            <span><strong>Email Notification:</strong> You will receive an email once your application is approved</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="bg-teal-50 rounded-xl p-4 mb-6 text-left">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-headset text-teal-600 text-2xl"></i>
                        <div>
                            <p class="text-sm text-teal-800 font-medium">Need assistance?</p>
                            <p class="text-xs text-teal-600">Contact our support team at <strong>support@couriernetpack.com</strong> or call <strong>+977-1-5970123</strong></p>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                       class="flex-1 bg-gray-200 text-gray-700 py-2 rounded-lg hover:bg-gray-300 transition text-center">
                        Logout
                    </a>
                    <a href="{{ url('/') }}" class="flex-1 bg-teal-600 text-white py-2 rounded-lg hover:bg-teal-700 transition text-center">
                        Return to Home
                    </a>
                </div>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                
                <p class="text-xs text-gray-400 mt-6">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Your information is secure and will only be used for verification purposes.
                </p>
            </div>
        </div>
    </div>
    
    <style>
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</body>
</html>
