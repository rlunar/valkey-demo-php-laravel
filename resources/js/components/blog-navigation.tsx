import { useState } from 'react';

interface BlogNavigationProps {
    categories: string[];
    activeCategory?: string;
    onCategoryChange?: (category: string) => void;
}

export default function BlogNavigation({
    categories,
    activeCategory,
    onCategoryChange
}: BlogNavigationProps) {
    const [hoveredCategory, setHoveredCategory] = useState<string | null>(null);

    const handleCategoryClick = (category: string) => {
        if (onCategoryChange) {
            onCategoryChange(category);
        }
    };

    return (
        <nav
            className="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 sticky top-0 z-10"
            aria-label="Blog categories navigation"
        >
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                {/* Mobile: Show scroll hint */}
                <div
                    className="sm:hidden text-xs text-gray-500 dark:text-gray-400 py-1 text-center"
                    aria-hidden="true"
                >
                    Swipe to see more categories
                </div>

                <div
                    className="flex overflow-x-auto py-2 scrollbar-hide scroll-smooth"
                    role="tablist"
                    aria-label="Category filters"
                >
                    {categories.map((category) => {
                        const isActive = activeCategory === category;
                        const isHovered = hoveredCategory === category;

                        return (
                            <button
                                key={category}
                                type="button"
                                role="tab"
                                aria-selected={isActive}
                                aria-controls={`category-${category.toLowerCase().replace(/\s+/g, '-')}-panel`}
                                onClick={() => handleCategoryClick(category)}
                                onMouseEnter={() => setHoveredCategory(category)}
                                onMouseLeave={() => setHoveredCategory(null)}
                                className={`
                                    relative px-3 py-2 text-sm font-medium whitespace-nowrap
                                    transition-colors duration-200 ease-in-out
                                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                                    dark:focus:ring-offset-gray-900
                                    min-w-0 flex-shrink-0
                                    touch-manipulation
                                    ${isActive
                                        ? 'text-gray-900 dark:text-gray-100'
                                        : 'text-gray-600 dark:text-gray-400'
                                    }
                                    ${isHovered && !isActive
                                        ? 'text-gray-900 dark:text-gray-100'
                                        : ''
                                    }
                                    hover:bg-gray-50 dark:hover:bg-gray-800
                                    active:bg-gray-100 dark:active:bg-gray-700
                                `}
                            >
                                {category}

                                {/* Active indicator */}
                                {isActive && (
                                    <span className="absolute bottom-0 left-0 right-0 h-0.5 bg-gray-900 dark:bg-gray-100 transition-all duration-200" />
                                )}

                                {/* Hover indicator */}
                                {isHovered && !isActive && (
                                    <span className="absolute bottom-0 left-0 right-0 h-0.5 bg-gray-400 dark:bg-gray-500 opacity-50 transition-all duration-200" />
                                )}
                            </button>
                        );
                    })}
                </div>

                {/* Fade indicators for scrollable content */}
                <div
                    className="absolute left-0 top-0 bottom-0 w-8 bg-gradient-to-r from-white to-transparent dark:from-gray-900 pointer-events-none sm:hidden"
                    aria-hidden="true"
                />
                <div
                    className="absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-white to-transparent dark:from-gray-900 pointer-events-none sm:hidden"
                    aria-hidden="true"
                />
            </div>


        </nav>
    );
}
