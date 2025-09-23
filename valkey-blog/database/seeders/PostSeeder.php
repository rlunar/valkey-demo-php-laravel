<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Increase memory limit for this seeder
        ini_set('memory_limit', '256M');

        // Disable query logging to save memory
        DB::disableQueryLog();

        // Get IDs only to reduce memory usage
        $userIds = User::pluck('id')->toArray();
        $categoryIds = Category::pluck('id')->toArray();
        $tagIds = Tag::pluck('id')->toArray();

        // Clear collections to free memory
        unset($users, $categories, $tags);

        // Create some featured posts with specific content
        $featuredPosts = [
            [
                'title' => 'Getting Started with Valkey: A Comprehensive Guide',
                'content' => $this->getValkeyIntroContent(),
                'excerpt' => 'Learn how to get started with Valkey, the high-performance key-value store that\'s Redis-compatible.',
                'status' => 'published',
                'published_at' => now()->subDays(1),
                'category' => 'Tutorials',
                'tags' => ['valkey', 'getting-started', 'tutorial', 'redis'],
            ],
            [
                'title' => 'Valkey vs Redis: Performance Benchmarks and Migration Guide',
                'content' => $this->getPerformanceComparisonContent(),
                'excerpt' => 'Detailed performance comparison between Valkey and Redis, plus a step-by-step migration guide.',
                'status' => 'published',
                'published_at' => now()->subDays(3),
                'category' => 'Performance',
                'tags' => ['valkey', 'redis', 'performance', 'benchmarks', 'migration'],
            ],
            [
                'title' => 'Building Scalable Applications with Valkey Clustering',
                'content' => $this->getClusteringContent(),
                'excerpt' => 'Learn how to implement and manage Valkey clusters for high-availability applications.',
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'category' => 'Architecture',
                'tags' => ['valkey', 'clustering', 'scalability', 'high-availability'],
            ],
            [
                'title' => 'Advanced Data Structures in Valkey: Beyond Key-Value',
                'content' => $this->getDataStructuresContent(),
                'excerpt' => 'Explore advanced data structures available in Valkey and their practical applications.',
                'status' => 'published',
                'published_at' => now()->subDays(7),
                'category' => 'Development',
                'tags' => ['valkey', 'data-structures', 'development', 'advanced'],
            ],
            [
                'title' => 'Optimizing Valkey for Production Workloads',
                'content' => $this->getOptimizationContent(),
                'excerpt' => 'Best practices and configuration tips for running Valkey in production environments.',
                'status' => 'published',
                'published_at' => now()->subDays(10),
                'category' => 'Performance',
                'tags' => ['valkey', 'production', 'optimization', 'configuration'],
            ],
        ];

        // Get category mapping for featured posts
        $categoryMap = Category::pluck('id', 'name')->toArray();
        $tagMap = Tag::pluck('id', 'name')->toArray();

        foreach ($featuredPosts as $postData) {
            $categoryId = $categoryMap[$postData['category']] ?? $categoryIds[0];
            $userId = $userIds[array_rand($userIds)];

            $post = Post::create([
                'title' => $postData['title'],
                'content' => $postData['content'],
                'excerpt' => $postData['excerpt'],
                'status' => $postData['status'],
                'published_at' => $postData['published_at'],
                'user_id' => $userId,
                'category_id' => $categoryId,
            ]);

            // Attach tags using IDs
            $postTagIds = [];
            foreach ($postData['tags'] as $tagName) {
                if (isset($tagMap[$tagName])) {
                    $postTagIds[] = $tagMap[$tagName];
                }
            }
            if (! empty($postTagIds)) {
                $post->tags()->attach($postTagIds);
            }
        }

        // Clear featured posts data
        unset($featuredPosts, $categoryMap, $tagMap);

        // Create additional random posts in smaller chunks
        $totalPosts = 2500;
        $chunkSize = 25; // Reduced chunk size for memory efficiency

        for ($i = 0; $i < $totalPosts; $i += $chunkSize) {
            $currentChunkSize = min($chunkSize, $totalPosts - $i);

            $posts = Post::factory($currentChunkSize)->create([
                'user_id' => $userIds[array_rand($userIds)],
            ]);

            // Attach tags in batch
            foreach ($posts as $post) {
                $numTags = rand(1, 5);
                $randomTagIds = array_rand(array_flip($tagIds), $numTags);
                if (! is_array($randomTagIds)) {
                    $randomTagIds = [$randomTagIds];
                }
                $post->tags()->attach($randomTagIds);
            }

            // Clear the posts collection
            unset($posts);

            // Free memory after each chunk
            $this->freeMemory();

            $this->command->info("Created chunk of {$currentChunkSize} posts. Progress: ".($i + $currentChunkSize)."/{$totalPosts}");
        }

        // Create some draft posts in smaller chunks
        $totalDraftPosts = 800;

        for ($i = 0; $i < $totalDraftPosts; $i += $chunkSize) {
            $currentChunkSize = min($chunkSize, $totalDraftPosts - $i);

            $draftPosts = Post::factory($currentChunkSize)->draft()->create([
                'user_id' => $userIds[array_rand($userIds)],
            ]);

            // Attach tags in batch
            foreach ($draftPosts as $post) {
                $numTags = rand(1, 3);
                $randomTagIds = array_rand(array_flip($tagIds), $numTags);
                if (! is_array($randomTagIds)) {
                    $randomTagIds = [$randomTagIds];
                }
                $post->tags()->attach($randomTagIds);
            }

            // Clear the posts collection
            unset($draftPosts);

            // Free memory after each chunk
            $this->freeMemory();

            $this->command->info("Created chunk of {$currentChunkSize} draft posts. Progress: ".($i + $currentChunkSize)."/{$totalDraftPosts}");
        }
    }

    private function getValkeyIntroContent(): string
    {
        return '# Introduction to Valkey

Valkey is a high-performance data structure server that serves as a drop-in replacement for Redis. Built with performance, reliability, and compatibility in mind, Valkey offers all the features you love about Redis while providing enhanced performance and additional capabilities.

## What is Valkey?

Valkey is an open-source, in-memory data structure store that can be used as a database, cache, and message broker. It supports various data structures such as strings, hashes, lists, sets, sorted sets, and more.

## Key Features

- **Redis Compatibility**: Drop-in replacement for Redis
- **High Performance**: Optimized for speed and efficiency
- **Rich Data Types**: Support for complex data structures
- **Persistence**: Multiple persistence options
- **Clustering**: Built-in clustering support
- **Pub/Sub**: Message publishing and subscription

## Installation

Getting started with Valkey is straightforward. You can install it using various methods:

### Using Docker

```bash
docker run -d --name valkey -p 6379:6379 valkey/valkey:latest
```

### From Source

```bash
git clone https://github.com/valkey-io/valkey.git
cd valkey
make
make install
```

## Basic Usage

Once installed, you can connect to Valkey using any Redis-compatible client:

```bash
valkey-cli
127.0.0.1:6379> SET mykey "Hello Valkey"
OK
127.0.0.1:6379> GET mykey
"Hello Valkey"
```

## Next Steps

Now that you have Valkey running, explore our other tutorials to learn about advanced features, clustering, and optimization techniques.';
    }

    private function getPerformanceComparisonContent(): string
    {
        return "# Valkey vs Redis: Performance Analysis

In this comprehensive analysis, we'll compare Valkey and Redis performance across various workloads and scenarios.

## Benchmark Setup

Our benchmarks were conducted using:
- Hardware: 16-core CPU, 64GB RAM, NVMe SSD
- Network: 10Gbps connection
- Test duration: 30 minutes per test
- Concurrent connections: 100-1000

## Results Summary

### Throughput Comparison

| Operation | Redis (ops/sec) | Valkey (ops/sec) | Improvement |
|-----------|----------------|------------------|-------------|
| SET       | 180,000        | 220,000          | +22%        |
| GET       | 200,000        | 250,000          | +25%        |
| HSET      | 150,000        | 185,000          | +23%        |
| LPUSH     | 160,000        | 195,000          | +22%        |

### Memory Usage

Valkey demonstrates superior memory efficiency:
- 15% lower memory overhead
- Better memory fragmentation handling
- Improved garbage collection

## Migration Guide

### Step 1: Backup Your Data

```bash
redis-cli --rdb backup.rdb
```

### Step 2: Install Valkey

Follow the installation guide in our previous tutorial.

### Step 3: Import Data

```bash
valkey-cli --pipe < backup.rdb
```

### Step 4: Update Client Configuration

Most Redis clients work seamlessly with Valkey. Simply update your connection string.

## Conclusion

Valkey offers significant performance improvements while maintaining full Redis compatibility, making it an excellent choice for high-performance applications.";
    }

    private function getClusteringContent(): string
    {
        return '# Valkey Clustering: Building Scalable Applications

Learn how to set up and manage Valkey clusters for high-availability and scalable applications.

## Cluster Architecture

Valkey clustering provides:
- Automatic data sharding
- High availability through replication
- Horizontal scaling capabilities
- Automatic failover

## Setting Up a Cluster

### Prerequisites

- At least 6 Valkey instances (3 masters, 3 replicas)
- Network connectivity between all nodes
- Consistent configuration across nodes

### Configuration

Create a cluster configuration file:

```
port 7000
cluster-enabled yes
cluster-config-file nodes.conf
cluster-node-timeout 5000
appendonly yes
```

### Starting the Cluster

```bash
# Start all nodes
for port in {7000..7005}; do
    valkey-server cluster-$port.conf &
done

# Create the cluster
valkey-cli --cluster create 127.0.0.1:7000 127.0.0.1:7001 127.0.0.1:7002 127.0.0.1:7003 127.0.0.1:7004 127.0.0.1:7005 --cluster-replicas 1
```

## Monitoring and Maintenance

### Health Checks

```bash
valkey-cli --cluster check 127.0.0.1:7000
```

### Adding Nodes

```bash
valkey-cli --cluster add-node 127.0.0.1:7006 127.0.0.1:7000
```

### Rebalancing

```bash
valkey-cli --cluster rebalance 127.0.0.1:7000
```

## Best Practices

1. **Monitor cluster health** regularly
2. **Plan for capacity** before scaling
3. **Use consistent hashing** for data distribution
4. **Implement proper backup strategies**
5. **Test failover scenarios**

Clustering is essential for production deployments requiring high availability and scalability.';
    }

    private function getDataStructuresContent(): string
    {
        return "# Advanced Data Structures in Valkey

Valkey supports a rich set of data structures beyond simple key-value pairs. Let's explore these powerful features.

## String Operations

Basic string operations with advanced features:

```bash
# Atomic counters
INCR page_views
INCRBY downloads 5

# Bit operations
SETBIT user:1000 10 1
GETBIT user:1000 10
```

## Hash Tables

Perfect for representing objects:

```bash
HSET user:1000 name \"John Doe\" email \"john@example.com\" age 30
HGET user:1000 name
HMGET user:1000 name email
```

## Lists

Implement queues and stacks:

```bash
# Queue (FIFO)
LPUSH queue:tasks \"task1\"
RPOP queue:tasks

# Stack (LIFO)
LPUSH stack:items \"item1\"
LPOP stack:items
```

## Sets

Unique collections with set operations:

```bash
SADD tags:post1 \"redis\" \"database\" \"cache\"
SADD tags:post2 \"redis\" \"performance\" \"scaling\"

# Intersection
SINTER tags:post1 tags:post2
```

## Sorted Sets

Ordered collections with scores:

```bash
ZADD leaderboard 100 \"player1\" 200 \"player2\" 150 \"player3\"
ZRANGE leaderboard 0 -1 WITHSCORES
ZREVRANGE leaderboard 0 2
```

## HyperLogLog

Approximate cardinality counting:

```bash
PFADD unique_visitors \"user1\" \"user2\" \"user3\"
PFCOUNT unique_visitors
```

## Streams

Event streaming and message queues:

```bash
XADD events * action \"login\" user \"john\" timestamp 1234567890
XREAD STREAMS events 0
```

## Geospatial

Location-based operations:

```bash
GEOADD locations 13.361389 38.115556 \"Palermo\" 15.087269 37.502669 \"Catania\"
GEODIST locations \"Palermo\" \"Catania\" km
```

## Use Cases

- **Strings**: Caching, counters, feature flags
- **Hashes**: User profiles, configuration objects
- **Lists**: Message queues, activity feeds
- **Sets**: Tags, unique visitors, recommendations
- **Sorted Sets**: Leaderboards, time series data
- **Streams**: Event sourcing, real-time analytics

These data structures make Valkey incredibly versatile for modern application development.";
    }

    private function getOptimizationContent(): string
    {
        return "# Optimizing Valkey for Production

Running Valkey in production requires careful configuration and monitoring. Here's your complete optimization guide.

## Memory Optimization

### Memory Policies

Configure eviction policies based on your use case:

```
# LRU eviction for cache scenarios
maxmemory-policy allkeys-lru

# No eviction for persistent data
maxmemory-policy noeviction
```

### Memory Usage Monitoring

```bash
# Check memory usage
INFO memory

# Analyze key patterns
valkey-cli --bigkeys
```

## Performance Tuning

### Kernel Parameters

Optimize OS-level settings:

```bash
# Disable transparent huge pages
echo never > /sys/kernel/mm/transparent_hugepage/enabled

# Increase somaxconn
echo 65535 > /proc/sys/net/core/somaxconn

# Optimize TCP settings
echo 1 > /proc/sys/net/ipv4/tcp_tw_reuse
```

### Valkey Configuration

```
# Increase client connections
maxclients 10000

# Optimize save intervals
save 900 1
save 300 10
save 60 10000

# Enable lazy freeing
lazyfree-lazy-eviction yes
lazyfree-lazy-expire yes
```

## Persistence Strategies

### RDB Snapshots

```
# Automatic snapshots
save 900 1
save 300 10
save 60 10000

# Manual snapshots
BGSAVE
```

### AOF (Append Only File)

```
# Enable AOF
appendonly yes
appendfsync everysec

# AOF rewrite
auto-aof-rewrite-percentage 100
auto-aof-rewrite-min-size 64mb
```

## Monitoring and Alerting

### Key Metrics

Monitor these critical metrics:

- Memory usage and fragmentation
- CPU utilization
- Network I/O
- Command latency
- Client connections
- Keyspace hits/misses

### Monitoring Tools

```bash
# Built-in monitoring
MONITOR
INFO all
LATENCY DOCTOR

# External tools integration
# Prometheus, Grafana, DataDog
```

## Security Hardening

### Authentication

```
# Require password
requirepass your_strong_password

# Rename dangerous commands
rename-command FLUSHDB \"\"
rename-command FLUSHALL \"\"
```

### Network Security

```
# Bind to specific interfaces
bind 127.0.0.1 10.0.0.1

# Enable TLS
tls-port 6380
tls-cert-file /path/to/cert.pem
tls-key-file /path/to/key.pem
```

## Backup and Recovery

### Automated Backups

```bash
#!/bin/bash
# Daily backup script
DATE=\$(date +%Y%m%d)
valkey-cli --rdb /backup/valkey-\$DATE.rdb
```

### Disaster Recovery

1. **Regular backups** to multiple locations
2. **Test restore procedures** regularly
3. **Document recovery processes**
4. **Monitor backup integrity**

## Scaling Strategies

### Vertical Scaling

- Increase memory and CPU
- Optimize data structures
- Use pipelining for bulk operations

### Horizontal Scaling

- Implement clustering
- Use read replicas
- Partition data strategically

Following these optimization practices ensures your Valkey deployment performs reliably under production workloads.";
    }

    /**
     * Free up memory after processing each chunk
     */
    private function freeMemory(): void
    {
        // Clear Eloquent model cache
        if (method_exists(Post::class, 'clearBootedModels')) {
            Post::clearBootedModels();
        }

        // Force garbage collection
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }

        // Clear memory cycles
        gc_mem_caches();
    }
}
