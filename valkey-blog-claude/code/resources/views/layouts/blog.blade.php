<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Laravel Blog')</title>
    <meta name="description" content="@yield('description', 'A modern Laravel blog')">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        .blog-header {
            border-bottom: 1px solid #e5e5e5;
        }

        .blog-title {
            margin-bottom: 0;
            font-size: 2.25rem;
            font-weight: 400;
        }

        .blog-description {
            font-size: 1.1rem;
            color: #6c757d;
        }

        .card-blog {
            transition: transform 0.2s ease-in-out;
        }

        .card-blog:hover {
            transform: translateY(-2px);
        }

        .category-badge {
            font-size: 0.75rem;
        }

        .blog-footer {
            padding: 2.5rem 0;
            color: #6c757d;
            text-align: center;
            background-color: #f8f9fa;
            border-top: .05rem solid #e5e5e5;
            margin-top: 3rem;
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Header -->
    <header class="blog-header py-3">
        <div class="row flex-nowrap justify-content-between align-items-center">
            <div class="col-4 pt-1">
                <a class="link-secondary" href="#">Subscribe</a>
            </div>
            <div class="col-4 text-center">
                <a class="blog-header-logo text-dark text-decoration-none" href="{{ route('blog.index') }}">
                    <h1 class="blog-title">Laravel Blog</h1>
                </a>
            </div>
            <div class="col-4 d-flex justify-content-end align-items-center">
                <a class="link-secondary" href="#" aria-label="Search">
                    <i class="fas fa-search"></i>
                </a>
                <a class="btn btn-sm btn-outline-secondary ms-2" href="#">Sign up</a>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <div class="nav-scroller py-1 mb-2">
        <nav class="nav d-flex justify-content-between">
            <a class="p-2 link-secondary" href="{{ route('blog.index') }}">Home</a>
            @foreach($categories ?? [] as $category)
                <a class="p-2 link-secondary" href="{{ route('blog.category', $category) }}">{{ $category->name }}</a>
            @endforeach
        </nav>
    </div>

    <div class="container">
        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="blog-footer">
        <p>Laravel Blog built with <a href="https://getbootstrap.com/">Bootstrap</a> by <a href="#">@yourname</a>.</p>
        <p>
            <a href="#">Back to top</a>
        </p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
