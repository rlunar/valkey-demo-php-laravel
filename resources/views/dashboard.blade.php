@extends('layouts.app')

@section('title', 'Dashboard - ' . config('app.name'))

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Dashboard</h1>
            <a href="{{ route('admin.posts.index') }}" class="btn btn-primary">Manage Posts</a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Welcome back!</h5>
                <p class="card-text">You're logged in and ready to manage your blog.</p>
                
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6 class="card-title">Quick Actions</h6>
                                <a href="{{ route('admin.posts.create') }}" class="btn btn-light btn-sm">Create New Post</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-title">Blog Management</h6>
                                <a href="{{ route('admin.posts.index') }}" class="btn btn-light btn-sm">View All Posts</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6 class="card-title">Public Blog</h6>
                                <a href="{{ route('home') }}" class="btn btn-light btn-sm">View Blog</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
