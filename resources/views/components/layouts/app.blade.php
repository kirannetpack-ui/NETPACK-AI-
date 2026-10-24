<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COURIER with NETPACK</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind CSS CDN (quick fix) -->
    
    @livewireStyles
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="np-system np-legacy-screen bg-gray-100">
    <div class="min-h-screen">
        <!-- Simple Navigation -->
        <nav class="bg-white shadow-md">
            <div class="container mx-auto px-6 py-3">
                <div class="flex justify-between items-center">
                    <div class="text-xl font-bold text-teal-600">
                        📦 COURIER with NETPACK
                    </div>
                    <div class="space-x-4">
                        <a href="/" class="text-gray-600 hover:text-teal-600">Home</a>
                        <a href="/grocery-box" class="text-gray-600 hover:text-teal-600">Grocery Box</a>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Main Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
    
    @livewireScripts
</body>
</html>
