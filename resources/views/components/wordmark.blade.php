@props(['href' => null, 'inverse' => false])
<a href="{{ $href ?? route('home') }}" {{ $attributes->class(['np-wordmark']) }} aria-label="COURIER with NETPACK home">
    <span class="np-wordmark-symbol" aria-hidden="true">N›</span>
    <span class="np-wordmark-name">COURIER<span class="np-wordmark-partner"><span class="np-wordmark-with">with</span> NETPACK</span></span>
</a>
