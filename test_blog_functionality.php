<?php

/**
 * Comprehensive Blog Functionality Test Script
 * 
 * This script tests all CRUD operations, error handling, and user feedback
 * as required by task 10.2
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Application;

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Blog Functionality Test Suite ===\n\n";

// Test Results Tracking
$tests = [];
$passed = 0;
$failed = 0;

function test($description, $callback) {
    global $tests, $passed, $failed;
    
    echo "Testing: $description... ";
    
    try {
        $result = $callback();
        if ($result === true) {
            echo "✅ PASSED\n";
            $tests[] = ['description' => $description, 'status' => 'PASSED'];
            $passed++;
        } else {
            echo "❌ FAILED: $result\n";
            $tests[] = ['description' => $description, 'status' => 'FAILED', 'error' => $result];
            $failed++;
        }
    } catch (Exception $e) {
        echo "❌ FAILED: " . $e->getMessage() . "\n";
        $tests[] = ['description' => $description, 'status' => 'FAILED', 'error' => $e->getMessage()];
        $failed++;
    }
}

// Clean up any existing test data
echo "Cleaning up existing test data...\n";
Post::where('title', 'like', 'Test%')->delete();
User::where('email', 'like', 'test%')->delete();

echo "\n=== 1. Model Functionality Tests ===\n";

// Test 1: User Model Creation
test("User model creation and relationships", function() {
    $user = User::create([
        'name' => 'Test Author',
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
        'bio' => 'Test bio for author',
    ]);
    
    if (!$user->id) return "User creation failed";
    if ($user->name !== 'Test Author') return "User name not set correctly";
    if ($user->bio !== 'Test bio for author') return "User bio not set correctly";
    
    return true;
});

// Test 2: Post Model Creation with Auto Slug Generation
test("Post model creation with auto slug generation", function() {
    $user = User::where('email', 'test@example.com')->first();
    
    $post = Post::create([
        'title' => 'Test Post Title',
        'content' => 'This is test content for the post.',
        'status' => 'published',
        'published_at' => now(),
        'user_id' => $user->id,
    ]);
    
    if (!$post->id) return "Post creation failed";
    if ($post->slug !== 'test-post-title') return "Auto slug generation failed. Expected 'test-post-title', got '{$post->slug}'";
    if ($post->title !== 'Test Post Title') return "Post title not set correctly";
    
    return true;
});

// Test 3: Post Model Relationships
test("Post model relationships", function() {
    $post = Post::where('title', 'Test Post Title')->first();
    
    if (!$post->user) return "Post->user relationship failed";
    if ($post->user->name !== 'Test Author') return "Post author relationship incorrect";
    
    $user = User::where('email', 'test@example.com')->first();
    if ($user->posts->count() !== 1) return "User->posts relationship failed";
    
    return true;
});

// Test 4: Published Scope
test("Published scope functionality", function() {
    $user = User::where('email', 'test@example.com')->first();
    
    // Create a draft post
    $draftPost = Post::create([
        'title' => 'Test Draft Post',
        'content' => 'This is a draft post.',
        'status' => 'draft',
        'user_id' => $user->id,
    ]);
    
    $publishedCount = Post::published()->count();
    $totalCount = Post::count();
    
    if ($totalCount !== 2) return "Total post count incorrect. Expected 2, got $totalCount";
    if ($publishedCount !== 1) return "Published scope failed. Expected 1, got $publishedCount";
    
    return true;
});

// Test 5: Slug Uniqueness
test("Slug uniqueness handling", function() {
    $user = User::where('email', 'test@example.com')->first();
    
    // Create another post with same title
    $post2 = Post::create([
        'title' => 'Test Post Title',
        'content' => 'Another post with same title.',
        'status' => 'published',
        'published_at' => now(),
        'user_id' => $user->id,
    ]);
    
    if ($post2->slug !== 'test-post-title-1') return "Slug uniqueness failed. Expected 'test-post-title-1', got '{$post2->slug}'";
    
    return true;
});

// Test 6: Excerpt Generation
test("Automatic excerpt generation", function() {
    $user = User::where('email', 'test@example.com')->first();
    
    $longContent = str_repeat('This is a very long content that should be truncated when generating an excerpt. ', 20);
    
    $post = Post::create([
        'title' => 'Test Long Content Post',
        'content' => $longContent,
        'status' => 'published',
        'published_at' => now(),
        'user_id' => $user->id,
    ]);
    
    $excerpt = $post->excerpt;
    if (strlen($excerpt) > 163) return "Auto excerpt too long. Length: " . strlen($excerpt);
    if (!str_ends_with($excerpt, '...')) return "Auto excerpt should end with '...'";
    
    return true;
});

echo "\n=== 2. CRUD Operations Tests ===\n";

// Test 7: Create Operation
test("Post creation through controller logic", function() {
    $user = User::where('email', 'test@example.com')->first();
    
    // Simulate controller create logic
    $data = [
        'title' => 'Test Controller Post',
        'content' => 'Content created through controller logic',
        'excerpt' => 'Custom excerpt',
        'status' => 'published',
        'user_id' => $user->id,
    ];
    
    if ($data['status'] === 'published') {
        $data['published_at'] = now();
    }
    
    $post = Post::create($data);
    
    if (!$post->id) return "Controller create logic failed";
    if ($post->excerpt !== 'Custom excerpt') return "Custom excerpt not saved";
    
    return true;
});

// Test 8: Read Operation
test("Post retrieval and display logic", function() {
    // Test published posts retrieval
    $publishedPosts = Post::published()->with('user')->get();
    
    if ($publishedPosts->count() < 1) return "No published posts found";
    
    // Test individual post retrieval by slug
    $post = Post::published()->where('slug', 'test-post-title')->first();
    
    if (!$post) return "Post retrieval by slug failed";
    if (!$post->user) return "User relationship not loaded";
    
    return true;
});

// Test 9: Update Operation
test("Post update functionality", function() {
    $post = Post::where('slug', 'test-post-title')->first();
    $originalSlug = $post->slug;
    
    // Simulate controller update logic
    $updateData = [
        'title' => 'Updated Test Post Title',
        'content' => 'Updated content',
        'status' => 'published',
    ];
    
    $post->update($updateData);
    
    if ($post->title !== 'Updated Test Post Title') return "Title update failed";
    if ($post->content !== 'Updated content') return "Content update failed";
    // Slug should remain the same when updating existing post
    if ($post->slug !== $originalSlug) return "Slug should not change on update";
    
    return true;
});

// Test 10: Delete Operation
test("Post deletion functionality", function() {
    $post = Post::where('title', 'Test Long Content Post')->first();
    $postId = $post->id;
    
    $post->delete();
    
    $deletedPost = Post::find($postId);
    if ($deletedPost) return "Post deletion failed - post still exists";
    
    return true;
});

echo "\n=== 3. Error Handling Tests ===\n";

// Test 11: Validation Errors
test("Form validation error handling", function() {
    // Test validation rules
    $validator = validator([], [
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'status' => 'required|in:draft,published',
    ]);
    
    if (!$validator->fails()) return "Validation should fail for empty data";
    
    $errors = $validator->errors();
    if (!$errors->has('title')) return "Title validation error missing";
    if (!$errors->has('content')) return "Content validation error missing";
    if (!$errors->has('status')) return "Status validation error missing";
    
    return true;
});

// Test 12: Slug Validation
test("Slug validation rules", function() {
    // Test invalid slug formats
    $invalidSlugs = ['Invalid Slug', 'invalid_slug', 'invalid-slug-', '-invalid-slug', 'invalid--slug'];
    
    foreach ($invalidSlugs as $slug) {
        $validator = validator(['slug' => $slug], [
            'slug' => 'nullable|string|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
        ]);
        
        if (!$validator->fails()) return "Slug validation should fail for: $slug";
    }
    
    // Test valid slug
    $validator = validator(['slug' => 'valid-slug-123'], [
        'slug' => 'nullable|string|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
    ]);
    
    if ($validator->fails()) return "Valid slug should pass validation";
    
    return true;
});

// Test 13: 404 Handling
test("404 error handling for non-existent posts", function() {
    try {
        $post = Post::published()->where('slug', 'non-existent-slug')->firstOrFail();
        return "Should throw ModelNotFoundException";
    } catch (Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return true;
    }
});

echo "\n=== 4. Content Formatting Tests ===\n";

// Test 14: Content Formatting
test("Content formatting and HTML rendering", function() {
    $user = User::where('email', 'test@example.com')->first();
    
    $markdownContent = "# Header 1\n\n## Header 2\n\n**Bold text** and *italic text*\n\n`code snippet`\n\n> Blockquote text";
    
    $post = Post::create([
        'title' => 'Test Formatting Post',
        'content' => $markdownContent,
        'status' => 'published',
        'published_at' => now(),
        'user_id' => $user->id,
    ]);
    
    $formattedContent = $post->formatted_content;
    
    if (!str_contains($formattedContent, '<h1 class="display-4 mb-3">Header 1</h1>')) return "H1 formatting failed";
    if (!str_contains($formattedContent, '<h2 class="h2 mb-3">Header 2</h2>')) return "H2 formatting failed";
    if (!str_contains($formattedContent, '<strong>Bold text</strong>')) return "Bold formatting failed";
    if (!str_contains($formattedContent, '<em>italic text</em>')) return "Italic formatting failed";
    if (!str_contains($formattedContent, '<code class="bg-light px-2 py-1 rounded">code snippet</code>')) return "Code formatting failed";
    
    return true;
});

echo "\n=== 5. Security Tests ===\n";

// Test 15: XSS Prevention
test("XSS prevention in content", function() {
    $user = User::where('email', 'test@example.com')->first();
    
    $maliciousContent = '<script>alert("XSS")</script><p>Safe content</p><img src="x" onerror="alert(1)">';
    
    $post = Post::create([
        'title' => 'Test XSS Post',
        'content' => $maliciousContent,
        'status' => 'published',
        'published_at' => now(),
        'user_id' => $user->id,
    ]);
    
    $formattedContent = $post->formatted_content;
    
    if (str_contains($formattedContent, '<script>')) return "Script tags not stripped";
    if (str_contains($formattedContent, 'onerror=')) return "Event handlers not stripped";
    if (!str_contains($formattedContent, '<p>Safe content</p>')) return "Safe content was removed";
    
    return true;
});

echo "\n=== 6. Performance Tests ===\n";

// Test 16: Database Query Optimization
test("Eager loading relationships", function() {
    // Enable query logging
    DB::enableQueryLog();
    
    // Fetch posts with users (should use eager loading)
    $posts = Post::with('user')->published()->get();
    
    $queries = DB::getQueryLog();
    DB::disableQueryLog();
    
    // Should be 2 queries max (1 for posts, 1 for users)
    if (count($queries) > 2) return "Too many queries executed: " . count($queries);
    
    return true;
});

echo "\n=== Test Results Summary ===\n";
echo "Total Tests: " . ($passed + $failed) . "\n";
echo "Passed: $passed ✅\n";
echo "Failed: $failed ❌\n";
echo "Success Rate: " . round(($passed / ($passed + $failed)) * 100, 1) . "%\n\n";

if ($failed > 0) {
    echo "Failed Tests:\n";
    foreach ($tests as $test) {
        if ($test['status'] === 'FAILED') {
            echo "- {$test['description']}: {$test['error']}\n";
        }
    }
    echo "\n";
}

// Clean up test data
echo "Cleaning up test data...\n";
Post::where('title', 'like', 'Test%')->delete();
Post::where('title', 'like', 'Updated%')->delete();
User::where('email', 'like', 'test%')->delete();

echo "Test suite completed!\n";

if ($failed === 0) {
    echo "🎉 All tests passed! The blog functionality is working correctly.\n";
    exit(0);
} else {
    echo "⚠️  Some tests failed. Please review the issues above.\n";
    exit(1);
}