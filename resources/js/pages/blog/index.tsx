import { Head } from '@inertiajs/react';
import { BlogData } from '@/types';
import BlogHeader from '@/components/blog-header';
import BlogNavigation from '@/components/blog-navigation';
import FeaturedPost from '@/components/featured-post';

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
                                        <article
                                            key={post.id}
                                            className="flex rounded-lg border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow dark:border-gray-700"
                                        >
                                            {/* Thumbnail placeholder - hidden on mobile */}
                                            <div className="w-48 h-32 bg-gray-300 flex-shrink-0 hidden lg:block dark:bg-gray-600">
                                                {post.thumbnailUrl ? (
                                                    <img
                                                        src={post.thumbnailUrl}
                                                        alt={post.title}
                                                        className="w-full h-full object-cover"
                                                    />
                                                ) : (
                                                    <div className="w-full h-full flex items-center justify-center text-gray-500 dark:text-gray-400">
                                                        <span className="text-xs">Image</span>
                                                    </div>
                                                )}
                                            </div>

                                            {/* Content */}
                                            <div className="flex-1 p-4">
                                                <div className="mb-2">
                                                    <span className="inline-block px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded dark:bg-blue-900 dark:text-blue-200">
                                                        {post.category}
                                                    </span>
                                                </div>
                                                <h3 className="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">
                                                    <a href={post.readMoreUrl} className="hover:text-blue-600 dark:hover:text-blue-400">
                                                        {post.title}
                                                    </a>
                                                </h3>
                                                <p className="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                                    {post.date}
                                                </p>
                                                <p className="text-sm text-gray-600 dark:text-gray-300">
                                                    {post.excerpt}
                                                </p>
                                            </div>
                                        </article>
                                    ))}
                                </div>
                            </section>

                            {/* Main Blog Posts Section */}
                            <section className="mb-8">
                                <h2 className="text-2xl font-serif font-bold mb-6 text-gray-900 dark:text-gray-100">
                                    From the Firehose
                                </h2>
                                <div className="space-y-8">
                                    {blog.mainPosts.map((post) => (
                                        <article key={post.id} className="border-b border-gray-200 pb-8 dark:border-gray-700">
                                            <header className="mb-4">
                                                <h3 className="text-xl font-semibold mb-2 text-gray-900 dark:text-gray-100">
                                                    {post.title}
                                                </h3>
                                                <div className="text-sm text-gray-500 dark:text-gray-400">
                                                    <span>By {post.author}</span>
                                                    <span className="mx-2">•</span>
                                                    <span>{post.date}</span>
                                                </div>
                                            </header>
                                            <div
                                                className="prose prose-gray dark:prose-invert max-w-none"
                                                dangerouslySetInnerHTML={{ __html: post.content }}
                                            />
                                        </article>
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
                        <aside className="lg:col-span-1">
                            <div className="sticky top-8 space-y-8">
                                {/* About Section */}
                                <div className="bg-gray-50 rounded-lg p-6 dark:bg-gray-800">
                                    <h3 className="text-lg font-semibold mb-3 text-gray-900 dark:text-gray-100">
                                        About
                                    </h3>
                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                        {blog.sidebar.aboutText}
                                    </p>
                                </div>

                                {/* Recent Posts */}
                                <div>
                                    <h3 className="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                                        Recent posts
                                    </h3>
                                    <div className="space-y-4">
                                        {blog.sidebar.recentPosts.map((post, index) => (
                                            <div key={index} className="flex space-x-3">
                                                <div className="w-16 h-16 bg-gray-300 rounded flex-shrink-0 dark:bg-gray-600">
                                                    {post.thumbnailUrl ? (
                                                        <img
                                                            src={post.thumbnailUrl}
                                                            alt={post.title}
                                                            className="w-full h-full object-cover rounded"
                                                        />
                                                    ) : (
                                                        <div className="w-full h-full flex items-center justify-center text-gray-500 dark:text-gray-400 rounded">
                                                            <span className="text-xs">Img</span>
                                                        </div>
                                                    )}
                                                </div>
                                                <div className="flex-1 min-w-0">
                                                    <h4 className="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        <a href={post.url} className="hover:text-blue-600 dark:hover:text-blue-400">
                                                            {post.title}
                                                        </a>
                                                    </h4>
                                                    <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                        {post.date}
                                                    </p>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                {/* Archives */}
                                <div>
                                    <h3 className="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                                        Archives
                                    </h3>
                                    <ul className="space-y-2">
                                        {blog.sidebar.archives.map((archive, index) => (
                                            <li key={index}>
                                                <a
                                                    href={archive.url}
                                                    className="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                                >
                                                    {archive.label}
                                                </a>
                                            </li>
                                        ))}
                                    </ul>
                                </div>

                                {/* External Links */}
                                <div>
                                    <h3 className="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                                        Elsewhere
                                    </h3>
                                    <ul className="space-y-2">
                                        {blog.sidebar.externalLinks.map((link, index) => (
                                            <li key={index}>
                                                <a
                                                    href={link.url}
                                                    className="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >
                                                    {link.label}
                                                </a>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            </div>
                        </aside>
                    </div>
                </main>
            </div>
        </>
    );
}
