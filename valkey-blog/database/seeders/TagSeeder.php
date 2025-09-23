<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            // Programming Languages
            'php',
            'python',
            'javascript',
            'go',
            'rust',
            'java',
            'c++',
            'nodejs',
            
            // Frameworks & Libraries
            'laravel',
            'symfony',
            'django',
            'react',
            'vue',
            'express',
            'spring',
            
            // Database & Storage
            'redis',
            'valkey',
            'mysql',
            'postgresql',
            'mongodb',
            'elasticsearch',
            'memcached',
            
            // DevOps & Infrastructure
            'docker',
            'kubernetes',
            'aws',
            'gcp',
            'azure',
            'terraform',
            'ansible',
            'jenkins',
            'github-actions',
            
            // Concepts & Practices
            'microservices',
            'api-design',
            'testing',
            'security',
            'monitoring',
            'logging',
            'caching',
            'scalability',
            'performance',
            'optimization',
            'best-practices',
            'design-patterns',
            
            // Tools & Technologies
            'git',
            'linux',
            'nginx',
            'apache',
            'load-balancing',
            'message-queues',
            'websockets',
            'graphql',
            'rest-api',
            
            // Valkey Specific
            'data-structures',
            'key-value-store',
            'in-memory-database',
            'clustering',
            'replication',
            'persistence',
            'pub-sub',
            'lua-scripting',
        ];

        foreach ($tags as $tagName) {
            Tag::create(['name' => $tagName]);
        }

        // Create some additional random tags
        Tag::factory(10)->create();
    }
}