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

                        <!-- Slug Field (Optional) -->
                        <div class="mb-3">
                            <label for="slug" class="form-label">URL Slug</label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control @error('slug') is-invalid @enderror" 
                                       id="slug" 
                                       name="slug" 
                                       value="{{ old('slug') }}" 
                                       maxlength="255"
                                       placeholder="Leave empty to auto-generate from title">
                                <span class="input-group-text" id="slug-status">
                                    <i class="bi bi-check-circle text-success" style="display: none;" id="slug-valid"></i>
                                    <i class="bi bi-exclamation-circle text-warning" style="display: none;" id="slug-warning"></i>
                                </span>
                            </div>
                            @error('slug')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">Optional. If left empty, will be automatically generated from the title. Must be unique and URL-safe (lowercase letters, numbers, and hyphens only).</div>
                            <div id="url-preview" class="form-text text-muted mt-1" style="display: none;">
                                <strong>URL Preview:</strong> <span id="preview-url"></span>
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
                                      placeholder="Brief description of the post (optional)">{{ old('excerpt') }}</textarea>
                            @error('excerpt')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">Optional. Maximum 500 characters. If left empty, will be auto-generated from content.</div>
                            <div id="excerpt-preview" class="mt-2 p-2 bg-light rounded" style="display: none;">
                                <small class="text-muted"><strong>Auto-generated preview:</strong></small>
                                <div id="excerpt-preview-text" class="mt-1"></div>
                            </div>
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
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    let slugManuallyEdited = false;

    // Function to generate slug from title
    function generateSlug(title) {
        return title
            .toLowerCase()
            .trim()
            .replace(/[^\w\s-]/g, '') // Remove special characters except spaces and hyphens
            .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with hyphens
            .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
    }

    // Auto-generate slug from title if slug hasn't been manually edited
    if (titleInput && slugInput) {
        const urlPreview = document.getElementById('url-preview');
        const previewUrl = document.getElementById('preview-url');
        const baseUrl = '{{ url("/") }}/';

        function updateUrlPreview() {
            const currentSlug = slugInput.value.trim();
            const slugValid = document.getElementById('slug-valid');
            const slugWarning = document.getElementById('slug-warning');
            
            // Validate slug format
            const slugRegex = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;
            const isValidFormat = !currentSlug || slugRegex.test(currentSlug);
            
            // Update status indicators
            if (slugValid && slugWarning) {
                if (currentSlug && isValidFormat) {
                    slugValid.style.display = 'inline';
                    slugWarning.style.display = 'none';
                } else if (currentSlug && !isValidFormat) {
                    slugValid.style.display = 'none';
                    slugWarning.style.display = 'inline';
                } else {
                    slugValid.style.display = 'none';
                    slugWarning.style.display = 'none';
                }
            }
            
            if (currentSlug && urlPreview && previewUrl) {
                previewUrl.textContent = baseUrl + currentSlug;
                urlPreview.style.display = 'block';
            } else if (urlPreview) {
                urlPreview.style.display = 'none';
            }
        }

        titleInput.addEventListener('input', function() {
            if (!slugManuallyEdited && this.value.trim()) {
                const generatedSlug = generateSlug(this.value);
                slugInput.value = generatedSlug;
                updateUrlPreview();
                
                // Update the help text to show the generated slug
                const helpText = slugInput.parentNode.querySelector('.form-text:first-of-type');
                if (helpText && generatedSlug) {
                    helpText.innerHTML = `Auto-generated: <code>${generatedSlug}</code>. You can edit this if needed.`;
                } else if (helpText) {
                    helpText.innerHTML = 'Optional. If left empty, will be automatically generated from the title. Must be unique and URL-safe (lowercase letters, numbers, and hyphens only).';
                }
            }
        });

        // Track if user manually edits the slug
        slugInput.addEventListener('input', function() {
            slugManuallyEdited = this.value.trim() !== '';
            
            // Format the slug as user types
            if (this.value) {
                const formattedSlug = generateSlug(this.value);
                if (this.value !== formattedSlug) {
                    const cursorPosition = this.selectionStart;
                    this.value = formattedSlug;
                    this.setSelectionRange(cursorPosition, cursorPosition);
                }
            }
            
            updateUrlPreview();
        });

        // Reset manual edit flag if slug is cleared
        slugInput.addEventListener('blur', function() {
            if (!this.value.trim()) {
                slugManuallyEdited = false;
                // Regenerate from title if available
                if (titleInput.value.trim()) {
                    this.value = generateSlug(titleInput.value);
                    updateUrlPreview();
                }
            }
        });

        // Initialize URL preview if slug already has value
        updateUrlPreview();
    }

    // Auto-resize textarea based on content
    const contentTextarea = document.getElementById('content');
    if (contentTextarea) {
        contentTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }
    
    // Character counter for excerpt and auto-preview
    const excerptTextarea = document.getElementById('excerpt');
    const contentTextarea = document.getElementById('content');
    const excerptPreview = document.getElementById('excerpt-preview');
    const excerptPreviewText = document.getElementById('excerpt-preview-text');
    
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
        
        function generateExcerpt(content, length = 160) {
            // Strip HTML tags and get plain text
            const plainText = content.replace(/<[^>]*>/g, '');
            
            // Remove extra whitespace
            const cleanText = plainText.replace(/\s+/g, ' ').trim();
            
            // Truncate to specified length
            if (cleanText.length <= length) {
                return cleanText;
            }
            
            // Find the last complete word within the length limit
            const truncated = cleanText.substring(0, length);
            const lastSpace = truncated.lastIndexOf(' ');
            
            if (lastSpace !== -1) {
                return truncated.substring(0, lastSpace) + '...';
            }
            
            return truncated + '...';
        }
        
        function updateExcerptPreview() {
            const hasExcerpt = excerptTextarea.value.trim().length > 0;
            const hasContent = contentTextarea && contentTextarea.value.trim().length > 0;
            
            if (!hasExcerpt && hasContent && excerptPreview && excerptPreviewText) {
                const autoExcerpt = generateExcerpt(contentTextarea.value);
                excerptPreviewText.textContent = autoExcerpt;
                excerptPreview.style.display = 'block';
            } else if (excerptPreview) {
                excerptPreview.style.display = 'none';
            }
        }
        
        excerptTextarea.addEventListener('input', function() {
            updateCounter();
            updateExcerptPreview();
        });
        
        if (contentTextarea) {
            contentTextarea.addEventListener('input', updateExcerptPreview);
        }
        
        updateCounter();
        updateExcerptPreview();
    }
});
</script>
@endsection