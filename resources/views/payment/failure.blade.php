{{-- resources/views/payment/failure.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - NETPACK</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="np-system np-legacy-screen bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full mx-4">
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-times-circle text-red-600 text-4xl"></i>
                </div>
                
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Payment Failed!</h1>
                <p class="text-gray-600 mb-6">We couldn't process your payment. Please try again.</p>
                
                <div class="space-y-3">
                    <a href="{{ url()->previous() }}" 
                       class="block w-full bg-teal-600 text-white py-3 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-arrow-left mr-2"></i> Try Again
                    </a>
                    <a href="{{ url('/') }}" 
                       class="block w-full bg-gray-200 text-gray-700 py-3 rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-headset mr-2"></i> Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>