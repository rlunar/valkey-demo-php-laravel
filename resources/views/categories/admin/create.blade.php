@extends('layouts.app')

@section('title', 'Create New Category')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Create New Category</h1>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Categories
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.categories.store') }}" method="POST">
                        @csrf

                        <!-- Name Field -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required 
                                   maxlength="255"
                                   placeholder="Enter category name">
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">Choose a descriptive name for your category (e.g., "Technology", "Tutorials", "News")</div>
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
                                       placeholder="Leave empty to auto-generate from name">
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
                            <div class="form-text">Optional. If left empty, will be automatically generated from the name. Must be unique and URL-safe (lowercase letters, numbers, and hyphens only).</div>
                            <div id="url-preview" class="form-text text-muted mt-1" style="display: none;">
                                <strong>URL Preview:</strong> <span id="preview-url"></span>
                            </div>
                        </div>

                        <!-- Description Field -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3" 
                                      maxlength="1000"
                                      placeholder="Brief description of what this category covers (optional)">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">Optional. Maximum 1000 characters. This helps readers understand what content to expect in this category.</div>
                        </div>

                        <!-- Color Field -->
                        <div class="mb-4">
                            <label for="color" class="form-label">Category Color</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="color" 
                                           class="form-control form-control-color @error('color') is-invalid @enderror" 
                                           id="color" 
                                           name="color" 
                                           value="{{ old('color', '#007bff') }}" 
                                           title="Choose a color for this category">
                                    @error('color')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center h-100">
                                        <div class="me-3">
                                            <div id="color-preview" 
                                                 class="border rounded" 
                                                 style="width: 40px; height: 40px; background-color: {{ old('color', '#007bff') }};"></div>
                                        </div>
                                        <div>
                                            <small class="text-muted">
                                                This color will be used as a visual indicator for the category throughout the site.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-text">Optional. Choose a color to help visually distinguish this category. Default is blue.</div>
                        </div>

                        <!-- Preview Section -->
                        <div class="mb-4">
                            <h6 class="text-muted">Preview</h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div id="preview-color-indicator" 
                                             class="me-3" 
                                             style="background-color: {{ old('color', '#007bff') }}; width: 4px; height: 40px; border-radius: 2px;"></div>
                                        <div>
                                            <h6 class="mb-1" id="preview-name">{{ old('name', 'Category Name') }}</h6>
                                            <small class="text-muted">0 posts</small>
                                            <div id="preview-description" class="mt-1 text-muted small">
                                                {{ old('description', 'Category description will appear here...') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Create Category
                            </button>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Help Section -->
            <div class="card mt-4 bg-light border-0">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-info-circle text-primary"></i> Category Guidelines
                    </h6>
                    <ul class="mb-0 small">
                        <li><strong>Keep names concise:</strong> Use 1-2 words when possible (e.g., "Technology" instead of "Technology Articles and Tutorials")</li>
                        <li><strong>Be specific:</strong> "Web Development" is better than just "Development"</li>
                        <li><strong>Avoid overlap:</strong> Make sure categories are distinct from each other</li>
                        <li><strong>Think long-term:</strong> Choose names that will make sense as your content grows</li>
                        <li><strong>Use colors wisely:</strong> Pick colors that work well together and are accessible</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    const descriptionInput = document.getElementById('description');
    const colorInput = document.getElementById('color');
    const colorPreview = document.getElementById('color-preview');
    const previewColorIndicator = document.getElementById('preview-color-indicator');
    const previewName = document.getElementById('preview-name');
    const previewDescription = document.getElementById('preview-description');
    let slugManuallyEdited = false;

    // Function to generate slug from name
    function generateSlug(name) {
        return name
            .toLowerCase()
            .trim()
            .replace(/[^\w\s-]/g, '') // Remove special characters except spaces and hyphens
            .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with hyphens
            .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
    }

    // Auto-generate slug from name if slug hasn't been manually edited
    if (nameInput && slugInput) {
        const urlPreview = document.getElementById('url-preview');
        const previewUrl = document.getElementById('preview-url');
        const baseUrl = '{{ url("/categories") }}/';

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

        nameInput.addEventListener('input', function() {
            if (!slugManuallyEdited && this.value.trim()) {
                const generatedSlug = generateSlug(this.value);
                slugInput.value = generatedSlug;
                updateUrlPreview();
            }
            
            // Update preview
            previewName.textContent = this.value.trim() || 'Category Name';
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
                // Regenerate from name if available
                if (nameInput.value.trim()) {
                    this.value = generateSlug(nameInput.value);
                    updateUrlPreview();
                }
            }
        });

        // Initialize URL preview if slug already has value
        updateUrlPreview();
    }

    // Update color preview
    if (colorInput && colorPreview && previewColorIndicator) {
        colorInput.addEventListener('input', function() {
            colorPreview.style.backgroundColor = this.value;
            previewColorIndicator.style.backgroundColor = this.value;
        });
    }

    // Update description preview
    if (descriptionInput && previewDescription) {
        descriptionInput.addEventListener('input', function() {
            previewDescription.textContent = this.value.trim() || 'Category description will appear here...';
        });
    }

    // Character counter for description
    if (descriptionInput) {
        const maxLength = 1000;
        const counter = document.createElement('div');
        counter.className = 'form-text text-end';
        counter.style.marginTop = '0.25rem';
        descriptionInput.parentNode.appendChild(counter);
        
        function updateCounter() {
            const remaining = maxLength - descriptionInput.value.length;
            counter.textContent = `${descriptionInput.value.length}/${maxLength} characters`;
            counter.className = remaining < 100 ? 'form-text text-end text-warning' : 'form-text text-end';
        }
        
        descriptionInput.addEventListener('input', updateCounter);
        updateCounter();
    }
});
</script>
@endsection