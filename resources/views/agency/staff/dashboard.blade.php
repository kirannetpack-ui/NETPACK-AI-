<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - {{ $staff->agency->name }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="np-system np-legacy-screen bg-gray-100">
    <nav class="bg-teal-700 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-user-tie text-2xl"></i>
                    <div>
                        <span class="font-bold text-lg">{{ $staff->agency->name }}</span>
                        <p class="text-xs text-teal-200">{{ $staff->name }} ({{ ucfirst($staff->position) }})</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('agency.staff.scan') }}" class="hover:text-teal-200">
                        <i class="fas fa-qrcode mr-1"></i> Scan QR
                    </a>
                    <a href="{{ route('agency.staff.shipments') }}" class="hover:text-teal-200">
                        <i class="fas fa-list mr-1"></i> Shipments
                    </a>
                    <form method="POST" action="{{ route('agency.staff.logout') }}">
                        @csrf
                        <button type="submit" class="hover:text-teal-200">
                            <i class="fas fa-sign-out-alt mr-1"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Your Role</p>
                        <p class="text-2xl font-bold text-teal-600">{{ ucfirst($staff->position) }}</p>
                    </div>
                    <i class="fas fa-id-card text-4xl text-teal-500"></i>
                </div>
                <div class="mt-3 text-sm text-gray-500">
                    @if($staff->can_scan_arrival)
                        <span class="inline-block bg-green-100 text-green-700 px-2 py-1 rounded text-xs mr-2">
                            ✓ Can mark Arrival
                        </span>
                    @endif
                    @if($staff->can_scan_departure)
                        <span class="inline-block bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs mr-2">
                            ✓ Can mark Departure
                        </span>
                    @endif
                    @if($staff->can_add_notes)
                        <span class="inline-block bg-purple-100 text-purple-700 px-2 py-1 rounded text-xs">
                            ✓ Can add Notes
                        </span>
                    @endif
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-teal-600 to-emerald-600 rounded-xl shadow-md p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-teal-100 text-sm">Welcome, {{ $staff->name }}!</p>
                        <p class="text-lg font-bold mt-1">Ready to process shipments</p>
                    </div>
                    <i class="fas fa-hand-peace text-4xl opacity-75"></i>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <a href="{{ route('agency.staff.scan') }}" class="block">
                <div class="bg-white rounded-xl shadow-md p-6 text-center hover:shadow-lg transition">
                    <i class="fas fa-qrcode text-5xl text-teal-600 mb-3"></i>
                    <h3 class="text-xl font-bold">Scan QR Code</h3>
                    <p class="text-gray-500 mt-1">Scan HAWB QR to update status</p>
                </div>
            </a>
            <a href="{{ route('agency.staff.shipments') }}" class="block">
                <div class="bg-white rounded-xl shadow-md p-6 text-center hover:shadow-lg transition">
                    <i class="fas fa-boxes text-5xl text-teal-600 mb-3"></i>
                    <h3 class="text-xl font-bold">View Shipments</h3>
                    <p class="text-gray-500 mt-1">Track all processed shipments</p>
                </div>
            </a>
        </div>
    </div>
</body>
</html>