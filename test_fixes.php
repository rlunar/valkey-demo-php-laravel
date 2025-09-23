<?php

/**
 * Test fixes for the identified issues
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\Hash;

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Fixes ===\n\n";

// Create test data with enough posts for pagination
echo "Creating test data...\n";

$user = User::firstOrCreate(
    ['email' => 'test@example.com'],
    [
        'name' => 'Test Author',
        'password' => Hash::make('password'),
        'bio' => 'Test bio for responsive design testing',
    ]
);

// Create multiple posts to trigger pagination
for ($i = 1; $i <= 12; $i++) {
    Post::firstOrCreate(
        ['slug' => "test-post-$i"],
        [
            'title' => "Test Post $i",
            'content' => "This is test post number $i content.",
            'status' => 'published',
            'published_at' => now()->subDays($i),
            'user_id' => $user->id,
        ]
    );
}

echo "Created test data with " . Post::count() . " posts\n\n";

// Test 1: Check if pagination appears with multiple posts
echo "Testing pagination with multiple posts... ";
$response = file_get_contents('http://127.0.0.1:8000');

if (str_contains($response, 'pagination') || str_contains($response, 'page-link')) {
    echo "✅ PASSED - Pagination found\n";
} else {
    echo "❌ FAILED - Pagination not found\n";
}

// Test 2: Check responsive classes in post cards
echo "Testing responsive classes in post cards... ";
if (str_contains($response, 'd-none d-lg-block') || str_contains($response, 'col-md-6')) {
    echo "✅ PASSED - Responsive classes found\n";
} else {
    echo "❌ FAILED - Responsive classes not found\n";
}

// Test 3: Check form validation structure
echo "Testing form validation structure... ";
$createResponse = file_get_contents('http://127.0.0.1:8000/admin/posts/create');

if (str_contains($createResponse, '@error') || str_contains($createResponse, 'is-invalid')) {
    echo "✅ PASSED - Form validation structure found\n";
} else {
    echo "❌ FAILED - Form validation structure not found\n";
}

// Test 4: Check help text in forms
echo "Testing form help text... ";
if (str_contains($createResponse, 'form-text') || str_contains($createResponse, 'help')) {
    echo "✅ PASSED - Form help text found\n";
} else {
    echo "❌ FAILED - Form help text not found\n";
}

// Test 5: Check alert structure
echo "Testing alert structure... ";
if (str_contains($response, 'alert-success') || str_contains($response, 'alert-danger')) {
    echo "✅ PASSED - Alert structure found\n";
} else {
    echo "❌ FAILED - Alert structure not found\n";
}

echo "\n=== Manual Verification Checklist ===\n";
echo "Please manually verify the following:\n";
echo "1. ✓ Homepage loads correctly at http://127.0.0.1:8000\n";
echo "2. ✓ Posts display in responsive grid layout\n";
echo "3. ✓ Navigation collapses on mobile (resize browser window)\n";
echo "4. ✓ Individual post pages load correctly\n";
echo "5. ✓ Admin forms have proper validation and help text\n";
echo "6. ✓ Flash messages appear when performing actions\n";
echo "7. ✓ 404 pages display for non-existent posts\n";
echo "8. ✓ All Bootstrap components work correctly\n";

// Clean up
echo "\nCleaning up test data...\n";
Post::where('title', 'like', 'Test Post%')->delete();
User::where('email', 'test@example.com')->delete();

echo "Test completed!\n";