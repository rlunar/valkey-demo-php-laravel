@props(['popularPosts' => collect()])

<div class="popular-posts-sidebar bg-white p-4 rounded shadow-sm mb-4">
    <h5 class="mb-3 d-flex align-items-center">
        <i class="bi bi-fire text-danger me-2"></i>
        <span>Most Viewed</span>
    </h5>
    
    @if($popularPosts->count() > 0)
        <div class="list-group list-group-flush">
            @foreach($popularPosts as $index => $post)
                <div class="list-group-item border-0 px-0 py-2">
                    <div class="d-flex align-items-start">
                        <!-- Ranking Number -->
                        <div class="flex-shrink-0 me-3">
                            <span class="badge bg-{{ $index < 3 ? 'primary' : 'secondary' }} rounded-circle d-flex align-items-center justify-content-center" 
                                  style="width: 24px; height: 24px; font-size: 0.75rem;">
                                {{ $index + 1 }}
                            </span>
                        </div>
                        
                        <!-- Post Info -->
                        <div class="flex-grow-1 min-w-0">
                            <h6 class="mb-1">
                                <a href="{{ route('post.show', $post->slug) }}" 
                                   class="text-decoration-none text-dark fw-semibold lh-sm"
                                   title="{{ $post->title }}">
                                    {{ Str::limit($post->title, 60) }}
                                </a>
                            </h6>
                            
                            <!-- Post Meta -->
                            <div class="d-flex align-items-center text-muted small mb-1">
                                <i class="bi bi-eye me-1"></i>
                                <span class="me-3">{{ number_format($post->view_count) }} views</span>
                                
                                @if($post->category)
                                    <i class="bi bi-folder me-1"></i>
                                    <span>{{ $post->category->name }}</span>
                                @endif
                            </div>
                            
                            <!-- Author and Date -->
                            <div class="text-muted small">
                                <i class="bi bi-person me-1"></i>
                                <span class="me-2">{{ $post->user->name }}</span>
                                <i class="bi bi-calendar3 me-1"></i>
                                <span>{{ $post->published_at->format('M j, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- View All Popular Posts Link -->
        <div class="mt-3 text-center">
            <a href="{{ route('popular') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-right me-1"></i>
                View All Popular Posts
            </a>
        </div>
    @else
        <div class="text-center py-3">
            <i class="bi bi-graph-up text-muted" style="font-size: 2rem;"></i>
            <p class="text-muted mb-0 mt-2">No popular posts yet</p>
            <small class="text-muted">Check back soon for trending content!</small>
        </div>
    @endif
</div>