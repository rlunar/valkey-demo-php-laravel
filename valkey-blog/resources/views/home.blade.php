@extends('layouts.app')

@section('title', 'Valkey Developer Blog')
@section('description', 'Latest insights, tutorials, and updates from the Valkey development team')

@section('content')
<!-- Blog Header -->
<div class="p-4 p-md-5 mb-4 text-center bg-white rounded shadow-sm">
    <div class="col-md-8 mx-auto">
        <h1 class="display-4 blog-title text-dark">Valkey Developer Blog</h1>
        <p class="fs-5 text-muted">Insights, tutorials, and updates from the Valkey development community</p>
        <p class="lead mb-0">
            <small class="text-muted">
                Discover the latest in high-performance data structures, Redis compatibility, and open-source innovation
            </small>
        </p>
    </div>
</div>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-9">

        @if($posts->count() > 0)
            <!-- Featured Post (First Post) -->
            @if($posts->isNotEmpty())
                <x-post-card :post="$posts->first()" :featured="true" />
            @endif

            <!-- Regular Posts Grid -->
            @if($posts->count() > 1)
                <div class="row mb-4">
                    @foreach($posts->skip(1) as $post)
                        <x-post-card :post="$post" />
                    @endforeach
                </div>
            @endif

            <!-- Pagination -->
            @if($posts->hasPages())
                <nav aria-label="Blog pagination" class="d-flex justify-content-center mt-5">
                    {{ $posts->links('pagination::bootstrap-4') }}
                </nav>
            @endif
        @else
            <!-- No Posts Message -->
            <div class="text-center py-5">
                <div class="col-md-6 mx-auto">
                    <svg class="bd-placeholder-img mb-4" width="120" height="120" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="No posts" preserveAspectRatio="xMidYMid slice" focusable="false">
                        <title>No posts</title>
                        <rect width="100%" height="100%" fill="#e9ecef" rx="15"></rect>
                        <text x="50%" y="50%" fill="#6c757d" dy=".3em" font-size="16">📝</text>
                    </svg>
                    <h3 class="text-muted mb-3">
                        @if($currentCategory || $currentTag || $currentTags->count() > 0)
                            No posts found matching your filters
                        @else
                            No posts available yet
                        @endif
                    </h3>
                    <p class="text-muted mb-4">
                        @if($currentCategory || $currentTag || $currentTags->count() > 0)
                            Try adjusting your filters or browse all posts.
                        @else
                            We're working on bringing you amazing content about Valkey. Check back soon for new insights and tutorials!
                        @endif
                    </p>
                    @auth
                        <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">Create First Post</a>
                    @endauth
                </div>
            </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-3">
        <x-filter-sidebar 
            :categories="$categories" 
            :popularTags="$popularTags" 
            :popularPosts="$popularPosts"
            :currentCategory="$currentCategory" 
            :currentTag="$currentTag" 
            :currentTags="$currentTags" 
        />
    </div>
</div>

<!-- Newsletter Signup Section -->
<div class="p-4 p-md-5 mb-4 bg-primary text-white rounded">
    <div class="col-md-8 mx-auto text-center">
        <h4 class="mb-3">Stay Updated</h4>
        <p class="mb-4">Get the latest Valkey insights delivered to your inbox. No spam, just quality content.</p>
        <form class="row g-3 justify-content-center">
            <div class="col-md-6">
                <input type="email" class="form-control" placeholder="Enter your email" required>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-light">Subscribe</button>
            </div>
        </form>
        <small class="d-block mt-2 opacity-75">We respect your privacy. Unsubscribe at any time.</small>
    </div>
</div>
@endsection