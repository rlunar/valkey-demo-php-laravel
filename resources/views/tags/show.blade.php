@extends('layouts.app')

@section('title', 'Posts tagged with "' . $tag->name . '" - Valkey Blog')
@section('description', 'Browse all blog posts tagged with "' . $tag->name . '"')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <div class="mb-3">
                    <span class="badge bg-primary fs-6 px-3 py-2">
                        <i class="bi bi-tag"></i> {{ $tag->name }}
                    </span>
                </div>
                <h1 class="display-6 fw-bold text-dark">Posts tagged with "{{ $tag->name }}"</h1>
                <p class="lead text-muted">
                    {{ $posts->total() }} {{ Str::plural('post', $posts->total()) }} found
                </p>
            </div>

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home') }}" class="text-decoration-none">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('tags.index') }}" class="text-decoration-none">Tags</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $tag->name }}</li>
                </ol>
            </nav>

            @if($posts->count() > 0)
                <!-- Posts List -->
                <div class="row">
                    @foreach($posts as $post)
                        <div class="col-12 mb-4">
                            <div class="card shadow-sm border-0 h-100 post-card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5 class="card-title mb-2">
                                                <a href="{{ route('post.show', $post->slug) }}" 
                                                   class="text-decoration-none text-dark stretched-link">
                                                    {{ $post->title }}
                                                </a>
                                            </h5>
                                            <p class="card-text text-muted mb-3">{{ $post->excerpt }}</p>
                                            
                                            <!-- Post Meta -->
                                            <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                                                <small class="text-muted">
                                                    <i class="bi bi-person"></i> {{ $post->user->name }}
                                                </small>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar"></i> {{ $post->published_at->format('M d, Y') }}
                                                </small>
                                                @if($post->category)
                                                    <span class="badge bg-primary">
                                                        <i class="bi bi-folder"></i> {{ $post->category->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <div class="d-flex flex-column h-100 justify-content-between">
                                                <div></div>
                                                <div>
                                                    <small class="text-muted d-block mb-2">
                                                        <i class="bi bi-eye"></i> Read more
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Tags -->
                                    @if($post->tags->count() > 0)
                                        <div class="mt-3 pt-3 border-top">
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($post->tags as $postTag)
                                                    <span class="badge {{ $postTag->id === $tag->id ? 'bg-primary' : 'bg-secondary' }} tag-badge">
                                                        <i class="bi bi-tag"></i> {{ $postTag->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($posts->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $posts->links() }}
                    </div>
                @endif
            @else
                <!-- No Posts Message -->
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-file-earmark-text" style="font-size: 4rem; color: #6c757d;"></i>
                    </div>
                    <h4 class="text-muted mb-3">No posts found</h4>
                    <p class="text-muted mb-4">
                        There are currently no posts tagged with "{{ $tag->name }}". 
                        Check back later or explore other tags.
                    </p>
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="{{ route('tags.index') }}" class="btn btn-primary">
                            <i class="bi bi-tags"></i> Browse All Tags
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left"></i> Back to Blog
                        </a>
                    </div>
                </div>
            @endif

            <!-- Related Tags -->
            @if($posts->count() > 0)
                @php
                    $relatedTags = collect();
                    foreach($posts as $post) {
                        foreach($post->tags as $postTag) {
                            if($postTag->id !== $tag->id) {
                                $relatedTags->push($postTag);
                            }
                        }
                    }
                    $relatedTags = $relatedTags->unique('id')->take(10);
                @endphp
                
                @if($relatedTags->count() > 0)
                    <div class="card shadow-sm border-0 mt-5">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">
                                <i class="bi bi-tags"></i> Related Tags
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($relatedTags as $relatedTag)
                                    <a href="{{ route('tags.show', $relatedTag->slug) }}" 
                                       class="text-decoration-none">
                                        <span class="badge bg-outline-secondary border tag-badge">
                                            <i class="bi bi-tag"></i> {{ $relatedTag->name }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <!-- Back Navigation -->
            <div class="text-center mt-5 pt-4 border-top">
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('tags.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-tags"></i> All Tags
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left"></i> Back to Blog
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .post-card {
        transition: all 0.2s ease-in-out;
    }
    
    .post-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    .tag-badge {
        transition: all 0.2s ease-in-out;
    }
    
    .tag-badge:hover {
        transform: scale(1.05);
    }
</style>
@endpush