import { Head } from '@inertiajs/react';
import { BlogData } from '@/types';
import BlogHeader from '@/components/blog-header';
import BlogNavigation from '@/components/blog-navigation';
import FeaturedPost from '@/components/featured-post';
import PostCard from '@/components/post-card';
import BlogPost from '@/components/blog-post';
import BlogSidebar from '@/components/blog-sidebar';

interface BlogPageProps {
    blog: BlogData;
}

export default function BlogIndex({ blog }: BlogPageProps) {
    return (
        <>
            <Head title="Blog" />

            <div className="min-h-screen bg-white dark:bg-gray-900">
                {/* Blog Header */}
                <BlogHeader siteName={blog.siteName} />

                {/* Blog Navigation */}
                <BlogNavigation
                    categories={blog.categories}
                    activeCategory="World" // Default active category - could be dynamic
                />

                {/* Main Content Area */}
                <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div className="grid grid-cols-1 lg:grid-cols-4 gap-8">
                        {/* Content Column */}
                        <div className="lg:col-span-3">
                            {/* Featured Post Section */}
                            <section className="mb-8">
                                <FeaturedPost post={blog.featuredPost} />
                            </section>

                            {/* Secondary Featured Posts */}
                            <section className="mb-8">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {blog.secondaryPosts.map((post) => (
                                        <PostCard key={post.id} post={post} />
                                    ))}
                                </div>
                            </section>

                            {/* Main Blog Posts Section */}
                            <section className="mb-8">
                                <h2 className="text-2xl font-serif font-bold mb-6 text-gray-900 dark:text-gray-100">
                                    From the Firehose
                                </h2>
                                <div>
                                    {blog.mainPosts.map((post) => (
                                        <BlogPost key={post.id} post={post} />
                                    ))}
                                </div>
                            </section>

                            {/* Pagination */}
                            <nav className="flex justify-between items-center">
                                <button
                                    type="button"
                                    className="px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700"
                                    disabled
                                >
                                    Older
                                </button>
                                <button
                                    type="button"
                                    className="px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700"
                                    disabled
                                >
                                    Newer
                                </button>
                            </nav>
                        </div>

                        {/* Sidebar */}
                        <div className="lg:col-span-1">
                            <BlogSidebar sidebar={blog.sidebar} />
                        </div>
                    </div>
                </main>
            </div>
        </>
    );
}
