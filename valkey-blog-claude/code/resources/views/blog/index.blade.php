@extends('layouts.blog')

@section('title', 'Laravel Blog - Home')
@section('description', 'Welcome to our Laravel blog featuring the latest posts and insights.')

@section('content')
    @if($featuredPost)
    <!-- Featured Post -->
    <div class="p-4 p-md-5 mb-4 rounded text-body-emphasis bg-body-secondary">
        <div class="col-lg-6 px-0">
            <h1 class="display-4 fst-italic">{{ $featuredPost->title }}</h1>
            <p class="lead my-3">{{ $featuredPost->excerpt }}</p>
            <p class="lead mb-0">
                <a href="{{ route('blog.show', $featuredPost) }}" class="text-body-emphasis fw-bold">Continue reading...</a>
            </p>
        </div>
    </div>
    @endif

    <div class="row mb-2">
        @foreach($posts->take(2) as $post)
        <div class="col-md-6">
            <div class="row g-0 border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">
                <div class="col p-4 d-flex flex-column position-static">
                    <strong class="d-inline-block mb-2 text-primary-emphasis">{{ $post->category->name }}</strong>
                    <h3 class="mb-0">{{ $post->title }}</h3>
                    <div class="mb-1 text-body-secondary">{{ $post->published_at->format('M d') }}</div>
                    <p class="card-text mb-auto">{{ $post->excerpt }}</p>
                    <a href="{{ route('blog.show', $post) }}" class="icon-link gap-1 icon-link-hover stretched-link">
                        Continue reading
                        <svg class="bi" aria-hidden="true" width="16" height="16">
                            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                        </svg>
                    </a>
                </div>
                <div class="col-auto d-none d-lg-block">
                    <svg aria-label="Placeholder: Thumbnail" class="bd-placeholder-img" height="250" preserveAspectRatio="xMidYMid slice" role="img" width="200" xmlns="http://www.w3.org/2000/svg">
                        <title>Placeholder</title>
                        <rect width="100%" height="100%" fill="#55595c"></rect>
                        <text x="50%" y="50%" fill="#eceeef" dy=".3em">Thumbnail</text>
                    </svg>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-5">
        <div class="col-md-8">
            <h3 class="pb-4 mb-4 fst-italic border-bottom">From the Firehose</h3>

            @foreach($posts->skip(2) as $post)
            <article class="blog-post">
                <h2 class="display-5 link-body-emphasis mb-1">
                    <a href="{{ route('blog.show', $post) }}" class="text-decoration-none link-body-emphasis">{{ $post->title }}</a>
                </h2>
                <p class="blog-post-meta">{{ $post->published_at->format('F d, Y') }} by <a href="#">{{ $post->category->name }}</a></p>
                <p>{{ $post->excerpt }}</p>
                <hr>
            </article>
            @endforeach

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

                <!-- Recent Posts -->
                <div>
                    <h4 class="fst-italic">Recent posts</h4>
                    <ul class="list-unstyled">
                        @foreach($posts->take(3) as $recentPost)
                        <li>
                            <a class="d-flex flex-column flex-lg-row gap-3 align-items-start align-items-lg-center py-3 link-body-emphasis text-decoration-none border-top" href="{{ route('blog.show', $recentPost) }}">
                                <svg aria-hidden="true" class="bd-placeholder-img" height="96" preserveAspectRatio="xMidYMid slice" width="100%" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="100%" height="100%" fill="#777"></rect>
                                </svg>
                                <div class="col-lg-8">
                                    <h6 class="mb-0">{{ $recentPost->title }}</h6>
                                    <small class="text-body-secondary">{{ $recentPost->published_at->format('F d, Y') }}</small>
                                </div>
                            </a>
                        </li>
                        @endforeach
                    </ul>
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
