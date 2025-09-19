import { useState } from 'react';
import { Link } from '@inertiajs/react';
import { SidebarData } from '@/types';

interface BlogSidebarProps {
  sidebar: SidebarData;
}

export default function BlogSidebar({ sidebar }: BlogSidebarProps) {
  const [isOpen, setIsOpen] = useState(false);

  const handleToggle = () => {
    setIsOpen(!isOpen);
  };

  const handleKeyDown = (event: React.KeyboardEvent) => {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      handleToggle();
    }
  };

  return (
    <aside className="lg:sticky lg:top-4 lg:self-start" role="complementary" aria-label="Blog sidebar">
      {/* Mobile toggle button */}
      <div className="lg:hidden mb-4">
        <button
          onClick={handleToggle}
          onKeyDown={handleKeyDown}
          className="w-full flex items-center justify-between p-3 sm:p-4 text-left bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 dark:focus:ring-blue-400 dark:focus:ring-offset-gray-900 touch-manipulation min-h-[44px]"
          aria-expanded={isOpen}
          aria-controls="sidebar-content"
          aria-label={`${isOpen ? 'Hide' : 'Show'} sidebar content`}
        >
          <span className="font-medium text-gray-900 dark:text-gray-100 text-base sm:text-lg">
            Sidebar
          </span>
          <svg
            className={`w-5 h-5 sm:w-6 sm:h-6 text-gray-500 transition-transform duration-200 ${
              isOpen ? 'rotate-180' : ''
            }`}
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            aria-hidden="true"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M19 9l-7 7-7-7"
            />
          </svg>
        </button>
      </div>

      {/* Sidebar content */}
      <div
        id="sidebar-content"
        className={`space-y-6 ${
          isOpen ? 'block' : 'hidden'
        } lg:block`}
        aria-hidden={!isOpen && 'true'}
      >
        {/* About Section */}
        <section className="bg-gray-50 p-4 sm:p-5 rounded-lg dark:bg-gray-800">
          <h2 className="text-lg sm:text-xl font-semibold text-gray-900 mb-3 dark:text-gray-100">
            About
          </h2>
          <p className="text-sm sm:text-base text-gray-600 leading-relaxed dark:text-gray-300">
            {sidebar.aboutText}
          </p>
        </section>

        {/* Recent Posts Section */}
        {sidebar.recentPosts.length > 0 && (
          <section aria-labelledby="recent-posts-heading">
            <h2 id="recent-posts-heading" className="text-lg sm:text-xl font-semibold text-gray-900 mb-4 dark:text-gray-100">
              Recent posts
            </h2>
            <div className="space-y-4" role="list">
              {sidebar.recentPosts.map((post, index) => (
                <article key={index} className="flex space-x-3 sm:space-x-4" role="listitem">
                  {/* Thumbnail placeholder */}
                  <div className="flex-shrink-0 w-16 h-16 sm:w-20 sm:h-20 bg-gray-200 rounded-lg overflow-hidden dark:bg-gray-700">
                    {post.thumbnailUrl ? (
                      <img
                        src={post.thumbnailUrl}
                        alt={`Thumbnail for ${post.title}`}
                        className="w-full h-full object-cover"
                        loading="lazy"
                        sizes="(max-width: 640px) 64px, 80px"
                      />
                    ) : (
                      <div className="w-full h-full flex items-center justify-center" aria-label="No image available">
                        <svg
                          className="w-6 h-6 sm:w-8 sm:h-8 text-gray-400 dark:text-gray-500"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                          aria-hidden="true"
                        >
                          <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                          />
                        </svg>
                      </div>
                    )}
                  </div>

                  {/* Post info */}
                  <div className="flex-1 min-w-0">
                    <h3 className="text-sm sm:text-base font-medium text-gray-900 dark:text-gray-100">
                      <Link
                        href={post.url}
                        className="hover:text-blue-600 transition-colors duration-200 dark:hover:text-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-sm dark:focus:ring-blue-400 dark:focus:ring-offset-gray-800"
                        aria-label={`Read recent post: ${post.title}`}
                      >
                        <span className="line-clamp-2">
                          {post.title}
                        </span>
                      </Link>
                    </h3>
                    <time
                      className="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 block"
                      dateTime={post.date}
                      aria-label={`Published ${post.date}`}
                    >
                      {post.date}
                    </time>
                  </div>
                </article>
              ))}
            </div>
          </section>
        )}

        {/* Archives Section */}
        {sidebar.archives.length > 0 && (
          <section aria-labelledby="archives-heading">
            <h2 id="archives-heading" className="text-lg sm:text-xl font-semibold text-gray-900 mb-4 dark:text-gray-100">
              Archives
            </h2>
            <nav aria-labelledby="archives-heading">
              <ul className="space-y-3" role="list">
                {sidebar.archives.map((archive, index) => (
                  <li key={index} role="listitem">
                    <Link
                      href={archive.url}
                      className="inline-block text-sm sm:text-base text-blue-600 hover:text-blue-800 hover:underline transition-colors duration-200 dark:text-blue-400 dark:hover:text-blue-300 py-1 touch-manipulation focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-sm dark:focus:ring-blue-400 dark:focus:ring-offset-gray-800"
                      aria-label={`View archive for ${archive.label}`}
                    >
                      {archive.label}
                    </Link>
                  </li>
                ))}
              </ul>
            </nav>
          </section>
        )}

        {/* External Links Section */}
        {sidebar.externalLinks.length > 0 && (
          <section aria-labelledby="external-links-heading">
            <h2 id="external-links-heading" className="text-lg sm:text-xl font-semibold text-gray-900 mb-4 dark:text-gray-100">
              Elsewhere
            </h2>
            <nav aria-labelledby="external-links-heading">
              <ul className="space-y-3" role="list">
                {sidebar.externalLinks.map((link, index) => (
                  <li key={index} role="listitem">
                    <a
                      href={link.url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center text-sm sm:text-base text-blue-600 hover:text-blue-800 hover:underline transition-colors duration-200 dark:text-blue-400 dark:hover:text-blue-300 py-1 touch-manipulation focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-sm dark:focus:ring-blue-400 dark:focus:ring-offset-gray-800"
                      aria-label={`Visit external link: ${link.label} (opens in new tab)`}
                    >
                      {link.label}
                      <svg
                        className="ml-1 w-3 h-3 sm:w-4 sm:h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={2}
                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                        />
                      </svg>
                    </a>
                  </li>
                ))}
              </ul>
            </nav>
          </section>
        )}
      </div>
    </aside>
  );
}
