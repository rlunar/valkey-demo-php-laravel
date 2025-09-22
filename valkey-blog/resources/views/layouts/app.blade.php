<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('description', 'Valkey Developer Advocate Blog - Latest insights and updates')">
    <meta name="author" content="@yield('author', 'Valkey Team')">

    <title>@yield('title', 'Valkey Blog')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <!-- Custom CSS -->
    <style>
        .blog-header {
            border-bottom: 1px solid #e5e5e5;
        }
        .blog-title {
            margin-bottom: 0;
            font-size: 2rem;
            font-weight: 400;
        }
        .blog-description {
            font-size: 1.1rem;
            color: #6c757d;
        }
        .blog-footer {
            padding: 2.5rem 0;
            color: #6c757d;
            text-align: center;
            background-color: #f8f9fa;
            border-top: .05rem solid #e5e5e5;
        }
        .blog-post {
            margin-bottom: 4rem;
        }
        .blog-post-title {
            margin-bottom: .25rem;
            font-size: 2.5rem;
        }
        .blog-post-meta {
            margin-bottom: 1.25rem;
            color: #6c757d;
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    @include('components.navigation')

    <!-- Main Content -->
    <main class="container">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="blog-footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} Valkey Blog. Built with <a href="https://laravel.com">Laravel</a> and <a href="https://getbootstrap.com">Bootstrap</a>.</p>
            <p>
                <a href="#" onclick="window.scrollTo({top: 0, behavior: 'smooth'}); return false;">Back to top</a>
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    @stack('scripts')
</body>
</html>