@props(['categories', 'popularTags', 'currentCategory', 'currentTag', 'currentTags'])

<div class="filter-sidebar">
    <!-- Active Filters -->
    @if($currentCategory || $currentTag || ($currentTags && $currentTags->count() > 0))
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Active Filters</h6>
            </div>
            <div class="card-body">
                @if($currentCategory)
                    <div class="mb-2">
                        <span class="text-muted small">Category:</span>
                        <span class="badge bg-primary">{{ $currentCategory->name }}</span>
                        <a href="{{ route('home') }}" 
                           class="text-muted ms-1" title="Remove filter">×</a>
                    </div>
                @endif
                
                @if($currentTag)
                    <div class="mb-2">
                        <span class="text-muted small">Tag:</span>
                        <span class="badge bg-secondary">#{{ $currentTag->name }}</span>
                        <a href="{{ route('home') }}" 
                           class="text-muted ms-1" title="Remove filter">×</a>
                    </div>
                @endif
                
                @if($currentTags && $currentTags->count() > 0)
                    <div class="mb-2">
                        <span class="text-muted small">Tags:</span>
                        @foreach($currentTags as $tag)
                            <span class="badge bg-secondary me-1">#{{ $tag->name }}</span>
                        @endforeach
                        <a href="{{ route('home') }}" 
                           class="text-muted ms-1" title="Remove filters">×</a>
                    </div>
                @endif
                
                <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary">
                    Clear All Filters
                </a>
            </div>
        </div>
    @endif

    <!-- Categories Filter -->
    @if($categories && $categories->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Categories</h6>
            </div>
            <div class="card-body">
                @foreach($categories as $category)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <a href="{{ route('home', ['category' => $category->slug]) }}" 
                           class="text-decoration-none {{ $currentCategory && $currentCategory->id === $category->id ? 'fw-bold' : '' }}">
                            {{ $category->name }}
                        </a>
                        <span class="badge bg-light text-muted">{{ $category->posts_count ?? 0 }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Popular Tags -->
    @if($popularTags && $popularTags->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Popular Tags</h6>
            </div>
            <div class="card-body">
                <div class="tag-cloud">
                    @foreach($popularTags as $tag)
                        <a href="{{ route('home', ['tag' => $tag->slug]) }}" 
                           class="badge bg-secondary text-decoration-none me-1 mb-1 {{ $currentTag && $currentTag->id === $tag->id ? 'bg-primary' : '' }}"
                           title="{{ $tag->posts_count ?? 0 }} posts">
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>