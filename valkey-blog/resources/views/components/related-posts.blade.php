@props(['relatedPosts', 'title' => 'Related Posts'])

@if($relatedPosts && $relatedPosts->count() > 0)
    <div class="related-posts">
        <h4 class="mb-3">{{ $title }}</h4>
        <div class="row">
            @foreach($relatedPosts as $post)
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title">
                                <a href="{{ route('post.show', $post->slug) }}" class="text-decoration-none">
                                    {{ $post->title }}
                                </a>
                            </h6>
                            <p class="card-text small text-muted">
                                {{ $post->getExcerptForContext('card') }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    {{ $post->published_at->format('M d, Y') }}
                                </small>
                                @if($post->category)
                                    <x-category-badge :category="$post->category" size="sm" />
                                @endif
                            </div>
                            @if($post->tags && $post->tags->count() > 0)
                                <div class="mt-2">
                                    <x-tag-list :tags="$post->tags" size="sm" :limit="3" />
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif