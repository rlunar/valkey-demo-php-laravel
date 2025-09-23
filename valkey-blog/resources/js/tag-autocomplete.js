/**
 * Tag Autocomplete and Management JavaScript
 * Provides autocomplete functionality for tag inputs in post forms
 */

class TagAutocomplete {
    constructor(inputSelector, options = {}) {
        this.input = document.querySelector(inputSelector);
        if (!this.input) return;

        this.options = {
            searchUrl: '/tags/search',
            createUrl: '/tags',
            minLength: 2,
            maxResults: 10,
            delay: 300,
            ...options
        };

        this.selectedTags = new Set();
        this.searchTimeout = null;
        this.isOpen = false;

        this.init();
    }

    init() {
        this.createElements();
        this.bindEvents();
        this.loadExistingTags();
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

        // Wrap the original input
        this.input.parentNode.insertBefore(this.container, this.input);
        this.container.appendChild(this.tagsDisplay);
        this.container.appendChild(this.input);
        this.container.appendChild(this.dropdown);

        // Style the input
        this.input.setAttribute('placeholder', 'Type to search or add tags...');
        this.input.setAttribute('autocomplete', 'off');
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

    loadExistingTags() {
        // Load existing tags from hidden inputs or data attributes
        const existingTagsInput = document.querySelector('input[name="existing_tags"]');
        if (existingTagsInput && existingTagsInput.value) {
            try {
                const tags = JSON.parse(existingTagsInput.value);
                tags.forEach(tag => this.addTag(tag.name, tag.id));
            } catch (e) {
                console.warn('Could not parse existing tags:', e);
            }
        }
    }

    handleInput(e) {
        const value = e.target.value.trim();
        
        if (value.length >= this.options.minLength) {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.searchTags(value);
            }, this.options.delay);
        } else {
            this.hideDropdown();
        }
    }

    handleKeydown(e) {
        switch (e.key) {
            case 'Enter':
                e.preventDefault();
                const value = this.input.value.trim();
                if (value) {
                    this.createTag(value);
                }
                break;
            case 'Escape':
                this.hideDropdown();
                break;
            case 'ArrowDown':
                e.preventDefault();
                this.navigateDropdown('down');
                break;
            case 'ArrowUp':
                e.preventDefault();
                this.navigateDropdown('up');
                break;
            case 'Backspace':
                if (this.input.value === '' && this.selectedTags.size > 0) {
                    const lastTag = Array.from(this.selectedTags).pop();
                    this.removeTag(lastTag);
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
        try {
            const response = await fetch(`${this.options.searchUrl}?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });

            if (response.ok) {
                const tags = await response.json();
                this.showDropdown(tags, query);
            }
        } catch (error) {
            console.error('Error searching tags:', error);
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
        try {
            const response = await fetch(this.options.createUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ name: name.trim() })
            });

            if (response.ok) {
                const result = await response.json();
                this.addTag(result.tag.name, result.tag.id);
                this.input.value = '';
                this.hideDropdown();
                this.input.focus();
            } else {
                const error = await response.json();
                console.error('Error creating tag:', error);
                // Still add the tag locally for form submission
                this.addTag(name.trim());
                this.input.value = '';
                this.hideDropdown();
                this.input.focus();
            }
        } catch (error) {
            console.error('Error creating tag:', error);
            // Fallback: add tag locally
            this.addTag(name.trim());
            this.input.value = '';
            this.hideDropdown();
            this.input.focus();
        }
    }

    addTag(name, id = null) {
        if (this.selectedTags.has(name)) return;

        this.selectedTags.add(name);

        // Create tag badge
        const tagBadge = document.createElement('span');
        tagBadge.className = 'badge bg-primary d-flex align-items-center gap-1';
        tagBadge.innerHTML = `
            <i class="bi bi-tag"></i>
            <span>${this.escapeHtml(name)}</span>
            <button type="button" class="btn-close btn-close-white" style="font-size: 0.7em;"></button>
        `;

        // Add remove functionality
        const removeBtn = tagBadge.querySelector('.btn-close');
        removeBtn.addEventListener('click', () => this.removeTag(name));

        this.tagsDisplay.appendChild(tagBadge);

        // Create hidden input for form submission
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'tags[]';
        hiddenInput.value = name;
        hiddenInput.dataset.tagName = name;
        this.container.appendChild(hiddenInput);

        this.updateTagsInput();
    }

    removeTag(name) {
        if (!this.selectedTags.has(name)) return;

        this.selectedTags.delete(name);

        // Remove badge
        const badges = this.tagsDisplay.querySelectorAll('.badge');
        badges.forEach(badge => {
            if (badge.textContent.trim().includes(name)) {
                badge.remove();
            }
        });

        // Remove hidden input
        const hiddenInput = this.container.querySelector(`input[data-tag-name="${name}"]`);
        if (hiddenInput) {
            hiddenInput.remove();
        }

        this.updateTagsInput();
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
                this.addTag(tag);
            } else {
                this.addTag(tag.name, tag.id);
            }
        });
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