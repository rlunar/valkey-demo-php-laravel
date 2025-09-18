import { Link } from '@inertiajs/react';
import { FeaturedPostData } from '@/types';

interface FeaturedPostProps {
    post: FeaturedPostData;
}

export default function FeaturedPost({ post }: FeaturedPostProps) {
    return (
        <article className="p-6 md:p-8 mb-6 rounded-lg bg-gray-100 dark:bg-gray-800">
            <div className="max-w-4xl">
                <h1 className="text-3xl md:text-4xl lg:text-5xl font-serif font-bold italic mb-4 text-gray-900 dark:text-gray-100 leading-tight">
                    {post.title}
                </h1>
                <p className="text-lg md:text-xl text-gray-700 dark:text-gray-300 mb-6 leading-relaxed">
                    {post.excerpt}
                </p>
                <Link
                    href={post.readMoreUrl}
                    className="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors duration-200"
                >
                    Continue reading
                    <svg
                        className="ml-2 w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </Link>
            </div>
        </article>
    );
}
