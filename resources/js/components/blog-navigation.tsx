import { useState, useRef, useEffect } from 'react';

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
    const [focusedIndex, setFocusedIndex] = useState<number>(-1);
    const containerRef = useRef<HTMLDivElement>(null);
    const buttonRefs = useRef<(HTMLButtonElement | null)[]>([]);

    const handleCategoryClick = (category: string) => {
        if (onCategoryChange) {
            onCategoryChange(category);
        }
    };

    const handleKeyDown = (event: React.KeyboardEvent, index: number) => {
        switch (event.key) {
            case 'ArrowLeft':
                event.preventDefault();
                const prevIndex = index > 0 ? index - 1 : categories.length - 1;
                setFocusedIndex(prevIndex);
                buttonRefs.current[prevIndex]?.focus();
                break;
            case 'ArrowRight':
                event.preventDefault();
                const nextIndex = index < categories.length - 1 ? index + 1 : 0;
                setFocusedIndex(nextIndex);
                buttonRefs.current[nextIndex]?.focus();
                break;
            case 'Home':
                event.preventDefault();
                setFocusedIndex(0);
                buttonRefs.current[0]?.focus();
                break;
            case 'End':
                event.preventDefault();
                const lastIndex = categories.length - 1;
                setFocusedIndex(lastIndex);
                buttonRefs.current[lastIndex]?.focus();
                break;
            case 'Enter':
            case ' ':
                event.preventDefault();
                handleCategoryClick(categories[index]);
                break;
        }
    };

    // Scroll focused element into view
    useEffect(() => {
        if (focusedIndex >= 0 && buttonRefs.current[focusedIndex]) {
            buttonRefs.current[focusedIndex]?.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'center'
            });
        }
    }, [focusedIndex]);

    return (
        <nav
            className="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 sticky top-0 z-10"
            aria-label="Blog categories navigation"
            role="navigation"
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
                    ref={containerRef}
                    className="flex overflow-x-auto py-2 sm:py-3 scrollbar-hide scroll-smooth"
                    role="tablist"
                    aria-label="Category filters"
                    aria-orientation="horizontal"
                >
                    {categories.map((category, index) => {
                        const isActive = activeCategory === category;
                        const isHovered = hoveredCategory === category;
                        const isFocused = focusedIndex === index;

                        return (
                            <button
                                key={category}
                                ref={(el) => (buttonRefs.current[index] = el)}
                                type="button"
                                role="tab"
                                tabIndex={isActive ? 0 : -1}
                                aria-selected={isActive}
                                aria-controls={`category-${category.toLowerCase().replace(/\s+/g, '-')}-panel`}
                                aria-label={`Filter by ${category} category`}
                                onClick={() => handleCategoryClick(category)}
                                onKeyDown={(e) => handleKeyDown(e, index)}
                                onMouseEnter={() => setHoveredCategory(category)}
                                onMouseLeave={() => setHoveredCategory(null)}
                                onFocus={() => setFocusedIndex(index)}
                                onBlur={() => setFocusedIndex(-1)}
                                className={`
                                    relative px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base font-medium whitespace-nowrap
                                    transition-colors duration-200 ease-in-out
                                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md
                                    dark:focus:ring-offset-gray-900 dark:focus:ring-blue-400
                                    min-w-0 flex-shrink-0
                                    touch-manipulation
                                    min-h-[44px] flex items-center
                                    ${isActive
                                        ? 'text-gray-900 dark:text-gray-100'
                                        : 'text-gray-600 dark:text-gray-400'
                                    }
                                    ${(isHovered || isFocused) && !isActive
                                        ? 'text-gray-900 dark:text-gray-100'
                                        : ''
                                    }
                                    hover:bg-gray-50 dark:hover:bg-gray-800
                                    active:bg-gray-100 dark:active:bg-gray-700
                                    focus:bg-gray-50 dark:focus:bg-gray-800
                                `}
                            >
                                {category}

                                {/* Active indicator */}
                                {isActive && (
                                    <span
                                        className="absolute bottom-0 left-0 right-0 h-0.5 bg-gray-900 dark:bg-gray-100 transition-all duration-200"
                                        aria-hidden="true"
                                    />
                                )}

                                {/* Hover/Focus indicator */}
                                {(isHovered || isFocused) && !isActive && (
                                    <span
                                        className="absolute bottom-0 left-0 right-0 h-0.5 bg-gray-400 dark:bg-gray-500 opacity-50 transition-all duration-200"
                                        aria-hidden="true"
                                    />
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
