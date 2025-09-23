@extends('layouts.app')

@section('title', $post->title . ' - Valkey Blog')
@section('description', $post->getExcerptForContext('meta'))
@section('author', $post->user->name)

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <!-- Post Header -->
        <div class="blog-post">
            <!-- Category and Tags -->
            <div class="mb-3">
                @if($post->category)
                    <x-category-badge :category="$post->category" size="md" />
                @endif
                
                @if($post->tags && $post->tags->count() > 0)
                    <div class="mt-2">
                        <x-tag-list :tags="$post->tags" size="sm" />
                    </div>
                @endif
            </div>
            
            <h1 class="blog-post-title">{{ $post->title }}</h1>
            <p class="blog-post-meta">
                {{ $post->published_at->format('F j, Y') }} by 
                <strong>{{ $post->user->name }}</strong>
                <span class="text-muted ms-3">
                    <i class="fas fa-eye me-1"></i>{{ number_format($post->view_count) }} {{ Str::plural('view', $post->view_count) }}
                </span>
            </p>

            <!-- Post Content -->
            <div class="blog-post-content">
                {!! $post->formatted_content !!}
            </div>

            <!-- Post Footer -->
            <hr class="my-4">
            
            <!-- Author Information -->
            <div class="d-flex align-items-center mb-4">
                <div class="flex-shrink-0">
                    @if($post->user->avatar)
                        <img src="{{ $post->user->avatar }}" alt="{{ $post->user->name }}" class="rounded-circle" width="64" height="64">
                    @else
                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                            <span class="text-white fw-bold fs-4">{{ strtoupper(substr($post->user->name, 0, 1)) }}</span>
                        </div>
                    @endif
                </div>
                <div class="flex-grow-1 ms-3">
                    <h5 class="mb-1">{{ $post->user->name }}</h5>
                    @if($post->user->bio)
                        <p class="text-muted mb-0">{{ $post->user->bio }}</p>
                    @endif
                </div>
            </div>

            <!-- Post Metadata -->
            <div class="row text-muted small">
                <div class="col-md-6">
                    <strong>Published:</strong> {{ $post->published_at->format('F j, Y \a\t g:i A') }}
                </div>
                <div class="col-md-6">
                    <strong>Last updated:</strong> {{ $post->updated_at->format('F j, Y \a\t g:i A') }}
                </div>
            </div>
        </div>

        <!-- Related Posts -->
        @if(isset($relatedPosts))
            <div class="mt-5 pt-4 border-top">
                <x-related-posts :relatedPosts="$relatedPosts" />
            </div>
        @endif

        <!-- Navigation -->
        <div class="d-flex justify-content-between align-items-center mt-5 pt-4 border-top">
            <a href="{{ route('home') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Back to Blog
            </a>
            
            <div class="text-muted small">
                <span>Share this post:</span>
                <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}" 
                   target="_blank" class="text-decoration-none ms-2" title="Share on Twitter">
                    Twitter
                </a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->url()) }}" 
                   target="_blank" class="text-decoration-none ms-2" title="Share on LinkedIn">
                    LinkedIn
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .blog-post-content {
        font-size: 1.1rem;
        line-height: 1.7;
        color: #333;
    }
    
    .blog-post-content p {
        margin-bottom: 1.5rem;
        text-align: justify;
    }
    
    .blog-post-content h1,
    .blog-post-content h2,
    .blog-post-content h3,
    .blog-post-content h4,
    .blog-post-content h5,
    .blog-post-content h6 {
        margin-top: 2.5rem;
        margin-bottom: 1.25rem;
        font-weight: 600;
        color: #212529;
    }
    
    .blog-post-content h1:first-child,
    .blog-post-content h2:first-child,
    .blog-post-content h3:first-child {
        margin-top: 0;
    }
    
    .blog-post-content blockquote {
        font-style: italic;
        color: #6c757d;
        margin: 2rem 0;
        font-size: 1.05rem;
    }
    
    .blog-post-content blockquote p {
        margin-bottom: 0;
    }
    
    .blog-post-content code {
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        font-size: 0.875em;
        color: #e83e8c;
        word-wrap: break-word;
    }
    
    .blog-post-content pre {
        overflow-x: auto;
        margin: 1.5rem 0;
        font-size: 0.875rem;
        line-height: 1.45;
    }
    
    .blog-post-content pre code {
        color: #212529;
        background: transparent;
        padding: 0;
        border-radius: 0;
    }
    
    .blog-post-content strong {
        font-weight: 600;
    }
    
    .blog-post-content em {
        font-style: italic;
    }
    
    .blog-post-content a {
        color: #0d6efd;
        text-decoration: underline;
    }
    
    .blog-post-content a:hover {
        color: #0a58ca;
        text-decoration: none;
    }
    
    .blog-post-content img {
        max-width: 100%;
        height: auto;
        border-radius: 0.375rem;
        margin: 1.5rem 0;
    }
    
    .blog-post-content ul,
    .blog-post-content ol {
        margin-bottom: 1.5rem;
        padding-left: 2rem;
    }
    
    .blog-post-content li {
        margin-bottom: 0.5rem;
    }
</style>
@endpush