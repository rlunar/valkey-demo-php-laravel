@extends('layouts.app')

@section('title', 'Manage Categories')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Manage Categories</h1>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create Category
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
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Posts</th>
                                        <th width="200">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categories as $category)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $category->name }}</div>
                                                <small class="text-muted">{{ $category->slug }}</small>
                                            </td>
                                            <td>{{ Str::limit($category->description, 50) }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $category->posts_count }}</span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('categories.show', $category->slug) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="View Category">
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
                                                              onsubmit="return confirm('Are you sure you want to delete this category?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-outline-danger" 
                                                                    title="Delete Category">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
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
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-tags" style="font-size: 3rem; color: #6c757d;"></i>
                    </div>
                    <h4 class="text-muted">No categories found</h4>
                    <p class="text-muted">Get started by creating your first category.</p>
                    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create Your First Category
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection