<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', config('app.name', 'Laravel'))</title>
    <meta name="description" content="@yield('description', 'A Laravel blog application')">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
    
    <style>
        body {
            font-family: 'Source Sans Pro', sans-serif;
        }
        .blog-title, .blog-post-title {
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <x-navigation />
    
    <!-- Main Content -->
    <main class="container">
        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <strong>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <strong>Error!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="blog-footer mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <p class="mb-1">&copy; {{ date('Y') }} Valkey Developer Blog. Built with ❤️ using Laravel and Bootstrap.</p>
                    <p class="mb-0 text-muted">
                        <small>Sharing insights, tutorials, and updates from the Valkey development community.</small>
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="#" class="text-muted text-decoration-none me-3">Privacy</a>
                    <a href="#" class="text-muted text-decoration-none me-3">Terms</a>
                    <a href="#" class="text-muted text-decoration-none">Contact</a>
                </div>
            </div>
            <div class="row mt-3 pt-3 border-top">
                <div class="col-12 text-center">
                    <p class="mb-0">
                        <a href="#" class="text-muted">Back to top</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Back to top functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Back to top functionality
            const backToTopLinks = document.querySelectorAll('a[href="#"]');
            backToTopLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            });
            
            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
