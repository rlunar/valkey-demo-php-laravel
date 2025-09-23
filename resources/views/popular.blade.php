@extends('layouts.app')

@section('title', 'Popular Posts - Valkey Developer Blog')
@section('description', 'Most viewed posts on the Valkey Developer Blog')

@section('content')
<!-- Popular Posts Header -->
<div class="p-4 p-md-5 mb-4 text-center bg-white rounded shadow-sm">
    <div class="col-md-8 mx-auto">
        <h1 class="display-4 blog-title text-dark">Popular Posts</h1>
        <p class="fs-5 text-muted">Most viewed articles from the Valkey development community</p>
        <p class="lead mb-0">
            <small class="text-muted">
                Discover what the community is reading most
            </small>
        </p>
    </div>
</div>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-9">

        @if($posts->count() > 0)
            <!-- Popular Posts Grid -->
            <div class="row">
                @foreach($posts as $post)
                    <div class="col-md-6 mb-4">
                        <x-post-card :post="$post" />
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $posts->links() }}
            </div>
        @else
            <!-- No Posts Message -->
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-newspaper fa-4x text-muted"></i>
                </div>
                <h3 class="text-muted">No Popular Posts Yet</h3>
                <p class="text-muted">Check back later for popular content!</p>
                <a href="{{ route('home') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Home
                </a>
            </div>
        @endif

    </div>

    <!-- Sidebar -->
    <div class="col-lg-3">
        <x-filter-sidebar 
            :categories="$categories" 
            :popularTags="$popularTags" 
            :popularPosts="$popularPosts"
        />
    </div>
</div>
@endsection