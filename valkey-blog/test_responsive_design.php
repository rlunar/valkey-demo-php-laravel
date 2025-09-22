<?php

/**
 * Responsive Design and Cross-Browser Compatibility Test
 * 
 * This script tests responsive design elements and error handling
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\Hash;

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Responsive Design & Error Handling Test ===\n\n";

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

// Create test data
echo "Setting up test data...\n";
$user = User::firstOrCreate(
    ['email' => 'test@example.com'],
    [
        'name' => 'Test Author',
        'password' => Hash::make('password'),
        'bio' => 'Test bio for responsive design testing',
    ]
);

$post = Post::firstOrCreate(
    ['slug' => 'responsive-test-post'],
    [
        'title' => 'Responsive Test Post',
        'content' => "# This is a test post\n\nThis post is used to test responsive design and cross-browser compatibility.\n\n## Features to test\n\n- **Responsive layout**\n- *Mobile navigation*\n- `Code formatting`\n\n> This is a blockquote for testing",
        'status' => 'published',
        'published_at' => now(),
        'user_id' => $user->id,
    ]
);

echo "\n=== 1. Bootstrap Integration Tests ===\n";

// Test 1: Bootstrap CSS Loading
test("Bootstrap CSS integration", function() {
    $response = file_get_contents('http://127.0.0.1:8000');
    
    if (!str_contains($response, 'bootstrap@5.3.0')) return "Bootstrap 5.3 CSS not found";
    if (!str_contains($response, 'bootstrap-icons')) return "Bootstrap Icons not found";
    
    return true;
});

// Test 2: Bootstrap JavaScript Loading
test("Bootstrap JavaScript integration", function() {
    $response = file_get_contents('http://127.0.0.1:8000');
    
    if (!str_contains($response, 'bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js')) return "Bootstrap JS not found";
    
    return true;
});

// Test 3: Responsive Meta Tags
test("Responsive viewport meta tag", function() {
    $response = file_get_contents('http://127.0.0.1:8000');
    
    if (!str_contains($response, 'name="viewport"')) return "Viewport meta tag not found";
    if (!str_contains($response, 'width=device-width')) return "Device width not set";
    if (!str_contains($response, 'initial-scale=1')) return "Initial scale not set";
    
    return true;
});

echo "\n=== 2. Layout Responsiveness Tests ===\n";

// Test 4: Bootstrap Grid Classes
test("Bootstrap grid system usage", function() {
    $response = file_get_contents('http://127.0.0.1:8000');
    
    if (!str_contains($response, 'col-md-')) return "Bootstrap grid classes not found";
    if (!str_contains($response, 'container')) return "Bootstrap container not found";
    if (!str_contains($response, 'row')) return "Bootstrap row classes not found";
    
    return true;
});

// Test 5: Mobile Navigation
test("Mobile navigation implementation", function() {
    $response = file_get_contents('http://127.0.0.1:8000');
    
    if (!str_contains($response, 'navbar-toggler')) return "Mobile navigation toggle not found";
    if (!str_contains($response, 'd-lg-none')) return "Mobile-only classes not found";
    if (!str_contains($response, 'navbar-collapse')) return "Collapsible navigation not found";
    
    return true;
});

// Test 6: Responsive Images/Placeholders
test("Responsive image handling", function() {
    $response = file_get_contents('http://127.0.0.1:8000');
    
    if (!str_contains($response, 'd-none d-lg-block')) return "Responsive display classes not found";
    if (!str_contains($response, 'bd-placeholder-img')) return "Placeholder images not found";
    
    return true;
});

echo "\n=== 3. Error Handling & User Feedback Tests ===\n";

// Test 7: Flash Message Display
test("Flash message system", function() {
    // Simulate a session with flash message
    session(['success' => 'Test success message']);
    
    $response = file_get_contents('http://127.0.0.1:8000');
    
    if (!str_contains($response, 'alert-success')) return "Success alert classes not found";
    if (!str_contains($response, 'alert-dismissible')) return "Dismissible alerts not implemented";
    if (!str_contains($response, 'btn-close')) return "Close button not found";
    
    return true;
});

// Test 8: 404 Error Page
test("404 error page handling", function() {
    $context = stream_context_create([
        'http' => [
            'ignore_errors' => true
        ]
    ]);
    
    $response = file_get_contents('http://127.0.0.1:8000/post/non-existent-slug', false, $context);
    $headers = $http_response_header;
    
    $statusLine = $headers[0];
    if (!str_contains($statusLine, '404')) return "404 status not returned for non-existent post";
    
    return true;
});

// Test 9: Form Validation Display
test("Form validation error display", function() {
    // Test admin post creation form
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'ignore_errors' => true
        ]
    ]);
    
    $response = file_get_contents('http://127.0.0.1:8000/admin/posts/create', false, $context);
    
    if (!str_contains($response, 'is-invalid')) return "Validation error classes not found";
    if (!str_contains($response, 'invalid-feedback')) return "Invalid feedback classes not found";
    
    return true;
});

echo "\n=== 4. Accessibility Tests ===\n";

// Test 10: ARIA Labels and Semantic HTML
test("Accessibility features", function() {
    $response = file_get_contents('http://127.0.0.1:8000');
    
    if (!str_contains($response, 'aria-label')) return "ARIA labels not found";
    if (!str_contains($response, '<main')) return "Main semantic element not found";
    if (!str_contains($response, '<header')) return "Header semantic element not found";
    if (!str_contains($response, '<footer')) return "Footer semantic element not found";
    
    return true;
});

// Test 11: Form Labels and Required Fields
test("Form accessibility", function() {
    $response = file_get_contents('http://127.0.0.1:8000/admin/posts/create');
    
    if (!str_contains($response, 'form-label')) return "Form labels not found";
    if (!str_contains($response, 'required')) return "Required field attributes not found";
    if (!str_contains($response, 'form-text')) return "Help text not found";
    
    return true;
});

echo "\n=== 5. Performance & Loading Tests ===\n";

// Test 12: CDN Usage for Bootstrap
test("CDN usage for external resources", function() {
    $response = file_get_contents('http://127.0.0.1:8000');
    
    if (!str_contains($response, 'cdn.jsdelivr.net')) return "CDN not used for Bootstrap";
    if (!str_contains($response, 'fonts.googleapis.com')) return "Google Fonts CDN not used";
    
    return true;
});

// Test 13: CSS and JS Optimization
test("Asset optimization", function() {
    $response = file_get_contents('http://127.0.0.1:8000');
    
    // Check for Vite asset compilation
    if (!str_contains($response, 'resources/css/app.css') && !str_contains($response, '/build/')) {
        return "Asset compilation not properly configured";
    }
    
    return true;
});

echo "\n=== 6. Content Display Tests ===\n";

// Test 14: Post Content Formatting
test("Post content formatting and styling", function() {
    $response = file_get_contents('http://127.0.0.1:8000/post/responsive-test-post');
    
    if (!str_contains($response, 'display-4')) return "Header styling not applied";
    if (!str_contains($response, 'bg-light')) return "Code styling not applied";
    if (!str_contains($response, 'blockquote')) return "Blockquote styling not applied";
    
    return true;
});

// Test 15: Pagination Styling
test("Pagination responsive design", function() {
    $response = file_get_contents('http://127.0.0.1:8000');
    
    if (!str_contains($response, 'pagination')) return "Pagination classes not found";
    if (!str_contains($response, 'd-flex justify-content-center')) return "Centered pagination not implemented";
    
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
Post::where('slug', 'responsive-test-post')->delete();
User::where('email', 'test@example.com')->delete();

echo "Responsive design test completed!\n";

if ($failed === 0) {
    echo "🎉 All responsive design tests passed!\n";
    exit(0);
} else {
    echo "⚠️  Some tests failed. Please review the issues above.\n";
    exit(1);
}