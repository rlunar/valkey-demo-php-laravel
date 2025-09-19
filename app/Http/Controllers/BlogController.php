<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    /**
     * Display the blog landing page.
     */
    public function index(): Response
    {
        $blogData = $this->getMockBlogData();

        return Inertia::render('blog/index', [
            'blog' => $blogData,
        ]);
    }

    /**
     * Get mock data for the blog page.
     * In a real application, this would fetch data from a database.
     */
    private function getMockBlogData(): array
    {
        return [
            'siteName' => 'Large',
            'categories' => [
                'World',
                'U.S.',
                'Technology',
                'Design',
                'Culture',
                'Business',
                'Politics',
                'Opinion',
                'Science',
                'Health',
                'Style',
                'Travel'
            ],
            'featuredPost' => [
                'title' => 'The Future of Web Development: Trends to Watch in 2024',
                'excerpt' => 'Explore the latest trends shaping web development, from AI-powered tools to advanced frameworks. This comprehensive guide covers everything you need to know about the evolving landscape of modern web development.',
                'readMoreUrl' => '/blog/future-web-development-2024'
            ],
            'secondaryPosts' => [
                [
                    'id' => '1',
                    'title' => 'Building Scalable React Applications',
                    'category' => 'Technology',
                    'date' => 'Nov 12, 2024',
                    'excerpt' => 'Learn best practices for building large-scale React applications that can grow with your team and user base. We\'ll cover architecture patterns, state management, and performance optimization.',
                    'readMoreUrl' => '/blog/scalable-react-applications',
                    'thumbnailUrl' => null
                ],
                [
                    'id' => '2',
                    'title' => 'The Art of Modern CSS Design',
                    'category' => 'Design',
                    'date' => 'Nov 11, 2024',
                    'excerpt' => 'Discover how modern CSS features like Grid, Flexbox, and custom properties are revolutionizing web design. Create beautiful, responsive layouts with less code.',
                    'readMoreUrl' => '/blog/modern-css-design',
                    'thumbnailUrl' => null
                ]
            ],
            'mainPosts' => [
                [
                    'id' => '3',
                    'title' => 'Getting Started with Laravel and Inertia.js',
                    'author' => 'Sarah Johnson',
                    'date' => '2024-01-15',
                    'content' => '<p>Laravel and Inertia.js provide a powerful combination for building modern web applications. This comprehensive guide will walk you through setting up your first project and understanding the core concepts.</p>

<h2>What is Inertia.js?</h2>
<p>Inertia.js is a protocol that allows you to build single-page applications using classic server-side routing and controllers. It works as a glue between your backend framework and your frontend JavaScript framework.</p>

<blockquote>
<p>Inertia isn\'t a framework, nor is it a replacement for your existing server-side or client-side frameworks. Rather, it\'s designed to work with them. Think of Inertia as glue that connects the two.</p>
</blockquote>

<h3>Key Benefits</h3>
<ul>
<li>No need to build an API for your frontend</li>
<li>Server-side routing and controllers</li>
<li>Automatic code splitting</li>
<li>Built-in progress indicators</li>
</ul>

<h3>Installation</h3>
<p>First, install Inertia\'s server-side adapter:</p>
<pre><code>composer require inertiajs/inertia-laravel</code></pre>

<p>Then publish the Inertia middleware:</p>
<pre><code>php artisan inertia:middleware</code></pre>

<h2>Setting Up Your First Page</h2>
<p>Create a controller that returns an Inertia response:</p>

<pre><code>use Inertia\\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render(\'Dashboard\', [
            \'user\' => auth()->user(),
            \'stats\' => $this->getStats(),
        ]);
    }
}</code></pre>

<p>This approach gives you the best of both worlds: the simplicity of server-side rendering with the interactivity of a single-page application.</p>'
                ],
                [
                    'id' => '4',
                    'title' => 'Mastering Tailwind CSS: Advanced Techniques',
                    'author' => 'Michael Chen',
                    'date' => '2024-01-10',
                    'content' => '<p>Tailwind CSS has revolutionized how we approach styling in modern web development. While many developers are familiar with the basics, there are advanced techniques that can significantly improve your workflow and code quality.</p>

<h2>Custom Component Classes</h2>
<p>One of the most powerful features of Tailwind is the ability to create custom component classes using the <code>@apply</code> directive:</p>

<pre><code>.btn-primary {
  @apply bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded;
}</code></pre>

<blockquote>
<p>Use <code>@apply</code> sparingly. Most of the time, it\'s better to use utility classes directly in your HTML for better maintainability.</p>
</blockquote>

<h3>Dynamic Class Generation</h3>
<p>Tailwind\'s JIT (Just-In-Time) compiler allows you to generate classes dynamically:</p>

<ol>
<li>Configure your <code>tailwind.config.js</code> properly</li>
<li>Use arbitrary value syntax: <code>w-[32rem]</code></li>
<li>Leverage CSS variables for dynamic values</li>
</ol>

<p>This flexibility makes Tailwind incredibly powerful for component-based architectures where you need precise control over styling.</p>'
                ],
                [
                    'id' => '5',
                    'title' => 'TypeScript Best Practices for React Development',
                    'author' => 'Emily Rodriguez',
                    'date' => '2024-01-05',
                    'content' => '<p>TypeScript has become an essential tool for React development, providing type safety and better developer experience. Here are some best practices to help you write better TypeScript React code.</p>

<h2>Interface vs Type Aliases</h2>
<p>When defining component props, prefer interfaces over type aliases for better extensibility:</p>

<pre><code>// Preferred
interface ButtonProps {
  variant: \'primary\' | \'secondary\';
  children: React.ReactNode;
}

// Less preferred for props
type ButtonProps = {
  variant: \'primary\' | \'secondary\';
  children: React.ReactNode;
}</code></pre>

<h3>Generic Components</h3>
<p>Create reusable components with generics for better type safety:</p>

<pre><code>interface ListProps&lt;T&gt; {
  items: T[];
  renderItem: (item: T) =&gt; React.ReactNode;
}

function List&lt;T&gt;({ items, renderItem }: ListProps&lt;T&gt;) {
  return (
    &lt;ul&gt;
      {items.map((item, index) =&gt; (
        &lt;li key={index}&gt;{renderItem(item)}&lt;/li&gt;
      ))}
    &lt;/ul&gt;
  );
}</code></pre>

<p>These patterns help create more maintainable and type-safe React applications.</p>'
                ]
            ],
            'sidebar' => [
                'aboutText' => 'Welcome to Large, a modern blog exploring the latest trends in web development, design, and technology. We share insights, tutorials, and best practices to help developers build better applications.',
                'recentPosts' => [
                    [
                        'title' => 'Getting Started with Laravel and Inertia.js',
                        'date' => 'January 15, 2024',
                        'url' => '/blog/laravel-inertia-getting-started',
                        'thumbnailUrl' => null
                    ],
                    [
                        'title' => 'Mastering Tailwind CSS: Advanced Techniques',
                        'date' => 'January 10, 2024',
                        'url' => '/blog/tailwind-advanced-techniques',
                        'thumbnailUrl' => null
                    ],
                    [
                        'title' => 'TypeScript Best Practices for React Development',
                        'date' => 'January 5, 2024',
                        'url' => '/blog/typescript-react-best-practices',
                        'thumbnailUrl' => null
                    ]
                ],
                'archives' => [
                    ['label' => 'January 2024', 'url' => '/blog/archive/2024/01'],
                    ['label' => 'December 2023', 'url' => '/blog/archive/2023/12'],
                    ['label' => 'November 2023', 'url' => '/blog/archive/2023/11'],
                    ['label' => 'October 2023', 'url' => '/blog/archive/2023/10'],
                    ['label' => 'September 2023', 'url' => '/blog/archive/2023/09'],
                    ['label' => 'August 2023', 'url' => '/blog/archive/2023/08'],
                    ['label' => 'July 2023', 'url' => '/blog/archive/2023/07'],
                    ['label' => 'June 2023', 'url' => '/blog/archive/2023/06'],
                    ['label' => 'May 2023', 'url' => '/blog/archive/2023/05'],
                    ['label' => 'April 2023', 'url' => '/blog/archive/2023/04'],
                    ['label' => 'March 2023', 'url' => '/blog/archive/2023/03'],
                    ['label' => 'February 2023', 'url' => '/blog/archive/2023/02']
                ],
                'externalLinks' => [
                    ['label' => 'GitHub', 'url' => 'https://github.com'],
                    ['label' => 'Twitter', 'url' => 'https://twitter.com'],
                    ['label' => 'LinkedIn', 'url' => 'https://linkedin.com'],
                    ['label' => 'Dev.to', 'url' => 'https://dev.to']
                ],
                'weather' => [
                    'enabled' => true,
                    'defaultLocation' => [
                        'lat' => 40.7128,
                        'lon' => -74.0060,
                        'name' => 'New York, NY'
                    ]
                ]
            ],
            'pagination' => [
                'hasOlder' => true,
                'hasNewer' => false,
                'olderUrl' => '/blog?page=2',
                'newerUrl' => null
            ]
        ];
    }
}
