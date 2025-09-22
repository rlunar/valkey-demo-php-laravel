@extends('layouts.app')

@section('title', 'Edit Post: ' . $post->title)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Edit Post</h1>
                <div class="btn-group">
                    <a href="{{ route('post.show', $post->slug) }}" class="btn btn-outline-primary">
                        <i class="bi bi-eye"></i> View Post
                    </a>
                    <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Posts
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.posts.update', $post) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Title Field -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $post->title) }}" 
                                   required 
                                   maxlength="255"
                                   placeholder="Enter post title">
                            @error('title')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Slug Field -->
                        <div class="mb-3">
                            <label for="slug" class="form-label">URL Slug</label>
                            <input type="text" 
                                   class="form-control @error('slug') is-invalid @enderror" 
                                   id="slug" 
                                   name="slug" 
                                   value="{{ old('slug', $post->slug) }}" 
                                   maxlength="255"
                                   placeholder="URL-safe version of the title">
                            @error('slug')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">
                                Current URL: <a href="{{ route('post.show', $post->slug) }}" target="_blank">{{ route('post.show', $post->slug) }}</a><br>
                                <strong>Warning:</strong> Changing the slug will change the post's URL. This may break existing links.
                            </div>
                        </div>

                        <!-- Excerpt Field -->
                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Excerpt</label>
                            <textarea class="form-control @error('excerpt') is-invalid @enderror" 
                                      id="excerpt" 
                                      name="excerpt" 
                                      rows="3" 
                                      maxlength="500"
                                      placeholder="Brief description of the post (optional)">{{ old('excerpt', $post->excerpt) }}</textarea>
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
                                      placeholder="Write your post content here...">{{ old('content', $post->content) }}</textarea>
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
                                <option value="draft" {{ old('status', $post->status) === 'draft' ? 'selected' : '' }}>
                                    Draft
                                </option>
                                <option value="published" {{ old('status', $post->status) === 'published' ? 'selected' : '' }}>
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

                        <!-- Post Metadata -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Post Information</h6>
                                        <p class="card-text mb-1">
                                            <strong>Author:</strong> {{ $post->user->name }}
                                        </p>
                                        <p class="card-text mb-1">
                                            <strong>Created:</strong> {{ $post->created_at->format('M j, Y \a\t g:i A') }}
                                        </p>
                                        <p class="card-text mb-1">
                                            <strong>Last Updated:</strong> {{ $post->updated_at->format('M j, Y \a\t g:i A') }}
                                        </p>
                                        @if($post->published_at)
                                            <p class="card-text mb-0">
                                                <strong>Published:</strong> {{ $post->published_at->format('M j, Y \a\t g:i A') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">URL Information</h6>
                                        <p class="card-text mb-1">
                                            <strong>Slug:</strong> <code>{{ $post->slug }}</code>
                                        </p>
                                        <p class="card-text mb-0">
                                            <strong>Public URL:</strong><br>
                                            <a href="{{ route('post.show', $post->slug) }}" target="_blank" class="text-break">
                                                {{ route('post.show', $post->slug) }}
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Post
                            </button>
                            <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <div class="ms-auto">
                                <form action="{{ route('admin.posts.destroy', $post) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this post? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-trash"></i> Delete Post
                                    </button>
                                </form>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    const originalSlug = slugInput ? slugInput.value : '';
    let slugManuallyChanged = false;

    // Function to generate slug from title
    function generateSlug(title) {
        return title
            .toLowerCase()
            .trim()
            .replace(/[^\w\s-]/g, '') // Remove special characters except spaces and hyphens
            .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with hyphens
            .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
    }

    // Track changes to slug field
    if (slugInput) {
        slugInput.addEventListener('input', function() {
            slugManuallyChanged = this.value !== originalSlug;
            
            // Format the slug as user types
            if (this.value) {
                const formattedSlug = generateSlug(this.value);
                if (this.value !== formattedSlug) {
                    const cursorPosition = this.selectionStart;
                    this.value = formattedSlug;
                    this.setSelectionRange(cursorPosition, cursorPosition);
                }
            }

            // Update warning message
            const helpText = slugInput.parentNode.querySelector('.form-text');
            if (helpText && slugManuallyChanged) {
                helpText.innerHTML = `
                    Current URL: <a href="{{ route('post.show', $post->slug) }}" target="_blank">{{ route('post.show', $post->slug) }}</a><br>
                    <strong class="text-warning">Warning:</strong> Changing the slug will change the post's URL. This may break existing links.
                `;
            }
        });

        // Add button to regenerate slug from current title
        const regenerateBtn = document.createElement('button');
        regenerateBtn.type = 'button';
        regenerateBtn.className = 'btn btn-sm btn-outline-secondary mt-2';
        regenerateBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Regenerate from Title';
        regenerateBtn.addEventListener('click', function() {
            if (titleInput && titleInput.value.trim()) {
                if (confirm('This will replace the current slug with one generated from the title. Are you sure?')) {
                    slugInput.value = generateSlug(titleInput.value);
                    slugManuallyChanged = true;
                    slugInput.dispatchEvent(new Event('input'));
                }
            }
        });
        
        slugInput.parentNode.appendChild(regenerateBtn);
    }

    // Auto-resize textarea based on content
    const contentTextarea = document.getElementById('content');
    if (contentTextarea) {
        // Set initial height
        contentTextarea.style.height = 'auto';
        contentTextarea.style.height = (contentTextarea.scrollHeight) + 'px';
        
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