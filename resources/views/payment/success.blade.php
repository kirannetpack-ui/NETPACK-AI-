{{-- resources/views/payment/success.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - NETPACK</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="np-system np-legacy-screen bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full mx-4">
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check-circle text-green-600 text-4xl"></i>
                </div>
                
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Payment Successful! 🎉</h1>
                <p class="text-gray-600 mb-6">Your order has been confirmed and payment is complete.</p>
                
                <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                    <p class="text-sm text-gray-500">Order Details</p>
                    <p class="font-semibold">Tracking Number: {{ $shipment->tracking_number }}</p>
                    <p class="text-sm text-gray-600">HAWB: {{ $shipment->hawb_number }}</p>
                    <p class="text-sm text-gray-600">Amount Paid: NPR {{ number_format($shipment->total_amount, 2) }}</p>
                </div>
                
                <div class="space-y-3">
                    <a href="{{ route('tracking.show', $shipment->tracking_number) }}" 
                       class="block w-full bg-teal-600 text-white py-3 rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-map-marker-alt mr-2"></i> Track Your Order
                    </a>
                    <a href="{{ url('/') }}" 
                       class="block w-full bg-gray-200 text-gray-700 py-3 rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-home mr-2"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>