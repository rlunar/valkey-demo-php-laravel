@props(['tags', 'size' => 'sm', 'clickable' => true, 'limit' => null])

@php
    $displayTags = $limit ? $tags->take($limit) : $tags;
    $remainingCount = $limit && $tags->count() > $limit ? $tags->count() - $limit : 0;
    
    $sizeClasses = [
        'xs' => 'badge-xs text-xs px-2 py-1',
        'sm' => 'badge-sm text-sm px-2 py-1',
        'md' => 'badge-md text-base px-3 py-1', 
        'lg' => 'badge-lg text-lg px-4 py-2'
    ];
    
    $baseClasses = 'badge bg-secondary text-white rounded-pill me-1 mb-1 text-decoration-none';
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['sm'];
@endphp

@if($displayTags->count() > 0)
    <div class="tag-list d-flex flex-wrap align-items-center">
        @foreach($displayTags as $tag)
            @if($clickable)
                <a href="{{ route('tags.show', $tag->slug) }}" 
                   class="{{ $baseClasses }} {{ $sizeClass }}"
                   title="View posts tagged with {{ $tag->name }}">
                    <i class="fas fa-tag me-1"></i>{{ $tag->name }}
                </a>
            @else
                <span class="{{ $baseClasses }} {{ $sizeClass }}">
                    <i class="fas fa-tag me-1"></i>{{ $tag->name }}
                </span>
            @endif
        @endforeach
        
        @if($remainingCount > 0)
            <span class="badge bg-light text-dark {{ $sizeClass }}" title="{{ $remainingCount }} more tags">
                +{{ $remainingCount }}
            </span>
        @endif
    </div>
@else
    <span class="text-muted fst-italic">No tags</span>
@endif