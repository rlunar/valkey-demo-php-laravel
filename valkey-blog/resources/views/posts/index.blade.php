@extends('layouts.app')

@section('title', 'Manage Posts')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Manage Posts</h1>
                <a href="{{ route('posts.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create New Post
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($posts->count() > 0)
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Status</th>
                                        <th>Published Date</th>
                                        <th>Created</th>
                                        <th width="200">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($posts as $post)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $post->title }}</div>
                                                @if($post->excerpt)
                                                    <small class="text-muted">{{ Str::limit($post->excerpt, 60) }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $post->user->name }}</td>
                                            <td>
                                                @if($post->status === 'published')
                                                    <span class="badge bg-success">Published</span>
                                                @else
                                                    <span class="badge bg-secondary">Draft</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($post->published_at)
                                                    {{ $post->published_at->format('M j, Y') }}
                                                @else
                                                    <span class="text-muted">Not published</span>
                                                @endif
                                            </td>
                                            <td>{{ $post->created_at->format('M j, Y') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('home.show', $post->slug) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="View Post">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('posts.edit', $post) }}" 
                                                       class="btn btn-sm btn-outline-secondary" 
                                                       title="Edit Post">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('posts.destroy', $post) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to delete this post?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                title="Delete Post">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                @if($posts->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $posts->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-file-text" style="font-size: 3rem; color: #6c757d;"></i>
                    </div>
                    <h4 class="text-muted">No posts found</h4>
                    <p class="text-muted">Get started by creating your first blog post.</p>
                    <a href="{{ route('posts.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create Your First Post
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection