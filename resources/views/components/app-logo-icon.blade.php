@props([
    'variant' => 'auto',
])

@php
    $imageClasses = collect(preg_split('/\s+/', trim($attributes->get('class') ?? '')))
        ->filter()
        ->push('object-contain')
        ->unique()
        ->implode(' ');
    $brandName = config('app.name', 'PRDP Scale-Up');
@endphp

@if ($variant === 'dark')
    <span {{ $attributes->except('class')->merge(['class' => 'contents']) }}>
        <img src="/assets/scale-up-mark-white.png" alt="{{ $brandName }}" class="{{ $imageClasses }}" />
    </span>
@elseif ($variant === 'light')
    <span {{ $attributes->except('class')->merge(['class' => 'contents']) }}>
        <img src="/assets/scale-up-mark.png" alt="{{ $brandName }}" class="{{ $imageClasses }}" />
    </span>
@else
    <span {{ $attributes->except('class')->merge(['class' => 'contents']) }}>
        <img src="/assets/scale-up-mark.png" alt="{{ $brandName }}" class="{{ $imageClasses }} dark:hidden" />
        <img src="/assets/scale-up-mark-white.png" alt="{{ $brandName }}" class="{{ $imageClasses }} hidden dark:block" />
    </span>
@endif
