import { useState } from 'react';
import { Link } from '@inertiajs/react';
import { SidebarData } from '@/types';

interface BlogSidebarProps {
  sidebar: SidebarData;
}

export default function BlogSidebar({ sidebar }: BlogSidebarProps) {
  const [isOpen, setIsOpen] = useState(false);

  return (
    <aside className="lg:sticky lg:top-4 lg:self-start" role="complementary" aria-label="Blog sidebar">
      {/* Mobile toggle button */}
      <div className="lg:hidden mb-4">
        <button
          onClick={() => setIsOpen(!isOpen)}
          className="w-full flex items-center justify-between p-3 text-left bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 dark:focus:ring-blue-400"
          aria-expanded={isOpen}
          aria-controls="sidebar-content"
        >
          <span className="font-medium text-gray-900 dark:text-gray-100">
            Sidebar
          </span>
          <svg
            className={`w-5 h-5 text-gray-500 transition-transform duration-200 ${
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
      >
        {/* About Section */}
        <section className="bg-gray-50 p-4 rounded-lg dark:bg-gray-800">
          <h2 className="text-lg font-semibold text-gray-900 mb-3 dark:text-gray-100">
            About
          </h2>
          <p className="text-sm text-gray-600 leading-relaxed dark:text-gray-300">
            {sidebar.aboutText}
          </p>
        </section>

        {/* Recent Posts Section */}
        {sidebar.recentPosts.length > 0 && (
          <section>
            <h2 className="text-lg font-semibold text-gray-900 mb-4 dark:text-gray-100">
              Recent posts
            </h2>
            <div className="space-y-3">
              {sidebar.recentPosts.map((post, index) => (
                <article key={index} className="flex space-x-3">
                  {/* Thumbnail placeholder */}
                  <div className="flex-shrink-0 w-16 h-16 bg-gray-200 rounded-lg overflow-hidden dark:bg-gray-700">
                    {post.thumbnailUrl ? (
                      <img
                        src={post.thumbnailUrl}
                        alt=""
                        className="w-full h-full object-cover"
                      />
                    ) : (
                      <div className="w-full h-full flex items-center justify-center">
                        <svg
                          className="w-6 h-6 text-gray-400 dark:text-gray-500"
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
                    <h3 className="text-sm font-medium text-gray-900 dark:text-gray-100">
                      <Link
                        href={post.url}
                        className="hover:text-blue-600 transition-colors duration-200 dark:hover:text-blue-400"
                      >
                        <span className="line-clamp-2">
                          {post.title}
                        </span>
                      </Link>
                    </h3>
                    <time className="text-xs text-gray-500 dark:text-gray-400 mt-1 block">
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
          <section>
            <h2 className="text-lg font-semibold text-gray-900 mb-4 dark:text-gray-100">
              Archives
            </h2>
            <nav aria-label="Archive navigation">
              <ul className="space-y-2">
                {sidebar.archives.map((archive, index) => (
                  <li key={index}>
                    <Link
                      href={archive.url}
                      className="text-sm text-blue-600 hover:text-blue-800 hover:underline transition-colors duration-200 dark:text-blue-400 dark:hover:text-blue-300"
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
          <section>
            <h2 className="text-lg font-semibold text-gray-900 mb-4 dark:text-gray-100">
              Elsewhere
            </h2>
            <nav aria-label="External links">
              <ul className="space-y-2">
                {sidebar.externalLinks.map((link, index) => (
                  <li key={index}>
                    <a
                      href={link.url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 hover:underline transition-colors duration-200 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                      {link.label}
                      <svg
                        className="ml-1 w-3 h-3"
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
