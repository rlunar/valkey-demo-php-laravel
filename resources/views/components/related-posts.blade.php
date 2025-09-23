@props(['relatedPosts', 'currentPost' => null, 'title' => 'Related Posts', 'limit' => 5])

@php
    $displayPosts = $relatedPosts->take($limit);
@endphp

@if($displayPosts->count() > 0)
    <div class="related-posts bg-light p-4 rounded shadow-sm">
        <h5 class="mb-3">
            <i class="fas fa-link me-2"></i>{{ $title }}
        </h5>
        
        <div class="row">
            @foreach($displayPosts as $post)
                <div class="col-md-6 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-2">
                                <a href="{{ route('post.show', $post->slug) }}" 
                                   class="text-decoration-none text-dark stretched-link">
                                    {{ Str::limit($post->title, 60) }}
                                </a>
                            </h6>
                            
                            @if($post->excerpt)
                                <p class="card-text text-muted small mb-2">
                                    {{ Str::limit($post->excerpt, 80) }}
                                </p>
                            @endif
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="post-meta small text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $post->created_at->format('M j, Y') }}
                                </div>
                                
                                @if($post->category)
                                    <x-category-badge :category="$post->category" size="xs" :clickable="false" />
                                @endif
                            </div>
                            
                            @if($post->tags->count() > 0)
                                <div class="mt-2">
                                    <x-tag-list :tags="$post->tags" size="xs" :limit="3" :clickable="false" />
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        @if($relatedPosts->count() > $limit)
            <div class="text-center mt-3">
                <small class="text-muted">
                    Showing {{ $limit }} of {{ $relatedPosts->count() }} related posts
                </small>
            </div>
        @endif
    </div>
@else
    <div class="related-posts bg-light p-4 rounded shadow-sm text-center">
        <h5 class="mb-3">
            <i class="fas fa-link me-2"></i>{{ $title }}
        </h5>
        <p class="text-muted mb-0">
            <i class="fas fa-info-circle me-2"></i>
            No related posts found at this time.
        </p>
    </div>
@endif