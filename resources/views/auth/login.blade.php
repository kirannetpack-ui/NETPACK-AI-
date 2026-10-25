<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign in — COURIER with NETPACK</title>
    <x-pwa-head />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="np-system">
<a href="#sign-in" class="np-skip">Skip to sign in</a>
<div class="np-auth-shell">
    <aside class="np-auth-story">
        <x-wordmark />
        <div><p class="np-eyebrow" style="color:#c99a77">A connected courier workspace</p><h1>Good deliveries<br>start with<br>clear operations.</h1><p>One place for your shipments, delivery partners and the details that keep everything moving.</p><div style="display:flex;gap:24px;margin-top:36px;font-size:10px;color:#9fb3c1;letter-spacing:.08em"><span>DOMESTIC</span><span>INTERNATIONAL</span><span>E-COMMERCE</span></div></div>
        <footer>COURIER WITH NETPACK / NEPAL</footer>
    </aside>
    <main class="np-auth-main" id="sign-in">
        @php($previewOnly = config('deployment.mode') === 'public-preview')
        @php($demoMode = config('deployment.mode') === 'demo')
        <p class="np-eyebrow">Welcome to your workspace</p>
        <h2>Sign in to NETPACK</h2>
        <p class="np-auth-description">Access your account and pick up where you left off.</p>
        @if($previewOnly)
        <div class="np-alert" role="status"><strong>Online demo setup is pending.</strong><br>The login page is available, but sign-in requires a connected database and seeded accounts. The test credentials below are currently available on localhost only.</div>
        @endif
        @if($demoMode)
        <div class="np-alert" role="status"><strong>Demonstration environment.</strong><br>Use the temporary test accounts below to review the workflow. Do not use this environment for live customer or financial operations.</div>
        @endif
        @if(session('error'))<div class="np-alert" role="alert">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="np-alert" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @if(session('success'))<div class="np-alert" role="status">{{ session('success') }}</div>@endif
        <form method="POST" action="{{ route('login.submit') }}" id="loginForm" class="np-auth-form" x-data="{ showPassword: false }">
            @csrf
            <div><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@company.com" required autocomplete="username" autofocus @disabled($previewOnly)></div>
            <div><div class="np-auth-password-label"><label for="password">Password</label><button type="button" @click="showPassword = !showPassword" :aria-pressed="showPassword" x-text="showPassword ? 'Hide password' : 'Show password'">Show password</button></div><input id="password" :type="showPassword ? 'text' : 'password'" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password" @disabled($previewOnly)></div>
            <div class="np-auth-meta"><label><input type="checkbox" name="remember" @disabled($previewOnly) {{ old('remember') ? 'checked' : '' }}> Keep me signed in</label><a href="{{ route('tracking.page') }}">Track without signing in ↗</a></div>
            <button type="submit" class="np-button" id="loginBtn" @disabled($previewOnly) style="{{ $previewOnly ? 'opacity:.5;cursor:not-allowed' : '' }}">{{ $previewOnly ? 'Sign-in awaiting database setup' : 'Sign in' }} <span aria-hidden="true">→</span></button>
        </form>
        <div class="np-auth-footer">New to NETPACK? <a href="{{ route('register') }}">Create an account ↗</a></div>
        @if(app()->environment(['local', 'testing']) || $previewOnly || $demoMode)
        <details class="np-auth-demo" @if($previewOnly || $demoMode) open @endif><summary>Development preview · demo accounts</summary><div>
            @foreach([
                ['superadmin@netpack.test', 'Netpack!Admin#2026', 'Super Admin'],
                ['international.admin@netpack.test', 'Netpack!International#2026', 'Global Admin'],
                ['domestic.admin@netpack.test', 'Netpack!Domestic#2026', 'Domestic Admin'],
                ['seller@netpack.test', 'Netpack!Seller#2026', 'Seller Merchant'],
                ['rider@netpack.test', 'Netpack!Rider#2026', 'Delivery Rider'],
                ['customer@netpack.test', 'Netpack!Customer#2026', 'Customer'],
                ['staff@netpack.test', 'Netpack!Staff#2026', 'Operations Staff'],
                ['partner@netpack.test', 'Netpack!Partner#2026', 'Domestic Partner'],
                ['overseas@netpack.test', 'Netpack!Overseas#2026', 'Overseas Partner'],
                ['client@netpack.test', 'Netpack!Client#2026', 'Business Client'],
            ] as $demo)
            @if($previewOnly || $demoMode)
            <section style="grid-column:1/-1;border-bottom:1px solid #e1e6e9;padding:10px 0"><strong>{{ $demo[2] }}</strong><br><span style="overflow-wrap:anywhere">{{ $demo[0] }}</span><br><code>{{ $demo[1] }}</code></section>
            @else
            <button type="button" onclick="fillRole(@js($demo[0]), @js($demo[1]))">{{ $demo[2] }}</button>
            @endif
            @endforeach
        </div></details>
        @endif
        <p style="font-size:10px;color:#95a0a8;margin-top:35px">&copy; {{ now()->year }} COURIER with NETPACK</p>
    </main>
</div>
<script>
function fillRole(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
}
</script>
<x-pwa-install-banner />
</body>
</html>
