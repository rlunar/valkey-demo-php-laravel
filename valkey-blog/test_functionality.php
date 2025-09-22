<?php

// Test script to verify blog functionality
require_once 'vendor/autoload.php';

use App\Models\Post;
use App\Models\User;

// Test database connection
try {
    echo "Testing database connection...\n";
    $postsCount = Post::count();
    $usersCount = User::count();
    echo "✓ Database connected. Posts: $postsCount, Users: $usersCount\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test Post model functionality
try {
    echo "\nTesting Post model...\n";
    
    // Test published scope
    $publishedPosts = Post::published()->count();
    echo "✓ Published posts scope works. Published posts: $publishedPosts\n";
    
    // Test relationships
    $post = Post::with('user')->first();
    if ($post && $post->user) {
        echo "✓ Post-User relationship works. Author: " . $post->user->name . "\n";
    } else {
        echo "✗ Post-User relationship failed\n";
    }
    
    // Test slug generation
    $testPost = new Post();
    $testPost->title = "Test Post Title";
    $testPost->content = "Test content";
    $testPost->status = "draft";
    $testPost->user_id = 1;
    
    // Test slug auto-generation
    if (method_exists($testPost, 'setTitleAttribute')) {
        echo "✓ Slug generation method exists\n";
    }
    
    // Test excerpt generation
    if ($post) {
        $excerpt = $post->generateExcerpt();
        echo "✓ Excerpt generation works. Length: " . strlen($excerpt) . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Post model test failed: " . $e->getMessage() . "\n";
}

// Test User model functionality
try {
    echo "\nTesting User model...\n";
    
    $user = User::with('posts')->first();
    if ($user) {
        $userPostsCount = $user->posts->count();
        echo "✓ User-Posts relationship works. User posts: $userPostsCount\n";
    } else {
        echo "✗ User-Posts relationship failed\n";
    }
    
} catch (Exception $e) {
    echo "✗ User model test failed: " . $e->getMessage() . "\n";
}

// Test validation rules
try {
    echo "\nTesting validation...\n";
    
    // Test required fields validation
    $validator = validator([], [
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'status' => 'required|in:draft,published',
    ]);
    
    if ($validator->fails()) {
        echo "✓ Validation rules work correctly\n";
    } else {
        echo "✗ Validation rules not working\n";
    }
    
} catch (Exception $e) {
    echo "✗ Validation test failed: " . $e->getMessage() . "\n";
}

echo "\nFunctionality tests completed.\n";