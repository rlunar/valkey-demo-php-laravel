<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/*
|--------------------------------------------------------------------------
| Public Blog Routes
|--------------------------------------------------------------------------
*/

// Blog homepage - displays list of published posts
Route::get('/', [HomeController::class, 'index'])->name('home');

// Individual post view using slug for SEO-friendly URLs
Route::get('/post/{slug}', [HomeController::class, 'show'])->name('post.show');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

// Dashboard route (from Breeze)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile management routes (from Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin Routes - Protected with Authentication Middleware
|--------------------------------------------------------------------------
*/

// Admin routes grouped with authentication middleware
// Redirects unauthenticated users to login page
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Post management resource routes
    Route::resource('posts', PostController::class)->except(['show']);
});

// Include authentication routes
require __DIR__.'/auth.php';
