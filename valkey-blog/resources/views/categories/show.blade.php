@extends('layouts.app')

@section('title', $category->name . ' - Categories - Valkey Blog')
@section('description', $category->description ?: 'Browse all posts in the ' . $category->name . ' category')

@section('content')
<div class="container">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Category Header -->
            <div class="mb-5">
                <div class="d-flex align-items-center mb-3">
                    @if($category->color)
                        <div class="category-color-indicator me-3" 
                             style="background-color: {{ $category->color }}; width: 6px; height: 60px; border-radius: 3px;"></div>
                    @endif
                    <div>
                        <h1 class="display-6 fw-bold mb-2">{{ $category->name }}</h1>
                        <p class="text-muted mb-0">
                            {{ $posts->total() }} {{ Str::plural('post', $posts->total()) }} in this category
                        </p>
                    </div>
                </div>
                
                @if($category->description)
                    <div class="alert alert-light border-0 bg-light">
                        <p class="mb-0 text-muted">{{ $category->description }}</p>
                    </div>
                @endif
            </div>

            @if($posts->count() > 0)
                <!-- Posts List -->
                <div class="posts-list">
                    @foreach($posts as $post)
                        <article class="card mb-4 shadow-sm border-0">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h3 class="card-title h4 mb-3">
                                            <a href="{{ route('post.show', $post->slug) }}" 
                                               class="text-decoration-none text-dark">
                                                {{ $post->title }}
                                            </a>
                                        </h3>
                                        
                                        <p class="card-text text-muted mb-3">
                                            {{ $post->getExcerptForContext('card') }}
                                        </p>
                                        
                                        <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                                            <small class="text-muted">
                                                <i class="bi bi-person"></i>
                                                By <strong>{{ $post->user->name }}</strong>
                                            </small>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar3"></i>
                                                {{ $post->published_at->format('M j, Y') }}
                                            </small>
                                            @if($post->status === 'published')
                                                <span class="badge bg-success">Published</span>
                                            @else
                                                <span class="badge bg-secondary">Draft</span>
                                            @endif
                                        </div>
                                        
                                        <!-- Tags -->
                                        @if($post->tags->count() > 0)
                                            <div class="mb-3">
                                                <small class="text-muted me-2">Tags:</small>
                                                @foreach($post->tags as $tag)
                                                    <a href="{{ route('tags.show', $tag->slug) }}" 
                                                       class="badge bg-light text-dark text-decoration-none me-1 border">
                                                        {{ $tag->name }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                        
                                        <a href="{{ route('post.show', $post->slug) }}" 
                                           class="btn btn-outline-primary btn-sm">
                                            Read More <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                    
                                    <!-- Post Thumbnail Placeholder -->
                                    <div class="col-md-4 d-none d-md-block">
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="height: 150px;">
                                            <i class="bi bi-file-text text-muted" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($posts->hasPages())
                    <nav aria-label="Posts pagination" class="d-flex justify-content-center mt-5">
                        {{ $posts->appends(request()->query())->links() }}
                    </nav>
                @endif
            @else
                <!-- No Posts Message -->
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-file-text" style="font-size: 4rem; color: #6c757d;"></i>
                    </div>
                    <h4 class="text-muted mb-3">No posts in this category yet</h4>
                    <p class="text-muted mb-4">
                        We haven't published any posts in the "{{ $category->name }}" category yet. 
                        Check back soon or explore other categories!
                    </p>
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-folder2-open"></i> Browse Categories
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-primary">
                            <i class="bi bi-house"></i> Back to Blog
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="position-sticky" style="top: 2rem;">
                <!-- Category Info Card -->
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Category Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            @if($category->color)
                                <div class="category-color-indicator me-3" 
                                     style="background-color: {{ $category->color }}; width: 4px; height: 30px; border-radius: 2px;"></div>
                            @endif
                            <div>
                                <h6 class="mb-1">{{ $category->name }}</h6>
                                <small class="text-muted">{{ $posts->total() }} posts</small>
                            </div>
                        </div>
                        
                        @if($category->description)
                            <p class="text-muted small mb-0">{{ $category->description }}</p>
                        @endif
                    </div>
                </div>

                <!-- Other Categories -->
                @if($otherCategories && $otherCategories->count() > 0)
                    <div class="card mb-4 shadow-sm border-0">
                        <div class="card-header bg-white border-0">
                            <h5 class="card-title mb-0">Other Categories</h5>
                        </div>
                        <div class="card-body">
                            @foreach($otherCategories as $otherCategory)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <a href="{{ route('categories.show', $otherCategory->slug) }}" 
                                       class="text-decoration-none text-dark">
                                        <div class="d-flex align-items-center">
                                            @if($otherCategory->color)
                                                <div class="me-2" 
                                                     style="background-color: {{ $otherCategory->color }}; width: 3px; height: 20px; border-radius: 1px;"></div>
                                            @endif
                                            <span>{{ $otherCategory->name }}</span>
                                        </div>
                                    </a>
                                    <small class="text-muted">{{ $otherCategory->posts_count }}</small>
                                </div>
                            @endforeach
                            
                            <div class="mt-3 pt-3 border-top">
                                <a href="{{ route('categories.index') }}" class="btn btn-sm btn-outline-primary w-100">
                                    View All Categories
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Navigation -->
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="d-grid gap-2">
                            <a href="{{ route('categories.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-folder2-open"></i> All Categories
                            </a>
                            <a href="{{ route('tags.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-tags"></i> Browse Tags
                            </a>
                            <a href="{{ route('home') }}" class="btn btn-primary">
                                <i class="bi bi-house"></i> Back to Blog
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .category-color-indicator {
        flex-shrink: 0;
    }
    
    .posts-list .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    
    .posts-list .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>
@endpush