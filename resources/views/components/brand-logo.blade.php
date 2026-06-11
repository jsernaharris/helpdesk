@props([
    'textClass' => 'text-xl font-bold text-gray-900',
    'imgClass' => 'h-8 w-auto',
])

@php
    $name = config('app.name');
    $logo = config('app.logo');
    // Use absolute URLs and root-relative paths verbatim; resolve everything
    // else (e.g. "images/logo.svg" in public/) through asset().
    $src = $logo
        ? (\Illuminate\Support\Str::startsWith($logo, ['http://', 'https://', '/']) ? $logo : asset($logo))
        : null;
@endphp

@if($src)
    <img src="{{ $src }}" alt="{{ $name }}" {{ $attributes->merge(['class' => $imgClass]) }}>
@else
    <span {{ $attributes->merge(['class' => $textClass]) }}>{{ $name }}</span>
@endif
