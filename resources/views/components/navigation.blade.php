<header class="blog-header py-3">
    <div class="container">
        <div class="row flex-nowrap justify-content-between align-items-center">
            <div class="col-4 pt-1">
                <a class="link-secondary" href="#" aria-label="Search">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
                        stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        class="mx-3" role="img" viewBox="0 0 24 24">
                        <title>Search</title>
                        <circle cx="10.5" cy="10.5" r="7.5"></circle>
                        <path d="m21 21-5.2-5.2"></path>
                    </svg>
                </a>
            </div>
            <div class="col-4 text-center">
                <a class="blog-header-logo text-dark text-decoration-none" href="{{ route('home') }}">
                    <img src="https://valkey.io/img/valkey-horizontal.svg" alt="Valkey Blog" class="img-fluid"
                        style="height: 40px; max-width: 200px;">
                </a>
            </div>
            <div class="col-4 d-flex justify-content-end align-items-center">
                @auth
                    <a class="btn btn-sm btn-outline-secondary me-2" href="{{ route('admin.posts.index') }}">Admin</a>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Logout</button>
                    </form>
                @else
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('login') }}">Sign in</a>
                @endauth
            </div>
        </div>
    </div>
</header>

<div class="nav-scroller py-1 mb-2 mx-4">
    <nav class="nav d-flex justify-content-between">
        <a class="p-2 link-secondary" href="{{ route('home') }}">Home</a>
        <a class="p-2 link-secondary" href="#">Technology</a>
        <a class="p-2 link-secondary" href="#">Performance</a>
        <a class="p-2 link-secondary" href="#">Open Source</a>
        <a class="p-2 link-secondary" href="#">Community</a>
        <a class="p-2 link-secondary" href="#">Documentation</a>
        <a class="p-2 link-secondary" href="#">Tutorials</a>
        <a class="p-2 link-secondary" href="#">News</a>
        <a class="p-2 link-secondary" href="#">About</a>
    </nav>
</div>

<!-- Mobile Navigation Toggle (Bootstrap Navbar for smaller screens) -->
<nav class="navbar navbar-expand-lg navbar-light bg-light d-lg-none">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <img src="https://valkey.io/img/valkey-horizontal.svg" alt="Valkey Blog"
                style="height: 30px; max-width: 150px;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Technology</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Performance</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Redis</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Open Source</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Community</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.posts.index') }}">Admin</a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link nav-link border-0 p-0">Logout</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Sign in</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
