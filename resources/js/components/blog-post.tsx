import React from 'react';
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

    return (
        <article className="mb-8 pb-8 border-b border-gray-200 last:border-b-0 dark:border-gray-700">
            {/* Post Header */}
            <header className="mb-6">
                <h2 className="text-2xl md:text-3xl font-serif font-bold text-gray-900 dark:text-gray-100 mb-3 leading-tight">
                    {post.title}
                </h2>
                <div className="flex items-center text-sm text-gray-600 dark:text-gray-400">
                    <span className="font-medium">By {post.author}</span>
                    <span className="mx-2 text-gray-400 dark:text-gray-500">•</span>
                    <time dateTime={post.date} className="text-gray-500 dark:text-gray-400">
                        {formatDate(post.date)}
                    </time>
                </div>
            </header>

            {/* Post Content */}
            <div
                className="blog-content max-w-none text-gray-700 dark:text-gray-300 leading-relaxed"
                dangerouslySetInnerHTML={{ __html: post.content }}
            />
        </article>
    );
}
