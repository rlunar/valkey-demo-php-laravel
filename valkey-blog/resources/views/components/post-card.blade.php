@props(['post', 'featured' => false])

@if($featured)
    <!-- Featured Post Card -->
    <div class="p-4 p-md-5 mb-4 text-white rounded bg-dark">
        <div class="col-md-6 px-0">
            <!-- Category Badge -->
            @if($post->category)
                <div class="mb-2">
                    <x-category-badge :category="$post->category" size="md" />
                </div>
            @endif
            
            <h1 class="display-4 fst-italic">{{ $post->title }}</h1>
            <p class="lead my-3">{{ $post->getExcerptForContext('featured') }}</p>
            <p class="lead mb-0">
                <a href="{{ route('post.show', $post->slug) }}" class="text-white fw-bold">Continue reading...</a>
            </p>
            
            <!-- Tags -->
            @if($post->tags && $post->tags->count() > 0)
                <div class="mt-3">
                    <x-tag-list :tags="$post->tags" size="sm" :limit="5" />
                </div>
            @endif
            
            <div class="mt-3">
                <small class="text-muted">
                    By <strong>{{ $post->user->name }}</strong> on {{ $post->published_at->format('M d, Y') }}
                    <span class="ms-3">
                        <i class="fas fa-eye me-1"></i>{{ number_format($post->view_count) }}
                    </span>
                </small>
            </div>
        </div>
    </div>
@else
    <!-- Regular Post Card -->
    <div class="col-md-6">
        <div class="row g-0 border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">
            <div class="col p-4 d-flex flex-column position-static">
                <!-- Category Badge -->
                @if($post->category)
                    <x-category-badge :category="$post->category" size="sm" />
                @else
                    <strong class="d-inline-block mb-2 text-primary">{{ ucfirst($post->status) }}</strong>
                @endif
                
                <h3 class="mb-0">{{ $post->title }}</h3>
                <div class="mb-1 text-muted">{{ $post->published_at->format('M d') }}</div>
                <p class="card-text mb-auto">{{ $post->getExcerptForContext('card') }}</p>
                
                <!-- Tags -->
                @if($post->tags && $post->tags->count() > 0)
                    <div class="mb-2">
                        <x-tag-list :tags="$post->tags" size="sm" :limit="3" />
                    </div>
                @endif
                
                <a href="{{ route('post.show', $post->slug) }}" class="stretched-link">Continue reading</a>
            </div>
            <div class="col-auto d-none d-lg-block">
                <svg class="bd-placeholder-img" width="200" height="250" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Thumbnail" preserveAspectRatio="xMidYMid slice" focusable="false">
                    <title>Placeholder</title>
                    <rect width="100%" height="100%" fill="#55595c"></rect>
                    <text x="50%" y="50%" fill="#eceeef" dy=".3em">Thumbnail</text>
                </svg>
            </div>
        </div>
        <div class="px-3 pb-2">
            <small class="text-muted">
                By <strong>{{ $post->user->name }}</strong>
                @if($post->user->bio)
                    <span class="text-muted"> - {{ Str::limit($post->user->bio, 50) }}</span>
                @endif
                <span class="ms-3">
                    <i class="fas fa-eye me-1"></i>{{ number_format($post->view_count) }}
                </span>
            </small>
        </div>
    </div>
@endif