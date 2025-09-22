<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;

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
| Admin Routes
|--------------------------------------------------------------------------
*/

// Admin routes grouped with authentication middleware
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Post management resource routes
    Route::resource('posts', PostController::class)->except(['show']);
});
