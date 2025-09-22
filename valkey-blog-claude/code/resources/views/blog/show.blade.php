@extends('layouts.blog')

@section('title', $post->title . ' - Laravel Blog')
@section('description', $post->excerpt)

@section('content')
<div class="row g-5">
    <div class="col-md-8">
        <article class="blog-post">
            <h2 class="display-5 link-body-emphasis mb-1">{{ $post->title }}</h2>
            <p class="blog-post-meta">{{ $post->published_at->format('F d, Y') }} by <a href="{{ route('blog.category', $post->category) }}">{{ $post->category->name }}</a></p>

            @if($post->image)
            <img src="{{ $post->image }}" class="img-fluid mb-4" alt="{{ $post->title }}">
            @endif

            <div class="blog-content">
                {!! nl2br(e($post->content)) !!}
            </div>
        </article>

        <!-- Comments Section -->
        <section class="mt-5">
            <h4 class="pb-4 mb-4 fst-italic border-bottom">Comments ({{ $post->approvedComments->count() }})</h4>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <!-- Comment Form -->
            <form action="{{ route('blog.comment', $post) }}" method="POST" class="mb-4">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="content" class="form-label">Comment</label>
                    <textarea class="form-control @error('content') is-invalid @enderror"
                              id="content" name="content" rows="4" required>{{ old('content') }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Submit Comment</button>
            </form>

            <!-- Comments List -->
            @foreach($post->approvedComments as $comment)
            <div class="border-bottom pb-3 mb-3">
                <h6>{{ $comment->name }}</h6>
                <small class="text-body-secondary">{{ $comment->created_at->format('F d, Y \a\t g:i A') }}</small>
                <p class="mt-2">{{ $comment->content }}</p>
            </div>
            @endforeach
        </section>
    </div>

    <div class="col-md-4">
        <div class="position-sticky" style="top: 2rem;">
            <!-- About -->
            <div class="p-4 mb-3 bg-body-tertiary rounded">
                <h4 class="fst-italic">About</h4>
                <p class="mb-0">Customize this section to tell your visitors a little bit about your publication, writers, content, or something else entirely. Totally up to you.</p>
            </div>

            <!-- Related Posts -->
            @if($relatedPosts->count() > 0)
            <div>
                <h4 class="fst-italic">Related posts</h4>
                <ul class="list-unstyled">
                    @foreach($relatedPosts as $relatedPost)
                    <li>
                        <a class="d-flex flex-column flex-lg-row gap-3 align-items-start align-items-lg-center py-3 link-body-emphasis text-decoration-none border-top" href="{{ route('blog.show', $relatedPost) }}">
                            <svg aria-hidden="true" class="bd-placeholder-img" height="96" preserveAspectRatio="xMidYMid slice" width="100%" xmlns="http://www.w3.org/2000/svg">
                                <rect width="100%" height="100%" fill="#777"></rect>
                            </svg>
                            <div class="col-lg-8">
                                <h6 class="mb-0">{{ $relatedPost->title }}</h6>
                                <small class="text-body-secondary">{{ $relatedPost->published_at->format('F d, Y') }}</small>
                            </div>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

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
