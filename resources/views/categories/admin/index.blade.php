@extends('layouts.app')

@section('title', 'Manage Categories')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Manage Categories</h1>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create New Category
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($categories->count() > 0)
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40"></th>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Description</th>
                                        <th>Posts Count</th>
                                        <th>Created</th>
                                        <th width="200">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categories as $category)
                                        <tr>
                                            <td>
                                                @if($category->color)
                                                    <div class="category-color-indicator" 
                                                         style="background-color: {{ $category->color }}; width: 20px; height: 20px; border-radius: 3px; border: 1px solid #dee2e6;"
                                                         title="Category Color: {{ $category->color }}"></div>
                                                @else
                                                    <div class="bg-light border rounded" 
                                                         style="width: 20px; height: 20px;"
                                                         title="No color set"></div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $category->name }}</div>
                                                <small class="text-muted">
                                                    <a href="{{ route('categories.show', $category->slug) }}" 
                                                       target="_blank" 
                                                       class="text-decoration-none">
                                                        View Public Page <i class="bi bi-box-arrow-up-right"></i>
                                                    </a>
                                                </small>
                                            </td>
                                            <td>
                                                <code class="text-muted">{{ $category->slug }}</code>
                                            </td>
                                            <td>
                                                @if($category->description)
                                                    <span title="{{ $category->description }}">
                                                        {{ Str::limit($category->description, 50) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted fst-italic">No description</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $category->posts_count > 0 ? 'primary' : 'secondary' }}">
                                                    {{ $category->posts_count }} {{ Str::plural('post', $category->posts_count) }}
                                                </span>
                                            </td>
                                            <td>{{ $category->created_at->format('M j, Y') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('categories.show', $category->slug) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="View Category"
                                                       target="_blank">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.categories.edit', $category) }}" 
                                                       class="btn btn-sm btn-outline-secondary" 
                                                       title="Edit Category">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    @if($category->posts_count == 0)
                                                        <form action="{{ route('admin.categories.destroy', $category) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('Are you sure you want to delete this category? This action cannot be undone.')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-outline-danger" 
                                                                    title="Delete Category">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-danger disabled" 
                                                                title="Cannot delete category with posts"
                                                                disabled>
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @endif
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
                @if($categories->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $categories->links() }}
                    </div>
                @endif

                <!-- Category Statistics -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0">{{ $categories->total() }}</h4>
                                        <p class="mb-0">Total Categories</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-folder2-open" style="font-size: 2rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0">{{ $categories->where('posts_count', '>', 0)->count() }}</h4>
                                        <p class="mb-0">Categories with Posts</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0">{{ $categories->where('posts_count', 0)->count() }}</h4>
                                        <p class="mb-0">Empty Categories</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-exclamation-circle" style="font-size: 2rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-folder2-open" style="font-size: 3rem; color: #6c757d;"></i>
                    </div>
                    <h4 class="text-muted">No categories found</h4>
                    <p class="text-muted">Get started by creating your first blog category to organize your content.</p>
                    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create Your First Category
                    </a>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="mt-4 pt-4 border-top">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Quick Actions</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.categories.create') }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-plus-circle"></i> New Category
                            </a>
                            <a href="{{ route('categories.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i> View Public Page
                            </a>
                            <a href="{{ route('admin.posts.index') }}" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-file-text"></i> Manage Posts
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Tips</h6>
                        <ul class="small text-muted mb-0">
                            <li>Categories help organize your content by topic</li>
                            <li>Each post must belong to exactly one category</li>
                            <li>Categories with posts cannot be deleted</li>
                            <li>Use colors to visually distinguish categories</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .category-color-indicator {
        flex-shrink: 0;
    }
    
    .table td {
        vertical-align: middle;
    }
</style>
@endpush