@extends('layouts.blog')

@section('title', 'Laravel Blog - Home')
@section('description', 'Welcome to our Laravel blog featuring the latest posts and insights.')

@section('content')
    @if($featuredPost)
    <!-- Featured Post -->
    <div class="p-4 p-md-5 mb-4 text-white rounded bg-dark">
        <div class="col-md-6 px-0">
            <h1 class="display-4 fst-italic">{{ $featuredPost->title }}</h1>
            <p class="lead my-3">{{ $featuredPost->excerpt }}</p>
            <p class="lead mb-0">
                <a href="{{ route('blog.show', $featuredPost) }}" class="text-white fw-bold">Continue reading...</a>
            </p>
        </div>
    </div>
    @endif

    <div class="row mb-2">
        @foreach($posts->take(2) as $post)
        <div class="col-md-6">
            <div class="row g-0 border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">
                <div class="col p-4 d-flex flex-column position-static">
                    <strong class="d-inline-block mb-2 text-primary">{{ $post->category->name }}</strong>
                    <h3 class="mb-0">{{ $post->title }}</h3>
                    <div class="mb-1 text-muted">{{ $post->published_at->format('M d') }}</div>
                    <p class="card-text mb-auto">{{ $post->excerpt }}</p>
                    <a href="{{ route('blog.show', $post) }}" class="stretched-link">Continue reading</a>
                </div>
                @if($post->image)
                <div class="col-auto d-none d-lg-block">
                    <img src="{{ $post->image }}" width="200" height="250" alt="Post image" class="bd-placeholder-img">
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-md-8">
            <h3 class="pb-4 mb-4 fst-italic border-bottom">Latest Posts</h3>

            @foreach($posts->skip(2) as $post)
            <article class="blog-post">
                <h2 class="blog-post-title">
                    <a href="{{ route('blog.show', $post) }}" class="text-decoration-none">{{ $post->title }}</a>
                </h2>
                <p class="blog-post-meta">{{ $post->published_at->format('F d, Y') }} by
                    <span class="badge category-badge" style="background-color: {{ $post->category->color }}">
                        {{ $post->category->name }}
                    </span>
                </p>
                <p>{{ $post->excerpt }}</p>
                <a href="{{ route('blog.show', $post) }}" class="btn btn-outline-primary">Read more</a>
                <hr>
            </article>
            @endforeach

            <!-- Pagination -->
            <nav class="blog-pagination" aria-label="Pagination">
                {{ $posts->links() }}
            </nav>
        </div>

        <div class="col-md-4">
            <div class="position-sticky" style="top: 2rem;">
                <!-- About -->
                <div class="p-4 mb-3 bg-light rounded">
                    <h4 class="fst-italic">About</h4>
                    <p class="mb-0">Welcome to our Laravel blog! Here you'll find the latest insights, tutorials, and thoughts on web development, Laravel, and more.</p>
                </div>

                <!-- Categories -->
                <div class="p-4">
                    <h4 class="fst-italic">Categories</h4>
                    <ol class="list-unstyled mb-0">
                        @foreach($categories as $category)
                        <li>
                            <a href="{{ route('blog.category', $category) }}" class="d-flex justify-content-between">
                                {{ $category->name }}
                                <span class="badge bg-secondary">{{ $category->published_posts_count }}</span>
                            </a>
                        </li>
                        @endforeach
                    </ol>
                </div>

                <!-- Social -->
                <div class="p-4">
                    <h4 class="fst-italic">Elsewhere</h4>
                    <ol class="list-unstyled">
                        <li><a href="#">GitHub</a></li>
                        <li><a href="#">Twitter</a></li>
                        <li><a href="#">Facebook</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection
