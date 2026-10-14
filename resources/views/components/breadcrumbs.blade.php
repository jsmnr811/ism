@props([
    'items' => [],
])

@php
$items = collect($items);
@endphp

@if ($items->isNotEmpty())
    <nav aria-label="breadcrumb">
        <flux:breadcrumbs>
            @foreach ($items as $item)
                @if (isset($item['href']))
                    <flux:breadcrumbs.item :href="$item['href']">
                        {{ $item['label'] }}
                    </flux:breadcrumbs.item>
                @else
                    <flux:breadcrumbs.item>
                        {{ $item['label'] }}
                    </flux:breadcrumbs.item>
                @endif
            @endforeach
        </flux:breadcrumbs>
    </nav>
@endif
