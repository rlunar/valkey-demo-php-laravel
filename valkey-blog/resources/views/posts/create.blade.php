@extends('layouts.app')

@section('title', 'Create New Post')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Create New Post</h1>
                <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Posts
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.posts.store') }}" method="POST">
                        @csrf

                        <!-- Title Field -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
                                   required 
                                   maxlength="255"
                                   placeholder="Enter post title">
                            @error('title')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Excerpt Field -->
                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Excerpt</label>
                            <textarea class="form-control @error('excerpt') is-invalid @enderror" 
                                      id="excerpt" 
                                      name="excerpt" 
                                      rows="3" 
                                      maxlength="500"
                                      placeholder="Brief description of the post (optional)">{{ old('excerpt') }}</textarea>
                            @error('excerpt')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">Optional. Maximum 500 characters.</div>
                        </div>

                        <!-- Content Field -->
                        <div class="mb-3">
                            <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" 
                                      name="content" 
                                      rows="15" 
                                      required
                                      placeholder="Write your post content here...">{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Status Field -->
                        <div class="mb-4">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="">Select status</option>
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>
                                    Draft
                                </option>
                                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>
                                    Published
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">
                                <strong>Draft:</strong> Post will be saved but not visible to visitors.<br>
                                <strong>Published:</strong> Post will be immediately visible to visitors.
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Create Post
                            </button>
                            <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-resize textarea based on content
    const contentTextarea = document.getElementById('content');
    if (contentTextarea) {
        contentTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }
    
    // Character counter for excerpt
    const excerptTextarea = document.getElementById('excerpt');
    if (excerptTextarea) {
        const maxLength = 500;
        const counter = document.createElement('div');
        counter.className = 'form-text text-end';
        counter.style.marginTop = '0.25rem';
        excerptTextarea.parentNode.appendChild(counter);
        
        function updateCounter() {
            const remaining = maxLength - excerptTextarea.value.length;
            counter.textContent = `${excerptTextarea.value.length}/${maxLength} characters`;
            counter.className = remaining < 50 ? 'form-text text-end text-warning' : 'form-text text-end';
        }
        
        excerptTextarea.addEventListener('input', updateCounter);
        updateCounter();
    }
});
</script>
@endsection