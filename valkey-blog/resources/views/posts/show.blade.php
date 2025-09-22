@extends('layouts.app')

@section('title', $post->title . ' - Valkey Blog')
@section('description', $post->excerpt ?? Str::limit(strip_tags($post->content), 160))
@section('author', $post->user->name)

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <!-- Post Header -->
        <div class="blog-post">
            <h1 class="blog-post-title">{{ $post->title }}</h1>
            <p class="blog-post-meta">
                {{ $post->published_at->format('F j, Y') }} by 
                <strong>{{ $post->user->name }}</strong>
            </p>

            <!-- Post Content -->
            <div class="blog-post-content">
                {!! nl2br(e($post->content)) !!}
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
    }
    
    .blog-post-content h1,
    .blog-post-content h2,
    .blog-post-content h3,
    .blog-post-content h4,
    .blog-post-content h5,
    .blog-post-content h6 {
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-weight: 600;
    }
    
    .blog-post-content blockquote {
        border-left: 4px solid #007bff;
        padding-left: 1rem;
        margin: 1.5rem 0;
        font-style: italic;
        color: #6c757d;
    }
    
    .blog-post-content code {
        background-color: #f8f9fa;
        padding: 0.2rem 0.4rem;
        border-radius: 0.25rem;
        font-size: 0.9em;
    }
    
    .blog-post-content pre {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 0.5rem;
        overflow-x: auto;
    }
</style>
@endpush