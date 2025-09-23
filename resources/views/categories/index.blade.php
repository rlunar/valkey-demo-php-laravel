@extends('layouts.app')

@section('title', 'Categories - Valkey Blog')
@section('description', 'Browse blog posts by category to find content that interests you most')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bold text-dark">Categories</h1>
                <p class="lead text-muted">Explore our content organized by topic</p>
            </div>

            @if($categories->count() > 0)
                <div class="row">
                    @foreach($categories as $category)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow-sm border-0 category-card">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-center mb-3">
                                        @if($category->color)
                                            <div class="category-color-indicator me-3" 
                                                 style="background-color: {{ $category->color }}; width: 4px; height: 40px; border-radius: 2px;"></div>
                                        @endif
                                        <div>
                                            <h5 class="card-title mb-1">
                                                <a href="{{ route('categories.show', $category->slug) }}" 
                                                   class="text-decoration-none text-dark stretched-link">
                                                    {{ $category->name }}
                                                </a>
                                            </h5>
                                            <small class="text-muted">
                                                {{ $category->posts_count }} {{ Str::plural('post', $category->posts_count) }}
                                            </small>
                                        </div>
                                    </div>
                                    
                                    @if($category->description)
                                        <p class="card-text text-muted flex-grow-1">
                                            {{ $category->description }}
                                        </p>
                                    @endif
                                    
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-light text-dark border">
                                                {{ $category->posts_count }} posts
                                            </span>
                                            <small class="text-muted">
                                                <i class="bi bi-arrow-right"></i>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($categories->hasPages())
                    <div class="d-flex justify-content-center mt-5">
                        {{ $categories->links() }}
                    </div>
                @endif
            @else
                <!-- No Categories Message -->
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-folder2-open" style="font-size: 4rem; color: #6c757d;"></i>
                    </div>
                    <h4 class="text-muted mb-3">No categories available</h4>
                    <p class="text-muted mb-4">Categories help organize content by topic. Check back soon as we add more content!</p>
                    <a href="{{ route('home') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-left"></i> Back to Blog
                    </a>
                </div>
            @endif

            <!-- Back to Blog -->
            <div class="text-center mt-5 pt-4 border-top">
                <a href="{{ route('home') }}" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Back to Blog
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .category-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    
    .category-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    .category-color-indicator {
        flex-shrink: 0;
    }
</style>
@endpush