@extends('layouts.app')

@section('title', 'Tag Management - Admin')
@section('description', 'Manage blog tags - view usage statistics and remove unused tags')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-tags"></i> Tag Management
                    </h1>
                    <p class="text-muted mb-0">Manage blog tags and monitor usage statistics</p>
                </div>
                <div>
                    <a href="{{ route('tags.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-eye"></i> View Public Tags
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $tags->total() }}</h4>
                                    <p class="mb-0">Total Tags</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-tags" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $tags->where('posts_count', '>', 0)->count() }}</h4>
                                    <p class="mb-0">Used Tags</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $tags->where('posts_count', 0)->count() }}</h4>
                                    <p class="mb-0">Unused Tags</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $tags->max('posts_count') ?? 0 }}</h4>
                                    <p class="mb-0">Most Used</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-star" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> All Tags
                    </h5>
                    <div class="d-flex gap-2">
                        <!-- Search Input -->
                        <div class="input-group" style="width: 300px;">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="tagSearch" 
                                   placeholder="Search tags..." autocomplete="off">
                        </div>
                        <!-- Bulk Actions -->
                        @if($tags->where('posts_count', 0)->count() > 0)
                            <button type="button" class="btn btn-outline-danger" id="bulkDeleteBtn" 
                                    data-bs-toggle="modal" data-bs-target="#bulkDeleteModal">
                                <i class="bi bi-trash"></i> Clean Unused
                            </button>
                        @endif
                    </div>
                </div>

                <div class="card-body p-0">
                    @if(session('success'))
                        <div class="alert alert-success mx-3 mt-3 mb-0">
                            <i class="bi bi-check-circle"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger mx-3 mt-3 mb-0">
                            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                        </div>
                    @endif

                    @if($tags->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="tagsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>Tag Name</th>
                                        <th>Slug</th>
                                        <th class="text-center">Posts Count</th>
                                        <th>Created Date</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tags as $tag)
                                        <tr class="tag-row" data-tag-name="{{ strtolower($tag->name) }}">
                                            <td class="ps-3">
                                                @if($tag->posts_count == 0)
                                                    <input type="checkbox" class="form-check-input tag-checkbox" 
                                                           value="{{ $tag->id }}">
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-tag me-2 text-muted"></i>
                                                    <div>
                                                        <strong>{{ $tag->name }}</strong>
                                                        @if($tag->posts_count > 0)
                                                            <br>
                                                            <a href="{{ route('tags.show', $tag->slug) }}" 
                                                               class="small text-decoration-none" target="_blank">
                                                                <i class="bi bi-eye"></i> View Public
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <code class="small">{{ $tag->slug }}</code>
                                            </td>
                                            <td class="text-center">
                                                @if($tag->posts_count > 0)
                                                    <span class="badge bg-success fs-6">{{ $tag->posts_count }}</span>
                                                @else
                                                    <span class="badge bg-secondary fs-6">0</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $tag->created_at->format('M d, Y') }}
                                                    <br>
                                                    {{ $tag->created_at->diffForHumans() }}
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                @if($tag->posts_count > 0)
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle"></i> Active
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning">
                                                        <i class="bi bi-exclamation-triangle"></i> Unused
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($tag->posts_count == 0)
                                                    <form action="{{ route('admin.tags.destroy', $tag) }}" method="POST" 
                                                          class="d-inline delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                data-bs-toggle="tooltip" 
                                                                title="Delete unused tag">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-muted small">
                                                        <i class="bi bi-shield-check"></i> Protected
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($tags->hasPages())
                            <div class="d-flex justify-content-center p-3 border-top">
                                {{ $tags->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-tags" style="font-size: 4rem; color: #6c757d;"></i>
                            </div>
                            <h5 class="text-muted mb-3">No tags found</h5>
                            <p class="text-muted">Tags will appear here as they are created through posts.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Delete Modal -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-trash"></i> Delete Unused Tags
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete all selected unused tags? This action cannot be undone.</p>
                <div id="selectedTagsList"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmBulkDelete">
                    <i class="bi bi-trash"></i> Delete Selected
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .tag-row {
        transition: background-color 0.2s ease-in-out;
    }
    
    .tag-row.highlight {
        background-color: #fff3cd !important;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        color: #495057;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Search functionality
    const searchInput = document.getElementById('tagSearch');
    const tableRows = document.querySelectorAll('.tag-row');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            tableRows.forEach(row => {
                const tagName = row.dataset.tagName;
                if (tagName.includes(searchTerm)) {
                    row.style.display = '';
                    if (searchTerm && searchTerm.length > 0) {
                        row.classList.add('highlight');
                    } else {
                        row.classList.remove('highlight');
                    }
                } else {
                    row.style.display = 'none';
                    row.classList.remove('highlight');
                }
            });
        });
    }

    // Select all functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const tagCheckboxes = document.querySelectorAll('.tag-checkbox');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            tagCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkDeleteButton();
        });
    }

    // Individual checkbox change
    tagCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkDeleteButton);
    });

    function updateBulkDeleteButton() {
        const checkedBoxes = document.querySelectorAll('.tag-checkbox:checked');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        
        if (bulkDeleteBtn) {
            if (checkedBoxes.length > 0) {
                bulkDeleteBtn.textContent = `Delete ${checkedBoxes.length} Tags`;
                bulkDeleteBtn.disabled = false;
            } else {
                bulkDeleteBtn.innerHTML = '<i class="bi bi-trash"></i> Clean Unused';
                bulkDeleteBtn.disabled = false;
            }
        }
    }

    // Delete form confirmation
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to delete this tag? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // Bulk delete functionality
    const confirmBulkDelete = document.getElementById('confirmBulkDelete');
    if (confirmBulkDelete) {
        confirmBulkDelete.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.tag-checkbox:checked');
            const tagIds = Array.from(checkedBoxes).map(cb => cb.value);
            
            if (tagIds.length > 0) {
                // Create a form to submit the bulk delete
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("admin.tags.bulk-delete") }}';
                
                // Add CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);
                
                // Add method override
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                form.appendChild(methodField);
                
                // Add tag IDs
                tagIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'tag_ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Update selected tags list in modal
    const bulkDeleteModal = document.getElementById('bulkDeleteModal');
    if (bulkDeleteModal) {
        bulkDeleteModal.addEventListener('show.bs.modal', function() {
            const checkedBoxes = document.querySelectorAll('.tag-checkbox:checked');
            const selectedTagsList = document.getElementById('selectedTagsList');
            
            if (checkedBoxes.length > 0) {
                let listHtml = '<strong>Selected tags:</strong><ul class="mt-2">';
                checkedBoxes.forEach(checkbox => {
                    const row = checkbox.closest('tr');
                    const tagName = row.querySelector('strong').textContent;
                    listHtml += `<li>${tagName}</li>`;
                });
                listHtml += '</ul>';
                selectedTagsList.innerHTML = listHtml;
            } else {
                selectedTagsList.innerHTML = '<em>No tags selected</em>';
            }
        });
    }
});
</script>
@endpush