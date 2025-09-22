@extends('layouts.blog')

@section('title', $post->title . ' - Laravel Blog')
@section('description', $post->excerpt)

@section('content')
<div class="row">
    <div class="col-md-8">
        <article class="blog-post">
            <h2 class="blog-post-title">{{ $post->title }}</h2>
            <p class="blog-post-meta">{{ $post->published_at->format('F d, Y') }} in
                <a href="{{ route('blog.category', $post->category) }}" class="badge category-badge text-decoration-none" style="background-color: {{ $post->category->color }}">
                    {{ $post->category->name }}
                </a>
            </p>

            @if($post->image)
            <img src="{{ $post->image }}" class="img-fluid mb-4" alt="{{ $post->title }}">
            @endif

            <div class="blog-content">
                {!! nl2br(e($post->content)) !!}
            </div>
        </article>

        <!-- Comments Section -->
        <section class="mt-5">
            <h4>Comments ({{ $post->approvedComments->count() }})</h4>

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
                <small class="text-muted">{{ $comment->created_at->format('F d, Y \a\t g:i A') }}</small>
                <p class="mt-2">{{ $comment->content }}</p>
            </div>
            @endforeach
        </section>
    </div>

    <div class="col-md-4">
        <div class="position-sticky" style="top: 2rem;">
            <!-- Related Posts -->
            @if($relatedPosts->count() > 0)
            <div class="p-4 mb-3 bg-light rounded">
                <h4 class="fst-italic">Related Posts</h4>
                @foreach($relatedPosts as $relatedPost)
                <div class="mb-2">
                    <a href="{{ route('blog.show', $relatedPost) }}" class="text-decoration-none">
                        {{ $relatedPost->title }}
                    </a>
                    <small class="d-block text-muted">{{ $relatedPost->published_at->format('M d, Y') }}</small>
                </div>
                @endforeach
            </div>
            @endif

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
