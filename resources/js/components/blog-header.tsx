import React from 'react';

interface BlogHeaderProps {
    siteName: string;
}

export default function BlogHeader({ siteName }: BlogHeaderProps) {
    return (
        <header className="border-b border-gray-200 py-3 sm:py-4 dark:border-gray-700" role="banner">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex items-center justify-between min-h-[44px]">
                    {/* Subscribe Link - Left Column */}
                    <div className="flex-1">
                        <a
                            href="#"
                            className="inline-block text-sm sm:text-base font-medium text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md px-2 py-2 -mx-2 -my-2 touch-manipulation dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-blue-400"
                            aria-label="Subscribe to newsletter"
                        >
                            Subscribe
                        </a>
                    </div>

                    {/* Site Logo/Title - Center Column */}
                    <div className="flex-1 text-center px-2">
                        <h1 className="text-xl sm:text-2xl lg:text-3xl font-serif font-bold text-gray-900 dark:text-gray-100 leading-tight">
                            <a
                                href="/blog"
                                className="hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md px-2 py-1 -mx-2 -my-1 touch-manipulation dark:hover:text-gray-300 dark:focus:ring-blue-400"
                                aria-label={`${siteName} - Home`}
                            >
                                {siteName}
                            </a>
                        </h1>
                    </div>

                    {/* Search and Sign Up - Right Column */}
                    <div className="flex-1 flex items-center justify-end space-x-2 sm:space-x-4">
                        <button
                            type="button"
                            className="text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md p-2 touch-manipulation dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-blue-400"
                            aria-label="Search"
                        >
                            <svg
                                className="w-5 h-5 sm:w-6 sm:h-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </button>
                        <a
                            href="#"
                            className="inline-block text-sm sm:text-base font-medium text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md px-2 py-2 -mx-2 -my-2 touch-manipulation dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-blue-400"
                            aria-label="Sign up for account"
                        >
                            Sign up
                        </a>
                    </div>
                </div>
            </div>
        </header>
    );
}
