import { BlogPostData } from '@/types';

interface BlogPostProps {
    post: BlogPostData;
}

export default function BlogPost({ post }: BlogPostProps) {
    // Format the date for better display
    const formatDate = (dateString: string) => {
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        } catch {
            return dateString;
        }
    };

    // Generate ISO date string for datetime attribute
    const getISODate = (dateString: string) => {
        try {
            const date = new Date(dateString);
            return date.toISOString().split('T')[0];
        } catch {
            return dateString;
        }
    };

    return (
        <article
            className="mb-6 sm:mb-8 pb-6 sm:pb-8 border-b border-gray-200 last:border-b-0 dark:border-gray-700"
            aria-labelledby={`post-title-${post.id}`}
        >
            {/* Post Header */}
            <header className="mb-4 sm:mb-6">
                <h2
                    id={`post-title-${post.id}`}
                    className="text-xl sm:text-2xl md:text-3xl font-serif font-bold text-gray-900 dark:text-gray-100 mb-3 leading-tight"
                >
                    {post.title}
                </h2>
                <div className="flex flex-col sm:flex-row sm:items-center text-sm sm:text-base text-gray-600 dark:text-gray-400 gap-1 sm:gap-0">
                    <span className="font-medium">
                        <span className="sr-only">Author: </span>
                        By {post.author}
                    </span>
                    <span className="hidden sm:inline mx-2 text-gray-400 dark:text-gray-500" aria-hidden="true">•</span>
                    <time
                        dateTime={getISODate(post.date)}
                        className="text-gray-500 dark:text-gray-400"
                        aria-label={`Published on ${formatDate(post.date)}`}
                    >
                        {formatDate(post.date)}
                    </time>
                </div>
            </header>

            {/* Post Content */}
            <div
                className="blog-content max-w-none text-gray-700 dark:text-gray-300 leading-relaxed text-sm sm:text-base prose prose-gray dark:prose-invert max-w-none"
                dangerouslySetInnerHTML={{ __html: post.content }}
                role="main"
                aria-label="Article content"
            />
        </article>
    );
}
