@props([
    'variant' => 'dark', // 'dark' (navy text), 'white' (white text)
    'size' => 'md',      // 'sm', 'md', 'lg', 'xl'
    'withTagline' => true,
    'href' => url('/'),
])

@php
    $textSizes = [
        'sm' => 'text-base sm:text-lg',
        'md' => 'text-lg sm:text-xl',
        'lg' => 'text-xl sm:text-2xl',
        'xl' => 'text-2xl sm:text-3xl',
    ];
    $withSizes = [
        'sm' => 'text-[11px]',
        'md' => 'text-xs',
        'lg' => 'text-sm',
        'xl' => 'text-base',
    ];
    $taglineSizes = [
        'sm' => 'text-[7.5px] tracking-[0.2em]',
        'md' => 'text-[8.5px] tracking-[0.22em]',
        'lg' => 'text-[9.5px] tracking-[0.24em]',
        'xl' => 'text-[10.5px] tracking-[0.26em]',
    ];
    
    $textSize = $textSizes[$size] ?? $textSizes['md'];
    $withSize = $withSizes[$size] ?? $withSizes['md'];
    $taglineSize = $taglineSizes[$size] ?? $taglineSizes['md'];
    $isWhite = $variant === 'white';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex flex-col group transition focus:outline-none select-none']) }}>
@else
    <div {{ $attributes->merge(['class' => 'inline-flex flex-col select-none']) }}>
@endif

    <div class="flex items-baseline gap-1.5 font-heading leading-none">
        <span class="font-black tracking-wider {{ $textSize }} {{ $isWhite ? 'text-white' : 'text-slate-900' }}">
            COURIER
        </span>
        <span class="font-semibold italic font-serif {{ $withSize }} {{ $isWhite ? 'text-teal-300' : 'text-slate-500' }}">
            with
        </span>
        <span class="font-black tracking-wider text-teal-500 group-hover:text-teal-400 transition {{ $textSize }}">
            NETPACK
        </span>
    </div>

    @if($withTagline)
        <span class="font-extrabold uppercase mt-1 {{ $taglineSize }} {{ $isWhite ? 'text-slate-400' : 'text-slate-500' }}">
            Global &middot; Domestic &middot; E-Commerce
        </span>
    @endif

@if($href)
    </a>
@else
    </div>
@endif
