<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign in — NETPACK</title>
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
        <p class="np-eyebrow">Welcome to your workspace</p>
        <h2>Sign in to NETPACK</h2>
        <p class="np-auth-description">Access your account and pick up where you left off.</p>
        @if(session('error'))<div class="np-alert" role="alert">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="np-alert" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @if(session('success'))<div class="np-alert" role="status">{{ session('success') }}</div>@endif
        <form method="POST" action="{{ route('login.submit') }}" id="loginForm" class="np-auth-form" x-data="{ showPassword: false }">
            @csrf
            <div><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@company.com" required autocomplete="username" autofocus></div>
            <div><div class="np-auth-password-label"><label for="password">Password</label><button type="button" @click="showPassword = !showPassword" :aria-pressed="showPassword" x-text="showPassword ? 'Hide password' : 'Show password'">Show password</button></div><input id="password" :type="showPassword ? 'text' : 'password'" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password"></div>
            <div class="np-auth-meta"><label><input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Keep me signed in</label><a href="{{ route('tracking.page') }}">Track without signing in ↗</a></div>
            <button type="submit" class="np-button" id="loginBtn">Sign in <span aria-hidden="true">→</span></button>
        </form>
        <div class="np-auth-footer">New to NETPACK? <a href="{{ route('register') }}">Create an account ↗</a></div>
        @if(app()->environment(['local', 'testing']))
        <details class="np-auth-demo"><summary>Development preview · demo accounts</summary><div>
            @foreach([
                ['superadmin@netpack.test', 'Netpack!Admin#2026', 'Super Admin'],
                ['international.admin@netpack.test', 'Netpack!International#2026', 'Global Admin'],
                ['domestic.admin@netpack.test', 'Netpack!Domestic#2026', 'Domestic Admin'],
                ['seller@netpack.test', 'Netpack!Seller#2026', 'Seller Merchant'],
                ['rider@netpack.test', 'Netpack!Rider#2026', 'Delivery Rider'],
                ['customer@netpack.test', 'Netpack!Customer#2026', 'Client'],
            ] as $demo)
            <button type="button" onclick="fillRole(@js($demo[0]), @js($demo[1]))">{{ $demo[2] }}</button>
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
</body>
</html>
