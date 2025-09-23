@props(['category', 'size' => 'sm'])

@if($category)
    <a href="{{ route('home', ['category' => $category->slug]) }}" 
       class="badge bg-primary text-decoration-none badge-{{ $size }}"
       @if($category->color) style="background-color: {{ $category->color }} !important;" @endif
       title="View posts in {{ $category->name }}">
        {{ $category->name }}
    </a>
@endif