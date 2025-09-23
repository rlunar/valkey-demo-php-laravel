@props(['categories', 'popularTags', 'selectedCategory' => null, 'selectedTag' => null])

<div class="filter-sidebar bg-light p-4 rounded shadow-sm">
    <h5 class="mb-3">
        <i class="fas fa-filter me-2"></i>Filter Posts
    </h5>
    
    <!-- Active Filters -->
    @if($selectedCategory || $selectedTag)
        <div class="active-filters mb-3">
            <h6 class="text-muted mb-2">Active Filters:</h6>
            <div class="d-flex flex-wrap gap-2">
                @if($selectedCategory)
                    <div class="d-flex align-items-center bg-primary text-white px-2 py-1 rounded">
                        <span class="me-2">{{ $selectedCategory->name }}</span>
                        <a href="{{ route('home') }}" class="text-white text-decoration-none" title="Remove category filter">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                @endif
                
                @if($selectedTag)
                    <div class="d-flex align-items-center bg-secondary text-white px-2 py-1 rounded">
                        <span class="me-2">{{ $selectedTag->name }}</span>
                        <a href="{{ route('home') }}" class="text-white text-decoration-none" title="Remove tag filter">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                @endif
            </div>
            
            <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm mt-2">
                <i class="fas fa-times me-1"></i>Clear All Filters
            </a>
        </div>
    @endif
    
    <!-- Categories Filter -->
    <div class="filter-section mb-4">
        <h6 class="mb-3">
            <i class="fas fa-folder me-2"></i>Categories
        </h6>
        
        @if($categories->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($categories as $category)
                    <a href="{{ route('categories.show', $category->slug) }}" 
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0 px-0 {{ $selectedCategory && $selectedCategory->id === $category->id ? 'active' : '' }}">
                        <span>{{ $category->name }}</span>
                        <span class="badge bg-secondary rounded-pill">{{ $category->posts_count ?? 0 }}</span>
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-muted fst-italic mb-0">No categories available</p>
        @endif
    </div>
    
    <!-- Popular Tags -->
    <div class="filter-section">
        <h6 class="mb-3">
            <i class="fas fa-tags me-2"></i>Popular Tags
        </h6>
        
        @if($popularTags->count() > 0)
            <div class="tag-cloud">
                @foreach($popularTags as $tag)
                    @php
                        $postCount = $tag->posts_count ?? 0;
                        $fontSize = $postCount > 10 ? 'fs-5' : ($postCount > 5 ? 'fs-6' : 'small');
                        $isSelected = $selectedTag && $selectedTag->id === $tag->id;
                    @endphp
                    
                    <a href="{{ route('tags.show', $tag->slug) }}" 
                       class="badge bg-{{ $isSelected ? 'primary' : 'secondary' }} text-white text-decoration-none me-1 mb-2 {{ $fontSize }}"
                       title="{{ $postCount }} posts">
                        {{ $tag->name }}
                    </a>
                @endforeach
            </div>
            
            <div class="mt-3">
                <a href="{{ route('tags.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-tags me-1"></i>View All Tags
                </a>
            </div>
        @else
            <p class="text-muted fst-italic mb-0">No tags available</p>
        @endif
    </div>
</div>