@props([
    'variant' => 'primary', // primary, success, danger, subtle
    'size' => 'md', // sm, md, lg
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center justify-center rounded-md font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-cherry-600/30 disabled:opacity-60 disabled:cursor-not-allowed';
    $sizes = [
        'sm' => 'text-xs px-3 py-1.5',
        'md' => 'text-sm px-4 py-2',
        'lg' => 'text-sm px-5 py-2.5',
    ];
    $variants = [
        'primary' => 'bg-cherry-600 hover:bg-cherry-700 text-white',
        'success' => 'bg-emerald-600 hover:bg-emerald-700 text-white',
        'danger' => 'bg-rose-600 hover:bg-rose-700 text-white',
        'subtle' => 'bg-white/5 hover:bg-white/10 border border-white/10 text-white',
    ];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "$base {$sizes[$size]} {$variants[$variant]}" ]) }}>
    {{ $slot }}
    
</button>


