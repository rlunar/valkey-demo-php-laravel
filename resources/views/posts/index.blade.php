@extends('layouts.app')

@section('title', 'Manage Posts')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Manage Posts</h1>
                <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create New Post
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.posts.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="category" class="form-label">Category</label>
                            <select name="category" id="category" class="form-select">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                            {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }} ({{ $category->posts_count }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="tag" class="form-label">Tag</label>
                            <select name="tag" id="tag" class="form-select">
                                <option value="">All Tags</option>
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->id }}" 
                                            {{ request('tag') == $tag->id ? 'selected' : '' }}>
                                        {{ $tag->name }} ({{ $tag->posts_count }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                            <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Active Filters Display -->
            @if(request()->hasAny(['category', 'tag', 'status']))
                <div class="mb-3">
                    <small class="text-muted">Active filters:</small>
                    @if(request('category'))
                        @php $selectedCategory = $categories->firstWhere('id', request('category')) @endphp
                        @if($selectedCategory)
                            <span class="badge bg-primary ms-1">Category: {{ $selectedCategory->name }}</span>
                        @endif
                    @endif
                    @if(request('tag'))
                        @php $selectedTag = $tags->firstWhere('id', request('tag')) @endphp
                        @if($selectedTag)
                            <span class="badge bg-secondary ms-1">Tag: {{ $selectedTag->name }}</span>
                        @endif
                    @endif
                    @if(request('status'))
                        <span class="badge bg-info ms-1">Status: {{ ucfirst(request('status')) }}</span>
                    @endif
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
                                        <th>Category</th>
                                        <th>Tags</th>
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
                                            <td>
                                                <x-category-badge :category="$post->category" />
                                            </td>
                                            <td>
                                                <x-tag-list :tags="$post->tags" :limit="3" />
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
                                                    <a href="{{ route('post.show', $post->slug) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="View Post">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.posts.edit', $post) }}" 
                                                       class="btn btn-sm btn-outline-secondary" 
                                                       title="Edit Post">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('admin.posts.destroy', $post) }}" 
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
                        {{ $posts->appends(request()->query())->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-file-text" style="font-size: 3rem; color: #6c757d;"></i>
                    </div>
                    <h4 class="text-muted">No posts found</h4>
                    <p class="text-muted">Get started by creating your first blog post.</p>
                    <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create Your First Post
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection