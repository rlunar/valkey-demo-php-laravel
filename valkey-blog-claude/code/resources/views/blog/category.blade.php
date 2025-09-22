@extends('layouts.blog')

@section('title', $category->name . ' - Laravel Blog')
@section('description', 'Posts in ' . $category->name . ' category')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="p-4 p-md-5 mb-4 text-white rounded" style="background-color: {{ $category->color }}">
            <div class="col-md-6 px-0">
                <h1 class="display-4">{{ $category->name }}</h1>
                <p class="lead">{{ $posts->total() }} {{ Str::plural('post', $posts->total()) }} in this category</p>
            </div>
        </div>

        @forelse($posts as $post)
        <article class="blog-post">
            <h2 class="blog-post-title">
                <a href="{{ route('blog.show', $post) }}" class="text-decoration-none">{{ $post->title }}</a>
            </h2>
            <p class="blog-post-meta">{{ $post->published_at->format('F d, Y') }}</p>
            <p>{{ $post->excerpt }}</p>
            <a href="{{ route('blog.show', $post) }}" class="btn btn-outline-primary">Read more</a>
            <hr>
        </article>
        @empty
        <p>No posts found in this category.</p>
        @endforelse

        <!-- Pagination -->
        <nav class="blog-pagination" aria-label="Pagination">
            {{ $posts->links() }}
        </nav>
    </div>

    <div class="col-md-4">
        <div class="position-sticky" style="top: 2rem;">
            <!-- Categories -->
            <div class="p-4">
                <h4 class="fst-italic">All Categories</h4>
                <ol class="list-unstyled mb-0">
                    @foreach($categories as $cat)
                    <li class="{{ $cat->id === $category->id ? 'fw-bold' : '' }}">
                        <a href="{{ route('blog.category', $cat) }}" class="d-flex justify-content-between">
                            {{ $cat->name }}
                            <span class="badge bg-secondary">{{ $cat->published_posts_count }}</span>
                        </a>
                    </li>
                    @endforeach
                </ol>
            </div>

            <!-- Back to blog -->
            <div class="p-4">
                <a href="{{ route('blog.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Back to Blog
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
