import { Head } from '@inertiajs/react';
import { BlogData } from '@/types';
import BlogHeader from '@/components/blog-header';
import BlogNavigation from '@/components/blog-navigation';
import FeaturedPost from '@/components/featured-post';
import PostCard from '@/components/post-card';
import BlogPost from '@/components/blog-post';
import BlogSidebar from '@/components/blog-sidebar';
import { BlogPagination } from '@/components/blog-pagination';

interface BlogPageProps {
    blog: BlogData;
}

export default function BlogIndex({ blog }: BlogPageProps) {
    return (
        <>
            <Head title={`${blog.siteName} - Blog`}>
                <meta name="description" content="A modern blog built with Laravel, React, and Tailwind CSS" />
                <meta name="viewport" content="width=device-width, initial-scale=1" />
                <meta property="og:title" content={`${blog.siteName} - Blog`} />
                <meta property="og:description" content="A modern blog built with Laravel, React, and Tailwind CSS" />
                <meta property="og:type" content="website" />
            </Head>

            <div className="min-h-screen bg-white dark:bg-gray-900 transition-colors duration-200">
                {/* Skip Navigation Link */}
                <a
                    href="#main-content"
                    className="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-blue-600 text-white px-4 py-2 rounded-md z-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    Skip to main content
                </a>

                {/* Blog Header */}
                <BlogHeader siteName={blog.siteName} />

                {/* Blog Navigation */}
                <BlogNavigation
                    categories={blog.categories}
                    activeCategory="World" // Default active category - could be dynamic
                />

                {/* Main Content Area */}
                <main
                    id="main-content"
                    className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8"
                    role="main"
                    aria-label="Blog content"
                >
                    {/* Responsive Layout: Stack on mobile, grid on desktop */}
                    <div className="grid grid-cols-1 lg:grid-cols-4 gap-4 sm:gap-6 lg:gap-8">
                        {/* Main Content Column */}
                        <div className="lg:col-span-3 space-y-4 sm:space-y-6 lg:space-y-8">
                            {/* Featured Post Section */}
                            {blog.featuredPost && (
                                <section aria-labelledby="featured-heading">
                                    <h2 id="featured-heading" className="sr-only">Featured Post</h2>
                                    <FeaturedPost post={blog.featuredPost} />
                                </section>
                            )}

                            {/* Secondary Featured Posts */}
                            {blog.secondaryPosts && blog.secondaryPosts.length > 0 && (
                                <section aria-labelledby="secondary-posts-heading">
                                    <h2 id="secondary-posts-heading" className="sr-only">Featured Posts</h2>
                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                        {blog.secondaryPosts.map((post) => (
                                            <PostCard key={post.id} post={post} />
                                        ))}
                                    </div>
                                </section>
                            )}

                            {/* Main Blog Posts Section */}
                            {blog.mainPosts && blog.mainPosts.length > 0 && (
                                <section aria-labelledby="main-posts-heading">
                                    <h2
                                        id="main-posts-heading"
                                        className="text-xl sm:text-2xl md:text-3xl font-serif font-bold mb-4 sm:mb-6 text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2"
                                    >
                                        From the Firehose
                                    </h2>
                                    <div className="space-y-4 sm:space-y-6 lg:space-y-8">
                                        {blog.mainPosts.map((post) => (
                                            <BlogPost key={post.id} post={post} />
                                        ))}
                                    </div>
                                </section>
                            )}

                            {/* Pagination */}
                            {blog.pagination && (blog.pagination.hasOlder || blog.pagination.hasNewer) && (
                                <section aria-label="Blog pagination">
                                    <BlogPagination
                                        hasOlder={blog.pagination.hasOlder}
                                        hasNewer={blog.pagination.hasNewer}
                                        olderUrl={blog.pagination.olderUrl}
                                        newerUrl={blog.pagination.newerUrl}
                                    />
                                </section>
                            )}
                        </div>

                        {/* Sidebar */}
                        <aside className="lg:col-span-1 order-first lg:order-last">
                            <BlogSidebar sidebar={blog.sidebar} />
                        </aside>
                    </div>
                </main>

                {/* Footer */}
                <footer className="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 mt-8 sm:mt-12">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
                        <div className="text-center text-sm sm:text-base text-gray-600 dark:text-gray-400">
                            <p>&copy; 2024 {blog.siteName}. Built with Laravel, React, and Tailwind CSS.</p>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
