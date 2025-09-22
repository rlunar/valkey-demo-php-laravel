@extends('layouts.app')

@section('title', 'Categories')
@section('description', 'Browse blog posts by category')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="blog-title mb-4">Categories</h1>
            <p class="text-muted mb-4">Browse posts by category</p>
        </div>
    </div>

    <div class="row">
        @foreach($categories as $category)
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="{{ route('categories.show', $category->slug) }}" class="text-decoration-none">
                                {{ $category->name }}
                            </a>
                        </h5>
                        @if($category->description)
                            <p class="card-text text-muted">{{ $category->description }}</p>
                        @endif
                        <small class="text-muted">
                            {{ $category->posts_count }} {{ Str::plural('post', $category->posts_count) }}
                        </small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection