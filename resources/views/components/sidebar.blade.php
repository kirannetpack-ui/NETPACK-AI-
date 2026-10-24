<div x-data="{ open: true }" class="fixed left-0 top-0 h-full bg-gradient-to-b from-gray-900 to-gray-800 text-white z-50 transition-all duration-300"
     :class="open ? 'w-64' : 'w-20'"
     style="overflow-y: auto; overflow-x: hidden; scrollbar-width: thin;">
    
    <!-- Logo -->
    <div class="p-4 border-b border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2" :class="{'justify-center': !open}">
                <i class="fas fa-box-open text-teal-400 text-2xl"></i>
                <span class="font-bold text-lg transition-opacity" x-show="open">COURIER with NETPACK</span>
            </div>
            <button @click="open = !open" class="text-gray-400 hover:text-white">
                <i class="fas" :class="open ? 'fa-chevron-left' : 'fa-chevron-right'"></i>
            </button>
        </div>
    </div>
    
    <!-- User Info -->
    <div class="p-4 border-b border-gray-700" x-show="open">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full flex items-center justify-center 
                @if(auth()->user()->user_type == 'admin') bg-red-600
                @elseif(auth()->user()->user_type == 'seller') bg-blue-600
                @elseif(auth()->user()->user_type == 'rider') bg-green-600
                @else bg-teal-600 @endif">
                <span class="font-semibold text-white">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
            </div>
            <div>
                <p class="font-medium text-sm">{{ auth()->user()->name ?? 'Guest' }}</p>
                <p class="text-xs px-2 py-0.5 rounded-full inline-block 
                    @if(auth()->user()->user_type == 'admin') bg-red-500/20 text-red-400
                    @elseif(auth()->user()->user_type == 'seller') bg-blue-500/20 text-blue-400
                    @elseif(auth()->user()->user_type == 'rider') bg-green-500/20 text-green-400
                    @else bg-teal-500/20 text-teal-400 @endif">
                    {{ ucfirst(auth()->user()->user_type ?? 'Customer') }}
                </p>
            </div>
        </div>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="mt-4 px-3">
        @php
            $userType = auth()->user()->user_type ?? 'customer';
            
           @if($userType == 'admin') {
    $menus = [
        ['icon' => 'fa-tachometer-alt', 'label' => 'Dashboard', 'route' => 'admin.dashboard', 'color' => 'text-teal-400'],
        ['icon' => 'fa-boxes', 'label' => 'Products', 'route' => 'admin.products.index', 'color' => 'text-blue-400'],
        ['icon' => 'fa-truck', 'label' => 'Shipments', 'route' => 'admin.shipments', 'color' => 'text-green-400'],
        ['icon' => 'fa-users', 'label' => 'Users', 'route' => 'admin.users.index', 'color' => 'text-purple-400'],
        ['icon' => 'fa-user-tie', 'label' => 'Staff', 'route' => 'admin.staff.index', 'color' => 'text-indigo-400'],
        ['icon' => 'fa-handshake', 'label' => 'Domestic Partners', 'route' => 'admin.partners.index', 'color' => 'text-yellow-400'],
        ['icon' => 'fa-network-wired', 'label' => 'International Hubs', 'route' => 'international.hubs.index', 'color' => 'text-pink-400'],
        ['icon' => 'fa-clipboard-list', 'label' => 'Pickups', 'route' => 'admin.pickups', 'color' => 'text-orange-400'],
        ['icon' => 'fa-chart-line', 'label' => 'Analytics', 'route' => 'admin.analytics', 'color' => 'text-red-400'],
        ['icon' => 'fa-wallet', 'label' => 'Settlements', 'route' => 'admin.settlements', 'color' => 'text-emerald-400'],
        ['icon' => 'fa-cog', 'label' => 'Settings', 'route' => 'admin.settings', 'color' => 'text-gray-400'],
    ];
}

            elseif($userType == 'seller') {
                $menus = [
                    ['icon' => 'fa-tachometer-alt', 'label' => 'Dashboard', 'route' => 'seller.dashboard', 'color' => 'text-teal-400'],
                    ['icon' => 'fa-box-open', 'label' => 'Grocery Box', 'route' => 'grocery.box', 'color' => 'text-blue-400'],
                    ['icon' => 'fa-shopping-cart', 'label' => 'E-commerce Orders', 'route' => 'ecommerce.seller.dashboard', 'color' => 'text-purple-400'],
                    ['icon' => 'fa-truck', 'label' => 'Pickup Requests', 'route' => 'domestic.pickup.my-requests', 'color' => 'text-green-400'],
                    ['icon' => 'fa-chart-line', 'label' => 'Earnings', 'route' => 'seller.earnings', 'color' => 'text-yellow-400'],
                    ['icon' => 'fa-wallet', 'label' => 'Wallet', 'route' => 'seller.wallet', 'color' => 'text-emerald-400'],
                    ['icon' => 'fa-store', 'label' => 'My Products', 'route' => 'seller.products', 'color' => 'text-orange-400'],
                    ['icon' => 'fa-cog', 'label' => 'Settings', 'route' => 'seller.settings', 'color' => 'text-gray-400'],
                ];
            }
            elseif($userType == 'rider') {
                $menus = [
                    ['icon' => 'fa-tachometer-alt', 'label' => 'Dashboard', 'route' => 'rider.dashboard', 'color' => 'text-teal-400'],
                    ['icon' => 'fa-truck-fast', 'label' => 'My Deliveries', 'route' => 'rider.deliveries', 'color' => 'text-blue-400'],
                    ['icon' => 'fa-wallet', 'label' => 'Earnings', 'route' => 'rider.earnings', 'color' => 'text-green-400'],
                    ['icon' => 'fa-history', 'label' => 'History', 'route' => 'rider.history', 'color' => 'text-yellow-400'],
                    ['icon' => 'fa-cog', 'label' => 'Settings', 'route' => 'rider.settings', 'color' => 'text-gray-400'],
                ];
            }
            else {
                // CLIENT / CUSTOMER MENU
                $menus = [
                    ['icon' => 'fa-home', 'label' => 'Dashboard', 'route' => 'client.dashboard', 'color' => 'text-teal-400'],
                    ['icon' => 'fa-box-open', 'label' => 'Grocery Box', 'route' => 'grocery.box', 'color' => 'text-blue-400'],
                    ['icon' => 'fa-globe-asia', 'label' => 'International Shipping', 'route' => 'shipments.create', 'color' => 'text-indigo-400'],
                    ['icon' => 'fa-truck-fast', 'label' => 'Domestic Delivery', 'route' => 'domestic.pickup.create', 'color' => 'text-green-400'],
                    ['icon' => 'fa-store', 'label' => 'E-commerce Store', 'route' => 'ecommerce.seller.dashboard', 'color' => 'text-purple-400'],
                    ['icon' => 'fa-search', 'label' => 'Track Shipment', 'route' => 'tracking.page', 'color' => 'text-yellow-400'],
                    ['icon' => 'fa-shopping-bag', 'label' => 'My Orders', 'route' => 'shipments.index', 'color' => 'text-orange-400'],
                    ['icon' => 'fa-wallet', 'label' => 'Wallet', 'route' => 'client.wallet', 'color' => 'text-emerald-400'],
                    ['icon' => 'fa-star', 'label' => 'Rate Us', 'route' => 'client.feedback', 'color' => 'text-pink-400'],
                    ['icon' => 'fa-headset', 'label' => 'Support', 'route' => 'client.support', 'color' => 'text-cyan-400'],
                    ['icon' => 'fa-user', 'label' => 'Profile', 'route' => 'profile', 'color' => 'text-gray-400'],
                    ['icon' => 'fa-cog', 'label' => 'Settings', 'route' => 'client.settings', 'color' => 'text-gray-400'],
                ];
            }
        @endphp
        
        @foreach($menus as $menu)
        <a href="{{ route($menu['route']) }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1 transition-all hover:bg-gray-700 {{ $menu['color'] }}"
           x-show="open">
            <i class="fas {{ $menu['icon'] }} w-5"></i>
            <span class="text-sm">{{ $menu['label'] }}</span>
        </a>
        <!-- Collapsed version -->
        <a href="{{ route($menu['route']) }}" 
           class="flex justify-center items-center px-2 py-2.5 rounded-lg mb-1 transition-all hover:bg-gray-700"
           x-show="!open" x-cloak>
            <i class="fas {{ $menu['icon'] }} text-lg"></i>
        </a>
        @endforeach
    </nav>
    
    <!-- Logout -->
    <div class="absolute bottom-0 left-0 right-0 p-3 border-t border-gray-700">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-700 transition w-full"
               x-show="open">
                <i class="fas fa-sign-out-alt w-5 text-red-400"></i>
                <span class="text-sm text-red-400">Logout</span>
            </button>
            <button type="submit"
               class="flex justify-center items-center px-2 py-2.5 rounded-lg hover:bg-gray-700 transition w-full"
               x-show="!open" x-cloak>
                <i class="fas fa-sign-out-alt text-lg text-red-400"></i>
            </button>
        </form>
    </div>
</div>

<style>
    .sidebar-transition { transition: all 0.3s ease; }
    [x-cloak] { display: none !important; }
</style>
