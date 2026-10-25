<header class="np-topbar sticky top-0 z-30">
    <div class="np-topbar-inner">
        <div class="flex items-center gap-3 min-w-0">
            <button @click="typeof toggleSidebar === 'function' ? toggleSidebar() : sidebarOpen = !sidebarOpen"
                    type="button" class="lg:hidden p-2 rounded-md" aria-label="Toggle Sidebar" :aria-expanded="sidebarOpen" aria-controls="workspace-navigation" title="Toggle Sidebar">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>
            <div class="min-w-0">
                <p class="np-topbar-title truncate">@yield('page-title', 'COURIER with NETPACK')</p>
                <p class="np-topbar-subtitle">{{ ucwords(str_replace('_', ' ', auth()->user()?->user_type ?? 'Courier')) }} workspace / Nepal</p>
            </div>
        </div>
        <form action="{{ route('tracking.search') }}" method="GET" class="np-search" role="search">
            <label for="workspace-tracking" class="sr-only">Tracking number</label>
            <input id="workspace-tracking" name="tracking" type="search" placeholder="Search a tracking number" required>
            <button type="submit" aria-label="Track shipment"><i class="fas fa-search" aria-hidden="true"></i></button>
        </form>
        <div class="np-topbar-actions">
            <button type="button" class="inline-flex items-center gap-2 text-xs"
                    onclick="window.dispatchEvent(new CustomEvent('open-ai-copilot'))" title="Ask Chanda AI Assistant">
                <i class="far fa-comment-dots" aria-hidden="true"></i><span class="hidden sm:inline">Assistant</span>
            </button>
            <a href="{{ route('notifications.index') }}" class="relative p-1.5" aria-label="Notifications">
                <i class="far fa-bell" aria-hidden="true"></i>
                @if(auth()->check() && auth()->user()->unreadNotifications()->exists())
                    <span class="absolute top-0 right-0 w-1.5 h-1.5 rounded-full bg-teal-600"></span>
                @endif
            </a>
            @auth
                <a href="{{ route('profile') }}" class="inline-flex items-center gap-2" title="View Profile">
                    <span class="np-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                    <span class="text-xs hidden xl:block">{{ auth()->user()->name }}</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="text-xs">Sign in</a>
            @endauth
        </div>
    </div>
</header>
