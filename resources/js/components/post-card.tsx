import { Link } from '@inertiajs/react';
import { PostCardData } from '@/types';

interface PostCardProps {
  post: PostCardData;
}

export default function PostCard({ post }: PostCardProps) {
  return (
    <article className="flex rounded-lg border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200 dark:border-gray-700 dark:bg-gray-800">
      {/* Thumbnail - hidden on mobile, visible on larger screens */}
      {post.thumbnailUrl && (
        <div className="hidden md:block w-48 h-32 bg-gray-300 flex-shrink-0 dark:bg-gray-600">
          <img
            src={post.thumbnailUrl}
            alt=""
            className="w-full h-full object-cover"
          />
        </div>
      )}

      {/* Content */}
      <div className="flex-1 p-4 md:p-6">
        {/* Category badge */}
        <div className="mb-2">
          <span className="inline-block px-2 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-full dark:text-blue-400 dark:bg-blue-900/30">
            {post.category}
          </span>
        </div>

        {/* Title */}
        <h3 className="mb-2">
          <Link
            href={post.readMoreUrl}
            className="text-lg font-semibold text-gray-900 hover:text-blue-600 transition-colors duration-200 dark:text-gray-100 dark:hover:text-blue-400"
          >
            {post.title}
          </Link>
        </h3>

        {/* Date */}
        <time className="text-sm text-gray-500 dark:text-gray-400 mb-3 block">
          {post.date}
        </time>

        {/* Excerpt */}
        <p className="text-gray-600 dark:text-gray-300 text-sm leading-relaxed mb-4 overflow-hidden">
          <span className="block overflow-hidden text-ellipsis" style={{
            display: '-webkit-box',
            WebkitLineClamp: 3,
            WebkitBoxOrient: 'vertical'
          }}>
            {post.excerpt}
          </span>
        </p>

        {/* Read more link */}
        <Link
          href={post.readMoreUrl}
          className="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors duration-200 dark:text-blue-400 dark:hover:text-blue-300"
        >
          Continue reading
          <svg
            className="ml-1 w-4 h-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
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
