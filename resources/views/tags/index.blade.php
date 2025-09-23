@extends('layouts.app')

@section('title', 'Tags - Valkey Blog')
@section('description', 'Explore blog posts by tags to discover content across different topics')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bold text-dark">Tags</h1>
                <p class="lead text-muted">Discover content by exploring our tag cloud</p>
            </div>

            @if($tags->count() > 0)
                <!-- Tag Cloud -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="bi bi-tags"></i> Tag Cloud
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="tag-cloud">
                            @foreach($tags as $tag)
                                @php
                                    // Calculate tag size based on post count
                                    $maxCount = $tags->max('posts_count');
                                    $minCount = $tags->min('posts_count');
                                    $range = $maxCount - $minCount;
                                    
                                    if ($range > 0) {
                                        $size = 1 + (($tag->posts_count - $minCount) / $range) * 2;
                                    } else {
                                        $size = 1.5;
                                    }
                                    
                                    // Determine badge color based on popularity
                                    if ($tag->posts_count >= $maxCount * 0.8) {
                                        $badgeClass = 'bg-primary';
                                    } elseif ($tag->posts_count >= $maxCount * 0.5) {
                                        $badgeClass = 'bg-info';
                                    } elseif ($tag->posts_count >= $maxCount * 0.3) {
                                        $badgeClass = 'bg-success';
                                    } else {
                                        $badgeClass = 'bg-secondary';
                                    }
                                @endphp
                                
                                <a href="{{ route('tags.show', $tag->slug) }}" 
                                   class="tag-link text-decoration-none me-2 mb-2 d-inline-block">
                                    <span class="badge {{ $badgeClass }} tag-badge" 
                                          style="font-size: {{ $size }}rem;"
                                          data-bs-toggle="tooltip" 
                                          title="{{ $tag->posts_count }} {{ Str::plural('post', $tag->posts_count) }}">
                                        {{ $tag->name }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Tag List View -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul"></i> All Tags
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($tags as $tag)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="d-flex align-items-center justify-content-between p-3 border rounded hover-card">
                                        <div>
                                            <h6 class="mb-1">
                                                <a href="{{ route('tags.show', $tag->slug) }}" 
                                                   class="text-decoration-none text-dark">
                                                    <i class="bi bi-tag"></i> {{ $tag->name }}
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                {{ $tag->posts_count }} {{ Str::plural('post', $tag->posts_count) }}
                                            </small>
                                        </div>
                                        <div>
                                            <span class="badge bg-light text-dark">
                                                {{ $tag->posts_count }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                @if($tags->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $tags->links() }}
                    </div>
                @endif
            @else
                <!-- No Tags Message -->
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-tags" style="font-size: 4rem; color: #6c757d;"></i>
                    </div>
                    <h4 class="text-muted mb-3">No tags available</h4>
                    <p class="text-muted mb-4">Tags help categorize content by topics. Check back soon as we add more content!</p>
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
    .tag-cloud {
        line-height: 2.5;
        text-align: center;
    }
    
    .tag-badge {
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }
    
    .tag-link:hover .tag-badge {
        transform: scale(1.1);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }
    
    .hover-card {
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }
    
    .hover-card:hover {
        background-color: #f8f9fa;
        border-color: #007bff !important;
        transform: translateY(-1px);
    }
</style>
@endpush

@push('scripts')
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush