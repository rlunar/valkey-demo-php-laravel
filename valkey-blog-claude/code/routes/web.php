<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;

Route::get('/', [BlogController::class, 'index'])->name('blog.index');
Route::get('/post/{post}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/category/{category}', [BlogController::class, 'category'])->name('blog.category');
Route::post('/post/{post}/comment', [BlogController::class, 'storeComment'])->name('blog.comment');
