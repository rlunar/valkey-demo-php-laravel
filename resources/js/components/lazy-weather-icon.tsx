import React, { useState, useCallback, useRef, useEffect } from 'react';
import { weatherLogger } from '@/lib/weather-logger';

interface LazyWeatherIconProps {
  icon: string;
  description: string;
  className?: string;
  fallbackClassName?: string;
  onLoad?: () => void;
  onError?: () => void;
}

const COMPONENT_NAME = 'LazyWeatherIcon';

// Cache for preloaded images
const imageCache = new Map<string, HTMLImageElement>();

// Preload commonly used weather icons
const COMMON_ICONS = ['01d', '01n', '02d', '02n', '03d', '03n', '04d', '04n', '09d', '09n', '10d', '10n', '11d', '11n', '13d', '13n', '50d', '50n'];

// Preload common icons on module load
if (typeof window !== 'undefined') {
  COMMON_ICONS.forEach(iconCode => {
    const img = new Image();
    img.src = `https://openweathermap.org/img/wn/${iconCode}@2x.png`;
    img.onload = () => {
      imageCache.set(iconCode, img);
      weatherLogger.debug(COMPONENT_NAME, `Preloaded icon: ${iconCode}`);
    };
    img.onerror = () => {
      weatherLogger.warn(COMPONENT_NAME, `Failed to preload icon: ${iconCode}`);
    };
  });
}

export const LazyWeatherIcon: React.FC<LazyWeatherIconProps> = React.memo(({
  icon,
  description,
  className = 'w-10 h-10',
  fallbackClassName = 'w-8 h-8 text-blue-600 dark:text-blue-400',
  onLoad,
  onError,
}) => {
  const [isLoaded, setIsLoaded] = useState(false);
  const [hasError, setHasError] = useState(false);
  const [isInView, setIsInView] = useState(false);
  const imgRef = useRef<HTMLImageElement>(null);
  const containerRef = useRef<HTMLDivElement>(null);

  // Intersection Observer for lazy loading
  useEffect(() => {
    const container = containerRef.current;
    if (!container || typeof window === 'undefined' || !('IntersectionObserver' in window)) {
      // Fallback: load immediately if IntersectionObserver is not supported
      setIsInView(true);
      return;
    }

    const observer = new IntersectionObserver(
      (entries) => {
        const [entry] = entries;
        if (entry.isIntersecting) {
          setIsInView(true);
          observer.disconnect();
          weatherLogger.debug(COMPONENT_NAME, `Icon ${icon} entered viewport, starting load`);
        }
      },
      {
        rootMargin: '50px', // Start loading 50px before the element is visible
        threshold: 0.1,
      }
    );

    observer.observe(container);

    return () => {
      observer.disconnect();
    };
  }, [icon]);

  // Check if image is already cached
  const isCached = imageCache.has(icon);

  // Handle image load
  const handleLoad = useCallback(() => {
    setIsLoaded(true);
    setHasError(false);
    onLoad?.();
    weatherLogger.debug(COMPONENT_NAME, `Icon ${icon} loaded successfully`);
  }, [icon, onLoad]);

  // Handle image error
  const handleError = useCallback(() => {
    setHasError(true);
    setIsLoaded(false);
    onError?.();
    weatherLogger.warn(COMPONENT_NAME, `Failed to load icon: ${icon}`);
  }, [icon, onError]);

  // Generate image URL
  const imageUrl = `https://openweathermap.org/img/wn/${icon}@2x.png`;

  // Render fallback SVG icon
  const renderFallbackIcon = () => (
    <svg
      className={fallbackClassName}
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
      aria-hidden="true"
      role="img"
      aria-label="Generic weather icon"
    >
      <path
        strokeLinecap="round"
        strokeLinejoin="round"
        strokeWidth="2"
        d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.002 4.002 0 003 15z"
      />
    </svg>
  );

  // Render loading skeleton
  const renderLoadingSkeleton = () => (
    <div
      className={`${className} bg-blue-100 dark:bg-blue-900/30 rounded animate-pulse flex items-center justify-center`}
      role="img"
      aria-label="Loading weather icon"
    >
      <div className="w-6 h-6 bg-blue-200 dark:bg-blue-800 rounded animate-pulse" />
    </div>
  );

  return (
    <div
      ref={containerRef}
      className="flex-shrink-0 flex items-center justify-center"
      role="img"
      aria-label={`Weather icon showing ${description}`}
    >
      {!isInView && !isCached ? (
        // Show skeleton while waiting for intersection
        renderLoadingSkeleton()
      ) : hasError ? (
        // Show fallback icon on error
        renderFallbackIcon()
      ) : (
        // Show actual image or loading state
        <>
          {!isLoaded && !isCached && renderLoadingSkeleton()}
          <img
            ref={imgRef}
            src={imageUrl}
            alt={`Weather icon: ${description}`}
            className={`${className} ${!isLoaded && !isCached ? 'hidden' : ''}`}
            loading="lazy"
            onLoad={handleLoad}
            onError={handleError}
            style={{
              // Ensure smooth transition
              transition: 'opacity 0.2s ease-in-out',
              opacity: isLoaded || isCached ? 1 : 0,
            }}
          />
        </>
      )}
    </div>
  );
});

LazyWeatherIcon.displayName = 'LazyWeatherIcon';

// Utility function to preload a specific weather icon
export const preloadWeatherIcon = (iconCode: string): Promise<void> => {
  return new Promise((resolve, reject) => {
    if (imageCache.has(iconCode)) {
      resolve();
      return;
    }

    const img = new Image();
    img.src = `https://openweathermap.org/img/wn/${iconCode}@2x.png`;

    img.onload = () => {
      imageCache.set(iconCode, img);
      weatherLogger.debug(COMPONENT_NAME, `Preloaded icon: ${iconCode}`);
      resolve();
    };

    img.onerror = () => {
      weatherLogger.warn(COMPONENT_NAME, `Failed to preload icon: ${iconCode}`);
      reject(new Error(`Failed to preload icon: ${iconCode}`));
    };
  });
};

// Utility function to clear icon cache
export const clearIconCache = (): void => {
  imageCache.clear();
  weatherLogger.info(COMPONENT_NAME, 'Icon cache cleared');
};

// Utility function to get cache size
export const getIconCacheSize = (): number => {
  return imageCache.size;
};
