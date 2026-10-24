@props(['href' => null, 'inverse' => false])
<a href="{{ $href ?? route('home') }}" {{ $attributes->class(['np-wordmark']) }} aria-label="COURIER with NETPACK home">
    <span class="np-wordmark-symbol" aria-hidden="true">N›</span>
    <span>NETPACK<small>Courier &amp; logistics</small></span>
</a>
