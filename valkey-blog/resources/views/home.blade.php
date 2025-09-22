@extends('layouts.app')

@section('title', 'Valkey Developer Blog')
@section('description', 'Latest insights, tutorials, and updates from the Valkey development team')

@section('content')
<div class="row mb-2">
    <div class="col-md-12">
        <!-- Blog Header -->
        <div class="blog-header py-3 mb-4">
            <div class="row flex-nowrap justify-content-between align-items-center">
                <div class="col-12 text-center">
                    <h1 class="blog-title">Valkey Developer Blog</h1>
                    <p class="blog-description">Insights, tutorials, and updates from the Valkey team</p>
                </div>
            </div>
        </div>

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
                <div class="d-flex justify-content-center">
                    {{ $posts->links() }}
                </div>
            @endif
        @else
            <!-- No Posts Message -->
            <div class="text-center py-5">
                <h3 class="text-muted">No posts available</h3>
                <p class="text-muted">Check back soon for new content!</p>
            </div>
        @endif
    </div>
</div>
@endsection