@props([
    'size' => 'text-4xl',
    'color' => null,
    'height' => null
])

@php
    // Map tailwind text sizes to heights for the logo to preserve layout proportions
    $logoHeight = $height ?: match($size) {
        'text-xs' => 'h-5',
        'text-sm' => 'h-6',
        'text-base' => 'h-7',
        'text-lg' => 'h-8',
        'text-xl' => 'h-9',
        'text-2xl' => 'h-10',
        'text-3xl' => 'h-12',
        'text-4xl' => 'h-14',
        'text-5xl' => 'h-16',
        default => 'h-12'
    };

    // If the color parameter is 'text-white' or similar, apply filter to make the image white
    $isWhite = (is_string($color) && str_contains($color, 'white'));
@endphp

<div class="flex items-center transition-transform duration-300 hover:scale-[1.02]">
    <img src="{{ asset('images/logo.png') }}" 
         alt="{{ config('app.name', 'Tamt') }}" 
         class="{{ $logoHeight }} w-auto object-contain"
         @if($isWhite) style="filter: brightness(0) invert(1);" @endif>
</div>
