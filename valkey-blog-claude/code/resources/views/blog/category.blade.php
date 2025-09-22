@extends('layouts.blog')

@section('title', $category->name . ' - Laravel Blog')
@section('description', 'Posts in ' . $category->name . ' category')

@section('content')
<div class="p-4 p-md-5 mb-4 rounded text-body-emphasis bg-body-secondary">
    <div class="col-lg-6 px-0">
        <h1 class="display-4 fst-italic">{{ $category->name }}</h1>
        <p class="lead my-3">{{ $posts->total() }} {{ Str::plural('post', $posts->total()) }} in this category</p>
    </div>
</div>

<div class="row g-5">
    <div class="col-md-8">
        <h3 class="pb-4 mb-4 fst-italic border-bottom">{{ $category->name }} Posts</h3>

        @forelse($posts as $post)
        <article class="blog-post">
            <h2 class="display-5 link-body-emphasis mb-1">
                <a href="{{ route('blog.show', $post) }}" class="text-decoration-none link-body-emphasis">{{ $post->title }}</a>
            </h2>
            <p class="blog-post-meta">{{ $post->published_at->format('F d, Y') }} by <a href="#">{{ $post->category->name }}</a></p>
            <p>{{ $post->excerpt }}</p>
            <hr>
        </article>
        @empty
        <div class="text-center py-5">
            <h4>No posts found in this category.</h4>
            <p class="text-body-secondary">Check back later for new content!</p>
        </div>
        @endforelse

        <!-- Pagination -->
        <nav class="blog-pagination" aria-label="Pagination">
            <a class="btn btn-outline-primary rounded-pill" href="#">Older</a>
            <a class="btn btn-outline-secondary rounded-pill disabled" aria-disabled="true">Newer</a>
        </nav>
    </div>

    <div class="col-md-4">
        <div class="position-sticky" style="top: 2rem;">
            <!-- About -->
            <div class="p-4 mb-3 bg-body-tertiary rounded">
                <h4 class="fst-italic">About</h4>
                <p class="mb-0">Customize this section to tell your visitors a little bit about your publication, writers, content, or something else entirely. Totally up to you.</p>
            </div>

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

            <!-- Archives -->
            <div class="p-4">
                <h4 class="fst-italic">Archives</h4>
                <ol class="list-unstyled mb-0">
                    <li><a href="#">March 2021</a></li>
                    <li><a href="#">February 2021</a></li>
                    <li><a href="#">January 2021</a></li>
                    <li><a href="#">December 2020</a></li>
                    <li><a href="#">November 2020</a></li>
                    <li><a href="#">October 2020</a></li>
                </ol>
            </div>

            <!-- Elsewhere -->
            <div class="p-4">
                <h4 class="fst-italic">Elsewhere</h4>
                <ol class="list-unstyled">
                    <li><a href="#">GitHub</a></li>
                    <li><a href="#">Social</a></li>
                    <li><a href="#">Facebook</a></li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection
