@props(['category', 'size' => 'sm', 'clickable' => true])

@php
    $sizeClasses = [
        'xs' => 'badge-xs text-xs px-2 py-1',
        'sm' => 'badge-sm text-sm px-2 py-1', 
        'md' => 'badge-md text-base px-3 py-1',
        'lg' => 'badge-lg text-lg px-4 py-2'
    ];
    
    $baseClasses = 'badge rounded-pill d-inline-flex align-items-center text-decoration-none';
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['sm'];
    
    // Use category color if available, otherwise default to primary
    $colorStyle = $category->color ? "background-color: {$category->color}; color: white;" : '';
    $colorClass = $category->color ? '' : 'bg-primary text-white';
@endphp

@if($clickable)
    <a href="{{ route('categories.show', $category->slug) }}" 
       class="{{ $baseClasses }} {{ $sizeClass }} {{ $colorClass }}"
       @if($category->color) style="{{ $colorStyle }}" @endif
       title="View posts in {{ $category->name }} category">
        {{ $category->name }}
    </a>
@else
    <span class="{{ $baseClasses }} {{ $sizeClass }} {{ $colorClass }}"
          @if($category->color) style="{{ $colorStyle }}" @endif>
        {{ $category->name }}
    </span>
@endif