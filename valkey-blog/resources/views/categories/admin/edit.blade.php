@extends('layouts.app')

@section('title', 'Edit Category: ' . $category->name)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Edit Category</h1>
                <div class="btn-group">
                    <a href="{{ route('categories.show', $category->slug) }}" class="btn btn-outline-primary">
                        <i class="bi bi-eye"></i> View Category
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Categories
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.categories.update', $category) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Name Field -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $category->name) }}" 
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

                        <!-- Slug Field -->
                        <div class="mb-3">
                            <label for="slug" class="form-label">URL Slug</label>
                            <input type="text" 
                                   class="form-control @error('slug') is-invalid @enderror" 
                                   id="slug" 
                                   name="slug" 
                                   value="{{ old('slug', $category->slug) }}" 
                                   maxlength="255"
                                   placeholder="URL-safe version of the name">
                            @error('slug')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">
                                Current URL: <a href="{{ route('categories.show', $category->slug) }}" target="_blank">{{ route('categories.show', $category->slug) }}</a><br>
                                <strong>Warning:</strong> Changing the slug will change the category's URL. This may break existing links.
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
                                      placeholder="Brief description of what this category covers (optional)">{{ old('description', $category->description) }}</textarea>
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
                                           value="{{ old('color', $category->color ?: '#007bff') }}" 
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
                                                 style="width: 40px; height: 40px; background-color: {{ old('color', $category->color ?: '#007bff') }};"></div>
                                        </div>
                                        <div>
                                            <small class="text-muted">
                                                This color will be used as a visual indicator for the category throughout the site.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-text">Optional. Choose a color to help visually distinguish this category.</div>
                        </div>

                        <!-- Preview Section -->
                        <div class="mb-4">
                            <h6 class="text-muted">Preview</h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div id="preview-color-indicator" 
                                             class="me-3" 
                                             style="background-color: {{ old('color', $category->color ?: '#007bff') }}; width: 4px; height: 40px; border-radius: 2px;"></div>
                                        <div>
                                            <h6 class="mb-1" id="preview-name">{{ old('name', $category->name) }}</h6>
                                            <small class="text-muted">{{ $category->posts_count }} {{ Str::plural('post', $category->posts_count) }}</small>
                                            <div id="preview-description" class="mt-1 text-muted small">
                                                {{ old('description', $category->description) ?: 'No description provided...' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Category Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Category Information</h6>
                                        <p class="card-text mb-1">
                                            <strong>Posts:</strong> {{ $category->posts_count }} {{ Str::plural('post', $category->posts_count) }}
                                        </p>
                                        <p class="card-text mb-1">
                                            <strong>Created:</strong> {{ $category->created_at->format('M j, Y \a\t g:i A') }}
                                        </p>
                                        <p class="card-text mb-0">
                                            <strong>Last Updated:</strong> {{ $category->updated_at->format('M j, Y \a\t g:i A') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">URL Information</h6>
                                        <p class="card-text mb-1">
                                            <strong>Slug:</strong> <code>{{ $category->slug }}</code>
                                        </p>
                                        <p class="card-text mb-0">
                                            <strong>Public URL:</strong><br>
                                            <a href="{{ route('categories.show', $category->slug) }}" target="_blank" class="text-break">
                                                {{ route('categories.show', $category->slug) }}
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Category
                            </button>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <div class="ms-auto">
                                @if($category->posts_count == 0)
                                    <form action="{{ route('admin.categories.destroy', $category) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this category? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="bi bi-trash"></i> Delete Category
                                        </button>
                                    </form>
                                @else
                                    <button type="button" 
                                            class="btn btn-outline-danger disabled" 
                                            title="Cannot delete category with {{ $category->posts_count }} posts"
                                            disabled>
                                        <i class="bi bi-trash"></i> Delete Category
                                    </button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Posts in Category -->
            @if($category->posts_count > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">Posts in this Category ({{ $category->posts_count }})</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0 text-muted">
                                This category contains {{ $category->posts_count }} {{ Str::plural('post', $category->posts_count) }}. 
                                You cannot delete this category until all posts are moved to other categories or deleted.
                            </p>
                            <a href="{{ route('categories.show', $category->slug) }}" class="btn btn-sm btn-outline-primary">
                                View Posts
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Help Section -->
            <div class="card mt-4 bg-light border-0">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-info-circle text-primary"></i> Category Guidelines
                    </h6>
                    <ul class="mb-0 small">
                        <li><strong>Keep names concise:</strong> Use 1-2 words when possible</li>
                        <li><strong>Be specific:</strong> "Web Development" is better than just "Development"</li>
                        <li><strong>Avoid overlap:</strong> Make sure categories are distinct from each other</li>
                        <li><strong>URL changes:</strong> Changing the slug will break existing links to this category</li>
                        <li><strong>Deletion:</strong> Categories with posts cannot be deleted</li>
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
    const originalSlug = slugInput ? slugInput.value : '';
    let slugManuallyChanged = false;

    // Function to generate slug from name
    function generateSlug(name) {
        return name
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
                    Current URL: <a href="{{ route('categories.show', $category->slug) }}" target="_blank">{{ route('categories.show', $category->slug) }}</a><br>
                    <strong class="text-warning">Warning:</strong> Changing the slug will change the category's URL. This may break existing links.
                `;
            }
        });

        // Add button to regenerate slug from current name
        const regenerateBtn = document.createElement('button');
        regenerateBtn.type = 'button';
        regenerateBtn.className = 'btn btn-sm btn-outline-secondary mt-2';
        regenerateBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Regenerate from Name';
        regenerateBtn.addEventListener('click', function() {
            if (nameInput && nameInput.value.trim()) {
                if (confirm('This will replace the current slug with one generated from the name. Are you sure?')) {
                    slugInput.value = generateSlug(nameInput.value);
                    slugManuallyChanged = true;
                    slugInput.dispatchEvent(new Event('input'));
                }
            }
        });
        
        slugInput.parentNode.appendChild(regenerateBtn);
    }

    // Update color preview
    if (colorInput && colorPreview && previewColorIndicator) {
        colorInput.addEventListener('input', function() {
            colorPreview.style.backgroundColor = this.value;
            previewColorIndicator.style.backgroundColor = this.value;
        });
    }

    // Update name preview
    if (nameInput && previewName) {
        nameInput.addEventListener('input', function() {
            previewName.textContent = this.value.trim() || '{{ $category->name }}';
        });
    }

    // Update description preview
    if (descriptionInput && previewDescription) {
        descriptionInput.addEventListener('input', function() {
            previewDescription.textContent = this.value.trim() || 'No description provided...';
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