<!-- resources/views/components/seller-sidebar.blade.php -->
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="{{ asset('images/logo.png') }}" alt="NetPack Logo" width="120">
        </div>
        <div class="sidebar-user">
            <div class="user-avatar">
                @if(Auth::user()->profile_photo)
                    <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="Profile">
                @else
                    <div class="avatar-placeholder">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                @endif
            </div>
            <div class="user-info">
                <h4>{{ Auth::user()->name }}</h4>
                <span class="user-role">Seller</span>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <!-- Dashboard -->
            @if(Route::has('seller.dashboard'))
            <li class="{{ request()->routeIs('seller.dashboard') ? 'active' : '' }}">
                <a href="{{ route('seller.dashboard') }}">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            @endif

            <!-- Products -->
            @if(Route::has('seller.products'))
            <li class="{{ request()->routeIs('seller.products*') ? 'active' : '' }}">
                <a href="{{ route('seller.products') }}">
                    <i class="fas fa-box"></i>
                    <span>My Products</span>
                    @php
                        $productCount = 0;
                        try {
                            $productCount = Auth::user()->products()->count();
                        } catch (\Exception $e) {
                            $productCount = 0;
                        }
                    @endphp
                    <span class="badge">{{ $productCount }}</span>
                </a>
            </li>
            @endif

            <!-- Orders -->
            @if(Route::has('seller.orders'))
            <li class="{{ request()->routeIs('seller.orders*') ? 'active' : '' }}">
                <a href="{{ route('seller.orders') }}">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                    @php
                        $orderCount = 0;
                        try {
                            if (class_exists('App\Models\Order')) {
                                $orderCount = \App\Models\Order::where('seller_id', Auth::id())
                                                              ->where('status', 'pending')
                                                              ->count();
                            }
                        } catch (\Exception $e) {
                            $orderCount = 0;
                        }
                    @endphp
                    @if($orderCount > 0)
                        <span class="badge badge-warning">{{ $orderCount }}</span>
                    @endif
                </a>
            </li>
            @endif

            <!-- Earnings -->
            @if(Route::has('seller.earnings'))
            <li class="{{ request()->routeIs('seller.earnings*') ? 'active' : '' }}">
                <a href="{{ route('seller.earnings') }}">
                    <i class="fas fa-wallet"></i>
                    <span>Earnings</span>
                </a>
            </li>
            @endif

            <!-- Wallet -->
            @if(Route::has('seller.wallet'))
            <li class="{{ request()->routeIs('seller.wallet*') ? 'active' : '' }}">
                <a href="{{ route('seller.wallet') }}">
                    <i class="fas fa-credit-card"></i>
                    <span>Wallet</span>
                </a>
            </li>
            @endif

            <!-- Withdraw -->
            @if(Route::has('seller.withdraw'))
            <li class="{{ request()->routeIs('seller.withdraw*') ? 'active' : '' }}">
                <a href="{{ route('seller.withdraw') }}">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Withdraw</span>
                </a>
            </li>
            @endif

            <!-- Shipments -->
            @if(Route::has('seller.shipments'))
            <li class="{{ request()->routeIs('seller.shipments*') ? 'active' : '' }}">
                <a href="{{ route('seller.shipments') }}">
                    <i class="fas fa-truck"></i>
                    <span>Shipments</span>
                </a>
            </li>
            @endif

            <!-- E-commerce -->
            @if(Route::has('ecommerce.seller.dashboard'))
            <li class="{{ request()->routeIs('ecommerce.seller.*') ? 'active' : '' }}">
                <a href="{{ route('ecommerce.seller.dashboard') }}">
                    <i class="fas fa-store"></i>
                    <span>E-commerce</span>
                </a>
            </li>
            @endif

            <!-- Notifications -->
            @if(Route::has('notifications.index'))
            <li class="{{ request()->routeIs('notifications*') ? 'active' : '' }}">
                <a href="{{ route('notifications.index') }}">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                    @php
                        $notificationCount = 0;
                        try {
                            $notificationCount = Auth::user()->unreadNotifications()->count();
                        } catch (\Exception $e) {
                            $notificationCount = 0;
                        }
                    @endphp
                    @if($notificationCount > 0)
                        <span class="badge badge-danger">{{ $notificationCount }}</span>
                    @endif
                </a>
            </li>
            @endif

            <!-- Messages/Chat -->
            @if(Route::has('chat.index'))
            <li class="{{ request()->routeIs('chat*') ? 'active' : '' }}">
                <a href="{{ route('chat.index') }}">
                    <i class="fas fa-comment-dots"></i>
                    <span>Messages</span>
                </a>
            </li>
            @endif

            <!-- Support -->
            @if(Route::has('seller.support'))
            <li class="{{ request()->routeIs('seller.support*') ? 'active' : '' }}">
                <a href="{{ route('seller.support') }}">
                    <i class="fas fa-headset"></i>
                    <span>Support</span>
                </a>
            </li>
            @endif

            <!-- Settings -->
            @if(Route::has('seller.settings'))
            <li class="{{ request()->routeIs('seller.settings*') ? 'active' : '' }}">
                <a href="{{ route('seller.settings') }}">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            @endif

            <!-- Profile -->
            @if(Route::has('profile'))
            <li class="{{ request()->routeIs('profile*') ? 'active' : '' }}">
                <a href="{{ route('profile') }}">
                    <i class="fas fa-user"></i>
                    <span>My Profile</span>
                </a>
            </li>
            @endif
        </ul>
    </nav>

    <div class="sidebar-footer">
        <!-- Logout -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>