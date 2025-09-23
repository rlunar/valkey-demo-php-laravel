/**
 * Tag Autocomplete and Management JavaScript
 * Provides autocomplete functionality for tag inputs in post forms
 */

class TagAutocomplete {
    constructor(inputSelector, options = {}) {
        this.input = document.querySelector(inputSelector);
        if (!this.input) return;

        this.options = {
            searchUrl: '/api/tags/search',
            createUrl: '/api/tags',
            minLength: 2,
            maxResults: 10,
            delay: 300,
            maxTags: 20,
            allowDuplicates: false,
            showNotifications: true,
            ...options
        };

        this.selectedTags = new Set();
        this.searchTimeout = null;
        this.isOpen = false;
        this.isLoading = false;
        this.cache = new Map();

        this.init();
    }

    init() {
        this.createElements();
        this.bindEvents();
        this.loadExistingTags();
        this.createNotificationContainer();
    }

    createElements() {
        // Create container for the tag input system
        this.container = document.createElement('div');
        this.container.className = 'tag-input-container position-relative';

        // Create selected tags display
        this.tagsDisplay = document.createElement('div');
        this.tagsDisplay.className = 'selected-tags-display d-flex flex-wrap gap-2 mb-2';

        // Create dropdown for suggestions
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'tag-dropdown position-absolute w-100 bg-white border rounded shadow-sm';
        this.dropdown.style.display = 'none';
        this.dropdown.style.zIndex = '1050';
        this.dropdown.style.maxHeight = '200px';
        this.dropdown.style.overflowY = 'auto';

        // Create loading indicator
        this.loadingIndicator = document.createElement('div');
        this.loadingIndicator.className = 'tag-loading position-absolute end-0 top-50 translate-middle-y me-2';
        this.loadingIndicator.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
        this.loadingIndicator.style.display = 'none';

        // Create tag counter
        this.tagCounter = document.createElement('div');
        this.tagCounter.className = 'tag-counter form-text text-end mt-1';
        this.tagCounter.style.fontSize = '0.875rem';

        // Wrap the original input
        this.input.parentNode.insertBefore(this.container, this.input);
        this.container.appendChild(this.tagsDisplay);
        this.container.appendChild(this.input);
        this.container.appendChild(this.loadingIndicator);
        this.container.appendChild(this.dropdown);
        this.container.appendChild(this.tagCounter);

        // Style the input
        this.input.setAttribute('placeholder', 'Type to search or add tags...');
        this.input.setAttribute('autocomplete', 'off');
        this.input.setAttribute('role', 'combobox');
        this.input.setAttribute('aria-expanded', 'false');
        this.input.setAttribute('aria-autocomplete', 'list');

        this.updateTagCounter();
    }

    bindEvents() {
        // Input events
        this.input.addEventListener('input', (e) => this.handleInput(e));
        this.input.addEventListener('keydown', (e) => this.handleKeydown(e));
        this.input.addEventListener('focus', () => this.handleFocus());
        this.input.addEventListener('blur', (e) => this.handleBlur(e));

        // Document click to close dropdown
        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                this.hideDropdown();
            }
        });
    }

    createNotificationContainer() {
        // Create notification container if it doesn't exist
        if (!document.getElementById('tag-notifications')) {
            this.notificationContainer = document.createElement('div');
            this.notificationContainer.id = 'tag-notifications';
            this.notificationContainer.className = 'position-fixed top-0 end-0 p-3';
            this.notificationContainer.style.zIndex = '1060';
            document.body.appendChild(this.notificationContainer);
        } else {
            this.notificationContainer = document.getElementById('tag-notifications');
        }
    }

    loadExistingTags() {
        // Load existing tags from hidden inputs or data attributes
        const existingTagsInput = document.querySelector('input[name="existing_tags"]');
        if (existingTagsInput && existingTagsInput.value) {
            try {
                const tags = JSON.parse(existingTagsInput.value);
                tags.forEach(tag => this.addTag(tag.name, tag.id, false));
            } catch (e) {
                console.warn('Could not parse existing tags:', e);
                this.showNotification('Warning: Could not load existing tags', 'warning');
            }
        }
    }

    handleInput(e) {
        const value = e.target.value.trim();
        
        // Clear any error states
        this.clearErrorState();
        
        if (value.length >= this.options.minLength) {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.searchTags(value);
            }, this.options.delay);
        } else {
            this.hideDropdown();
        }

        // Validate input length
        if (value.length > 50) {
            this.setErrorState('Tag name is too long (maximum 50 characters)');
        }
    }

    handleKeydown(e) {
        switch (e.key) {
            case 'Enter':
                e.preventDefault();
                const activeItem = this.dropdown.querySelector('.tag-dropdown-item.active');
                if (activeItem && this.isOpen) {
                    activeItem.click();
                } else {
                    const value = this.input.value.trim();
                    if (value) {
                        this.createTag(value);
                    }
                }
                break;
            case 'Escape':
                this.hideDropdown();
                this.input.blur();
                break;
            case 'ArrowDown':
                e.preventDefault();
                if (this.isOpen) {
                    this.navigateDropdown('down');
                } else if (this.input.value.trim().length >= this.options.minLength) {
                    this.searchTags(this.input.value.trim());
                }
                break;
            case 'ArrowUp':
                e.preventDefault();
                if (this.isOpen) {
                    this.navigateDropdown('up');
                }
                break;
            case 'Backspace':
                if (this.input.value === '' && this.selectedTags.size > 0) {
                    const lastTag = Array.from(this.selectedTags).pop();
                    this.removeTag(lastTag);
                }
                break;
            case 'Tab':
                if (this.isOpen) {
                    const activeItem = this.dropdown.querySelector('.tag-dropdown-item.active');
                    if (activeItem) {
                        e.preventDefault();
                        activeItem.click();
                    }
                }
                break;
        }
    }

    handleFocus() {
        if (this.input.value.trim().length >= this.options.minLength) {
            this.searchTags(this.input.value.trim());
        }
    }

    handleBlur(e) {
        // Delay hiding to allow clicking on dropdown items
        setTimeout(() => {
            if (!this.container.contains(document.activeElement)) {
                this.hideDropdown();
            }
        }, 150);
    }

    async searchTags(query) {
        // Check cache first
        if (this.cache.has(query)) {
            this.showDropdown(this.cache.get(query), query);
            return;
        }

        this.setLoadingState(true);
        
        try {
            const response = await fetch(`${this.options.searchUrl}?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });

            if (response.ok) {
                const tags = await response.json();
                this.cache.set(query, tags);
                this.showDropdown(tags, query);
            } else {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
        } catch (error) {
            console.error('Error searching tags:', error);
            this.showNotification('Failed to search tags. Please try again.', 'error');
            this.hideDropdown();
        } finally {
            this.setLoadingState(false);
        }
    }

    showDropdown(tags, query) {
        this.dropdown.innerHTML = '';

        // Filter out already selected tags
        const availableTags = tags.filter(tag => !this.selectedTags.has(tag.name));

        if (availableTags.length > 0) {
            availableTags.forEach((tag, index) => {
                const item = this.createDropdownItem(tag, index === 0);
                this.dropdown.appendChild(item);
            });
        }

        // Add "Create new tag" option if query doesn't match existing tags
        const exactMatch = tags.some(tag => tag.name.toLowerCase() === query.toLowerCase());
        if (!exactMatch && !this.selectedTags.has(query)) {
            const createItem = this.createNewTagItem(query);
            this.dropdown.appendChild(createItem);
        }

        if (this.dropdown.children.length > 0) {
            this.dropdown.style.display = 'block';
            this.isOpen = true;
        } else {
            this.hideDropdown();
        }
    }

    createDropdownItem(tag, isActive = false) {
        const item = document.createElement('div');
        item.className = `tag-dropdown-item p-2 cursor-pointer ${isActive ? 'active' : ''}`;
        item.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <span><i class="bi bi-tag me-1"></i> ${this.escapeHtml(tag.name)}</span>
                <small class="text-muted">Select</small>
            </div>
        `;

        item.addEventListener('click', () => {
            this.addTag(tag.name, tag.id);
            this.input.value = '';
            this.hideDropdown();
            this.input.focus();
        });

        item.addEventListener('mouseenter', () => {
            this.setActiveItem(item);
        });

        return item;
    }

    createNewTagItem(query) {
        const item = document.createElement('div');
        item.className = 'tag-dropdown-item p-2 cursor-pointer border-top';
        item.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <span><i class="bi bi-plus-circle me-1"></i> Create "${this.escapeHtml(query)}"</span>
                <small class="text-muted">New</small>
            </div>
        `;

        item.addEventListener('click', () => {
            this.createTag(query);
        });

        item.addEventListener('mouseenter', () => {
            this.setActiveItem(item);
        });

        return item;
    }

    setActiveItem(item) {
        // Remove active class from all items
        this.dropdown.querySelectorAll('.tag-dropdown-item').forEach(el => {
            el.classList.remove('active');
        });
        // Add active class to current item
        item.classList.add('active');
    }

    navigateDropdown(direction) {
        const items = this.dropdown.querySelectorAll('.tag-dropdown-item');
        if (items.length === 0) return;

        const activeItem = this.dropdown.querySelector('.tag-dropdown-item.active');
        let newIndex = 0;

        if (activeItem) {
            const currentIndex = Array.from(items).indexOf(activeItem);
            if (direction === 'down') {
                newIndex = (currentIndex + 1) % items.length;
            } else {
                newIndex = currentIndex > 0 ? currentIndex - 1 : items.length - 1;
            }
        }

        this.setActiveItem(items[newIndex]);
    }

    async createTag(name) {
        const trimmedName = name.trim();
        
        // Validate tag name
        if (!this.validateTagName(trimmedName)) {
            return;
        }

        // Check if tag already exists
        if (this.selectedTags.has(trimmedName)) {
            this.showNotification(`Tag "${trimmedName}" is already added`, 'warning');
            this.input.value = '';
            this.input.focus();
            return;
        }

        // Check tag limit
        if (this.selectedTags.size >= this.options.maxTags) {
            this.showNotification(`Maximum ${this.options.maxTags} tags allowed`, 'warning');
            return;
        }

        this.setLoadingState(true);
        
        try {
            const response = await fetch(this.options.createUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ name: trimmedName })
            });

            if (response.ok) {
                const result = await response.json();
                this.addTag(result.tag.name, result.tag.id, true);
                this.showNotification(`Tag "${result.tag.name}" created successfully`, 'success');
                this.clearCache(); // Clear cache to include new tag in future searches
            } else {
                const errorData = await response.json();
                if (response.status === 422 && errorData.errors && errorData.errors.name) {
                    // Tag already exists, try to add it anyway
                    this.addTag(trimmedName, null, true);
                    this.showNotification(`Tag "${trimmedName}" added (already exists)`, 'info');
                } else {
                    throw new Error(errorData.message || 'Failed to create tag');
                }
            }
        } catch (error) {
            console.error('Error creating tag:', error);
            // Fallback: add tag locally for form submission
            this.addTag(trimmedName, null, true);
            this.showNotification(`Tag "${trimmedName}" added locally (will be created on save)`, 'info');
        } finally {
            this.setLoadingState(false);
            this.input.value = '';
            this.hideDropdown();
            this.input.focus();
        }
    }

    addTag(name, id = null, animate = true) {
        if (this.selectedTags.has(name)) return;

        // Check tag limit
        if (this.selectedTags.size >= this.options.maxTags) {
            this.showNotification(`Maximum ${this.options.maxTags} tags allowed`, 'warning');
            return;
        }

        this.selectedTags.add(name);

        // Create tag badge
        const tagBadge = document.createElement('span');
        tagBadge.className = 'badge bg-primary d-flex align-items-center gap-1 tag-badge';
        tagBadge.dataset.tagName = name;
        tagBadge.innerHTML = `
            <i class="bi bi-tag"></i>
            <span>${this.escapeHtml(name)}</span>
            <button type="button" class="btn-close btn-close-white" style="font-size: 0.7em;" aria-label="Remove tag ${this.escapeHtml(name)}"></button>
        `;

        // Add animation class if requested
        if (animate) {
            tagBadge.style.opacity = '0';
            tagBadge.style.transform = 'scale(0.8)';
        }

        // Add remove functionality
        const removeBtn = tagBadge.querySelector('.btn-close');
        removeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            this.removeTag(name);
        });

        // Add keyboard support for remove button
        removeBtn.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.removeTag(name);
            }
        });

        this.tagsDisplay.appendChild(tagBadge);

        // Animate in if requested
        if (animate) {
            requestAnimationFrame(() => {
                tagBadge.style.transition = 'opacity 0.2s ease-out, transform 0.2s ease-out';
                tagBadge.style.opacity = '1';
                tagBadge.style.transform = 'scale(1)';
            });
        }

        // Create hidden input for form submission
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'tags[]';
        hiddenInput.value = name;
        hiddenInput.dataset.tagName = name;
        this.container.appendChild(hiddenInput);

        this.updateTagsInput();
        this.updateTagCounter();
    }

    removeTag(name) {
        if (!this.selectedTags.has(name)) return;

        this.selectedTags.delete(name);

        // Find and animate out the badge
        const badge = this.tagsDisplay.querySelector(`[data-tag-name="${name}"]`);
        if (badge) {
            badge.style.transition = 'opacity 0.2s ease-out, transform 0.2s ease-out';
            badge.style.opacity = '0';
            badge.style.transform = 'scale(0.8)';
            
            setTimeout(() => {
                badge.remove();
            }, 200);
        }

        // Remove hidden input
        const hiddenInput = this.container.querySelector(`input[data-tag-name="${name}"]`);
        if (hiddenInput) {
            hiddenInput.remove();
        }

        this.updateTagsInput();
        this.updateTagCounter();
        
        // Show notification for removal
        if (this.options.showNotifications) {
            this.showNotification(`Tag "${name}" removed`, 'info');
        }
    }

    updateTagsInput() {
        // Update a summary input if it exists
        const summaryInput = document.querySelector('input[name="tags_summary"]');
        if (summaryInput) {
            summaryInput.value = Array.from(this.selectedTags).join(', ');
        }
    }

    hideDropdown() {
        this.dropdown.style.display = 'none';
        this.isOpen = false;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Utility methods
    validateTagName(name) {
        if (!name || name.length === 0) {
            this.setErrorState('Tag name cannot be empty');
            return false;
        }

        if (name.length > 50) {
            this.setErrorState('Tag name is too long (maximum 50 characters)');
            return false;
        }

        if (!/^[a-zA-Z0-9\s\-_]+$/.test(name)) {
            this.setErrorState('Tag name can only contain letters, numbers, spaces, hyphens, and underscores');
            return false;
        }

        return true;
    }

    setLoadingState(loading) {
        this.isLoading = loading;
        this.loadingIndicator.style.display = loading ? 'block' : 'none';
        this.input.disabled = loading;
        
        if (loading) {
            this.input.classList.add('loading');
        } else {
            this.input.classList.remove('loading');
        }
    }

    setErrorState(message) {
        this.container.classList.add('error');
        this.input.classList.add('is-invalid');
        
        // Remove existing error message
        const existingError = this.container.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }

        // Add new error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message invalid-feedback d-block';
        errorDiv.textContent = message;
        this.container.appendChild(errorDiv);

        // Auto-clear error after 3 seconds
        setTimeout(() => {
            this.clearErrorState();
        }, 3000);
    }

    clearErrorState() {
        this.container.classList.remove('error');
        this.input.classList.remove('is-invalid');
        
        const errorMessage = this.container.querySelector('.error-message');
        if (errorMessage) {
            errorMessage.remove();
        }
    }

    updateTagCounter() {
        const count = this.selectedTags.size;
        const max = this.options.maxTags;
        this.tagCounter.textContent = `${count}/${max} tags`;
        
        if (count >= max) {
            this.tagCounter.classList.add('text-warning');
        } else {
            this.tagCounter.classList.remove('text-warning');
        }
    }

    showNotification(message, type = 'info') {
        if (!this.options.showNotifications) return;

        const notification = document.createElement('div');
        notification.className = `toast align-items-center text-white bg-${this.getBootstrapColorClass(type)} border-0`;
        notification.setAttribute('role', 'alert');
        notification.setAttribute('aria-live', 'assertive');
        notification.setAttribute('aria-atomic', 'true');
        
        notification.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi ${this.getIconClass(type)} me-2"></i>
                    ${this.escapeHtml(message)}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        this.notificationContainer.appendChild(notification);

        // Initialize Bootstrap toast
        const toast = new bootstrap.Toast(notification, {
            autohide: true,
            delay: type === 'error' ? 5000 : 3000
        });
        
        toast.show();

        // Remove from DOM after hiding
        notification.addEventListener('hidden.bs.toast', () => {
            notification.remove();
        });
    }

    getBootstrapColorClass(type) {
        const colorMap = {
            'success': 'success',
            'error': 'danger',
            'warning': 'warning',
            'info': 'info'
        };
        return colorMap[type] || 'info';
    }

    getIconClass(type) {
        const iconMap = {
            'success': 'bi-check-circle',
            'error': 'bi-exclamation-triangle',
            'warning': 'bi-exclamation-circle',
            'info': 'bi-info-circle'
        };
        return iconMap[type] || 'bi-info-circle';
    }

    clearCache() {
        this.cache.clear();
    }

    // Public methods
    getTags() {
        return Array.from(this.selectedTags);
    }

    clearTags() {
        const tags = Array.from(this.selectedTags);
        tags.forEach(tag => this.removeTag(tag));
    }

    setTags(tags) {
        this.clearTags();
        tags.forEach(tag => {
            if (typeof tag === 'string') {
                this.addTag(tag, null, false);
            } else {
                this.addTag(tag.name, tag.id, false);
            }
        });
    }

    // Method to refresh the autocomplete (useful after external tag changes)
    refresh() {
        this.clearCache();
        this.updateTagCounter();
    }

    // Method to disable/enable the autocomplete
    setEnabled(enabled) {
        this.input.disabled = !enabled;
        if (enabled) {
            this.container.classList.remove('disabled');
        } else {
            this.container.classList.add('disabled');
            this.hideDropdown();
        }
    }
}

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tag autocomplete for any input with class 'tag-input'
    const tagInputs = document.querySelectorAll('.tag-input');
    tagInputs.forEach(input => {
        new TagAutocomplete(`#${input.id}` || '.tag-input');
    });
});

// Export for manual initialization
window.TagAutocomplete = TagAutocomplete;