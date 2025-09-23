<?php

/**
 * Manual Test Script for Blog Functionality
 * This script creates test data and provides instructions for manual testing
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\Hash;

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Blog Manual Test Setup ===\n\n";

// Clean up existing test data
echo "Cleaning up existing test data...\n";
Post::where('title', 'like', '%Test%')->delete();
User::where('email', 'like', 'test%')->delete();

// Create test user
echo "Creating test user...\n";
$user = User::create([
    'name' => 'Test Author',
    'email' => 'test@valkey.io',
    'password' => Hash::make('password123'),
    'bio' => 'Senior Developer Advocate for Valkey, passionate about high-performance data structures and Redis compatibility.',
]);

echo "✅ Created user: {$user->email} (password: password123)\n";

// Create sample posts
echo "\nCreating sample blog posts...\n";

$posts = [
    [
        'title' => 'Getting Started with Valkey: A Redis-Compatible Alternative',
        'content' => "# Welcome to Valkey\n\nValkey is a high-performance data structure server that is fully compatible with Redis. In this post, we'll explore the key features and benefits of using Valkey in your applications.\n\n## Key Features\n\n- **Redis Compatibility**: Drop-in replacement for Redis\n- **High Performance**: Optimized for speed and efficiency\n- **Open Source**: Community-driven development\n\n### Installation\n\nTo get started with Valkey, you can install it using the following command:\n\n```bash\nwget https://download.valkey.io/valkey-latest.tar.gz\ntar -xzf valkey-latest.tar.gz\ncd valkey-*\nmake && make install\n```\n\n> **Note**: Make sure you have the required dependencies installed before compiling Valkey.\n\n## Performance Benchmarks\n\nOur benchmarks show that Valkey performs exceptionally well:\n\n- **SET operations**: 100,000+ ops/sec\n- **GET operations**: 150,000+ ops/sec\n- **Memory efficiency**: 20% better than alternatives\n\nStay tuned for more detailed performance analysis in upcoming posts!",
        'excerpt' => 'Discover Valkey, a high-performance Redis-compatible data structure server. Learn about its key features, installation process, and impressive performance benchmarks.',
        'status' => 'published',
    ],
    [
        'title' => 'Migrating from Redis to Valkey: A Step-by-Step Guide',
        'content' => "# Seamless Migration to Valkey\n\nMigrating from Redis to Valkey is straightforward thanks to full protocol compatibility. This guide will walk you through the migration process step by step.\n\n## Pre-Migration Checklist\n\nBefore starting your migration:\n\n1. **Backup your Redis data**\n2. **Test Valkey in a staging environment**\n3. **Review your application's Redis usage**\n4. **Plan your migration timeline**\n\n## Migration Steps\n\n### Step 1: Install Valkey\n\nFirst, install Valkey on your target servers:\n\n```bash\n# Download and install Valkey\nwget https://download.valkey.io/valkey-7.2.tar.gz\ntar -xzf valkey-7.2.tar.gz\ncd valkey-7.2\nmake && sudo make install\n```\n\n### Step 2: Configure Valkey\n\nCopy your Redis configuration and adapt it for Valkey:\n\n```bash\ncp /etc/redis/redis.conf /etc/valkey/valkey.conf\n# Edit the configuration as needed\n```\n\n### Step 3: Data Migration\n\nUse the built-in migration tools:\n\n```bash\nvalkey-cli --rdb /path/to/redis/dump.rdb\n```\n\n## Testing Your Migration\n\nAfter migration, thoroughly test your application:\n\n- Verify all data is present\n- Test application functionality\n- Monitor performance metrics\n- Check error logs\n\n*Happy migrating!*",
        'excerpt' => 'Learn how to migrate from Redis to Valkey with this comprehensive step-by-step guide. Includes pre-migration checklist, installation steps, and testing procedures.',
        'status' => 'published',
    ],
    [
        'title' => 'Advanced Valkey Configuration for Production Environments',
        'content' => "# Production-Ready Valkey Configuration\n\nRunning Valkey in production requires careful configuration to ensure optimal performance, security, and reliability.\n\n## Memory Management\n\n### Setting Memory Limits\n\n```conf\nmaxmemory 2gb\nmaxmemory-policy allkeys-lru\n```\n\n### Memory Optimization\n\n- Use appropriate data structures\n- Enable compression when beneficial\n- Monitor memory usage patterns\n\n## Security Configuration\n\n### Authentication\n\n```conf\nrequirepass your_secure_password_here\n```\n\n### Network Security\n\n```conf\nbind 127.0.0.1 10.0.0.1\nprotected-mode yes\nport 6379\n```\n\n## Performance Tuning\n\n### Persistence Settings\n\n```conf\n# RDB snapshots\nsave 900 1\nsave 300 10\nsave 60 10000\n\n# AOF persistence\nappendonly yes\nappendfsync everysec\n```\n\n### Client Connections\n\n```conf\nmaxclients 10000\ntcp-keepalive 300\n```\n\n## Monitoring and Logging\n\nSet up comprehensive monitoring:\n\n- **Metrics to track**: Memory usage, CPU utilization, connection count\n- **Log levels**: Configure appropriate logging levels\n- **Alerting**: Set up alerts for critical thresholds\n\n> **Pro Tip**: Always test configuration changes in a staging environment before applying to production.",
        'excerpt' => 'Master Valkey configuration for production environments. Covers memory management, security settings, performance tuning, and monitoring best practices.',
        'status' => 'published',
    ],
    [
        'title' => 'Understanding Valkey Data Structures and Use Cases',
        'content' => "# Valkey Data Structures Deep Dive\n\nValkey supports various data structures, each optimized for specific use cases. Let's explore when and how to use each one effectively.\n\n## Strings\n\nThe most basic data type in Valkey:\n\n```bash\nSET user:1000:name \"John Doe\"\nGET user:1000:name\nINCR page_views\n```\n\n**Use cases:**\n- Caching\n- Counters\n- Session storage\n\n## Lists\n\nOrdered collections of strings:\n\n```bash\nLPUSH notifications \"New message received\"\nLRANGE notifications 0 10\nRPOP notifications\n```\n\n**Use cases:**\n- Message queues\n- Activity feeds\n- Recent items lists\n\n## Sets\n\nUnordered collections of unique strings:\n\n```bash\nSADD user:1000:interests \"redis\" \"databases\" \"performance\"\nSMEMBERS user:1000:interests\nSINTER user:1000:interests user:2000:interests\n```\n\n**Use cases:**\n- Tags\n- Unique visitors tracking\n- Social graph relationships\n\n## Sorted Sets\n\nSets with scores for ordering:\n\n```bash\nZADD leaderboard 1500 \"player1\"\nZADD leaderboard 2000 \"player2\"\nZRANGE leaderboard 0 10 WITHSCORES\n```\n\n**Use cases:**\n- Leaderboards\n- Priority queues\n- Time-series data\n\n## Hashes\n\nField-value pairs:\n\n```bash\nHSET user:1000 name \"John\" email \"john@example.com\" age 30\nHGET user:1000 name\nHGETALL user:1000\n```\n\n**Use cases:**\n- Object storage\n- User profiles\n- Configuration data\n\n## Choosing the Right Data Structure\n\nConsider these factors:\n\n1. **Access patterns**: How will you read/write the data?\n2. **Memory efficiency**: Which structure uses memory most efficiently?\n3. **Performance requirements**: What operations need to be fast?\n4. **Data relationships**: How does the data relate to other data?\n\n*Choose wisely for optimal performance!*",
        'excerpt' => 'Comprehensive guide to Valkey data structures including strings, lists, sets, sorted sets, and hashes. Learn when and how to use each type effectively.',
        'status' => 'published',
    ],
    [
        'title' => 'Valkey vs Redis: Performance Comparison and Benchmarks',
        'content' => "# Valkey vs Redis: Performance Analysis\n\nIn this comprehensive comparison, we'll examine the performance differences between Valkey and Redis across various workloads and scenarios.\n\n## Benchmark Environment\n\n### Hardware Specifications\n- **CPU**: Intel Xeon E5-2686 v4 (8 cores)\n- **RAM**: 32GB DDR4\n- **Storage**: NVMe SSD\n- **Network**: 10 Gbps\n\n### Software Versions\n- **Valkey**: 7.2.0\n- **Redis**: 7.0.5\n- **OS**: Ubuntu 22.04 LTS\n\n## Benchmark Results\n\n### String Operations\n\n| Operation | Valkey (ops/sec) | Redis (ops/sec) | Improvement |\n|-----------|------------------|-----------------|-------------|\n| SET       | 125,000         | 118,000         | +5.9%       |\n| GET       | 180,000         | 172,000         | +4.7%       |\n| INCR      | 135,000         | 128,000         | +5.5%       |\n\n### List Operations\n\n```bash\n# Benchmark command used\nvalkey-benchmark -t lpush,lpop,lrange -n 100000 -c 50\n```\n\n| Operation | Valkey (ops/sec) | Redis (ops/sec) | Improvement |\n|-----------|------------------|-----------------|-------------|\n| LPUSH     | 110,000         | 105,000         | +4.8%       |\n| LPOP      | 115,000         | 108,000         | +6.5%       |\n| LRANGE    | 95,000          | 92,000          | +3.3%       |\n\n### Memory Efficiency\n\nValkey demonstrates superior memory efficiency:\n\n- **20% less memory usage** for string operations\n- **15% less memory usage** for hash operations\n- **Improved garbage collection** performance\n\n## Real-World Scenarios\n\n### Scenario 1: High-Frequency Caching\n\n**Workload**: 70% reads, 30% writes\n**Result**: Valkey showed 8% better throughput\n\n### Scenario 2: Session Storage\n\n**Workload**: Mixed hash operations\n**Result**: Valkey used 18% less memory\n\n### Scenario 3: Message Queue\n\n**Workload**: List push/pop operations\n**Result**: Valkey achieved 12% higher throughput\n\n## Key Takeaways\n\n1. **Consistent Performance Gains**: Valkey shows improvements across all tested operations\n2. **Memory Efficiency**: Significant memory savings in production workloads\n3. **Drop-in Compatibility**: No application changes required\n4. **Stability**: Maintains Redis' reliability while improving performance\n\n## Migration Recommendation\n\nBased on our benchmarks, migrating to Valkey provides:\n\n- ✅ **Better performance** across all operations\n- ✅ **Lower memory usage** reducing infrastructure costs\n- ✅ **Full compatibility** with existing Redis applications\n- ✅ **Active development** and community support\n\n*Ready to make the switch? Check out our migration guide!*",
        'excerpt' => 'Detailed performance comparison between Valkey and Redis with comprehensive benchmarks, real-world scenarios, and migration recommendations.',
        'status' => 'draft',
    ]
];

foreach ($posts as $index => $postData) {
    $post = Post::create([
        'title' => $postData['title'],
        'content' => $postData['content'],
        'excerpt' => $postData['excerpt'],
        'status' => $postData['status'],
        'published_at' => $postData['status'] === 'published' ? now()->subDays($index) : null,
        'user_id' => $user->id,
    ]);
    
    echo "✅ Created post: {$post->title} (Status: {$post->status})\n";
}

echo "\n=== Manual Testing Instructions ===\n\n";

echo "🌐 **Website URLs to Test:**\n";
echo "- Homepage: http://127.0.0.1:8000\n";
echo "- Sample post: http://127.0.0.1:8000/post/{$posts[0]['title']}\n";
echo "- Login: http://127.0.0.1:8000/login\n";
echo "- Admin posts: http://127.0.0.1:8000/admin/posts (requires login)\n\n";

echo "👤 **Test User Credentials:**\n";
echo "- Email: test@valkey.io\n";
echo "- Password: password123\n\n";

echo "✅ **Functionality to Test:**\n\n";

echo "**1. Public Blog Features:**\n";
echo "   - ✓ Homepage loads with published posts\n";
echo "   - ✓ Post cards display correctly\n";
echo "   - ✓ Individual post pages work\n";
echo "   - ✓ Navigation is responsive (resize browser)\n";
echo "   - ✓ Pagination appears (if more than 10 posts)\n";
echo "   - ✓ 404 page for non-existent posts\n\n";

echo "**2. Responsive Design:**\n";
echo "   - ✓ Mobile navigation collapses properly\n";
echo "   - ✓ Grid layout adapts to screen size\n";
echo "   - ✓ Typography scales appropriately\n";
echo "   - ✓ Images/placeholders hide on mobile\n";
echo "   - ✓ Forms are mobile-friendly\n\n";

echo "**3. Admin Features (after login):**\n";
echo "   - ✓ Post listing page works\n";
echo "   - ✓ Create new post form\n";
echo "   - ✓ Edit existing post\n";
echo "   - ✓ Delete post functionality\n";
echo "   - ✓ Form validation works\n";
echo "   - ✓ Success/error messages appear\n\n";

echo "**4. Error Handling:**\n";
echo "   - ✓ Form validation errors display\n";
echo "   - ✓ Flash messages work correctly\n";
echo "   - ✓ 404 pages display properly\n";
echo "   - ✓ Authentication redirects work\n\n";

echo "**5. Content Features:**\n";
echo "   - ✓ Markdown formatting renders correctly\n";
echo "   - ✓ Slug generation works\n";
echo "   - ✓ Excerpt generation/display\n";
echo "   - ✓ Author information displays\n";
echo "   - ✓ Publication dates show correctly\n\n";

echo "**6. Security:**\n";
echo "   - ✓ Admin routes require authentication\n";
echo "   - ✓ CSRF protection on forms\n";
echo "   - ✓ XSS prevention in content\n";
echo "   - ✓ Input validation works\n\n";

echo "📱 **Cross-Browser Testing:**\n";
echo "Test in multiple browsers:\n";
echo "   - ✓ Chrome/Chromium\n";
echo "   - ✓ Firefox\n";
echo "   - ✓ Safari (if available)\n";
echo "   - ✓ Mobile browsers\n\n";

echo "🔧 **Performance Checks:**\n";
echo "   - ✓ Page load times are reasonable\n";
echo "   - ✓ Bootstrap assets load from CDN\n";
echo "   - ✓ No JavaScript errors in console\n";
echo "   - ✓ Images/assets load properly\n\n";

echo "📊 **Database Verification:**\n";
echo "   - Published posts: " . Post::published()->count() . "\n";
echo "   - Draft posts: " . Post::where('status', 'draft')->count() . "\n";
echo "   - Total posts: " . Post::count() . "\n";
echo "   - Users: " . User::count() . "\n\n";

echo "🎯 **Test Completion Checklist:**\n";
echo "After testing, verify that:\n";
echo "   □ All CRUD operations work correctly\n";
echo "   □ Responsive design works on different screen sizes\n";
echo "   □ Error handling provides proper user feedback\n";
echo "   □ Cross-browser compatibility is maintained\n";
echo "   □ Performance is acceptable\n";
echo "   □ Security measures are in place\n\n";

echo "🧹 **Cleanup:**\n";
echo "To clean up test data after testing, run:\n";
echo "php artisan tinker --execute=\"App\\Models\\Post::where('user_id', {$user->id})->delete(); App\\Models\\User::find({$user->id})->delete();\"\n\n";

echo "=== Test Setup Complete! ===\n";
echo "You can now manually test all functionality using the instructions above.\n";