@props(['tags', 'size' => 'sm', 'limit' => null])

@if($tags && $tags->count() > 0)
    <div class="tag-list">
        @foreach($limit ? $tags->take($limit) : $tags as $tag)
            <a href="{{ route('home', ['tag' => $tag->slug]) }}" 
               class="badge bg-secondary text-decoration-none me-1 mb-1 badge-{{ $size }}"
               title="View posts tagged with {{ $tag->name }}">
                #{{ $tag->name }}
            </a>
        @endforeach
        
        @if($limit && $tags->count() > $limit)
            <span class="badge bg-light text-muted badge-{{ $size }}">
                +{{ $tags->count() - $limit }} more
            </span>
        @endif
    </div>
@endif