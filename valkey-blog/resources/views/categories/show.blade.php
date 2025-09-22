@extends('layouts.app')

@section('title', $category->name)
@section('description', $category->description ?: 'Posts in ' . $category->name . ' category')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="blog-title mb-2">{{ $category->name }}</h1>
            @if($category->description)
                <p class="text-muted mb-4">{{ $category->description }}</p>
            @endif
        </div>
    </div>

    @if($posts->count() > 0)
        <div class="row">
            @foreach($posts as $post)
                <x-post-card :post="$post" />
            @endforeach
        </div>

        @if($posts->hasPages())
            <nav aria-label="Category posts pagination" class="d-flex justify-content-center mt-5">
                {{ $posts->links('pagination::bootstrap-4') }}
            </nav>
        @endif
    @else
        <div class="text-center py-5">
            <div class="col-md-6 mx-auto">
                <h3 class="text-muted mb-3">No posts in this category yet</h3>
                <p class="text-muted">Check back later for new content in {{ $category->name }}.</p>
                <a href="{{ route('categories.index') }}" class="btn btn-outline-primary">Browse All Categories</a>
            </div>
        </div>
    @endif
</div>
@endsection