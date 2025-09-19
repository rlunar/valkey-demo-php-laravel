import { useState, useRef, useEffect } from 'react';

interface LazyImageProps {
    src: string;
    alt: string;
    className?: string;
    placeholder?: string;
    width?: number;
    height?: number;
    loading?: 'lazy' | 'eager';
}

export default function LazyImage({
    src,
    alt,
    className = '',
    placeholder,
    width,
    height,
    loading = 'lazy'
}: LazyImageProps) {
    const [isLoaded, setIsLoaded] = useState(false);
    const [isInView, setIsInView] = useState(false);
    const [hasError, setHasError] = useState(false);
    const imgRef = useRef<HTMLImageElement>(null);

    useEffect(() => {
        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    setIsInView(true);
                    observer.disconnect();
                }
            },
            {
                rootMargin: '50px' // Start loading 50px before the image comes into view
            }
        );

        if (imgRef.current) {
            observer.observe(imgRef.current);
        }

        return () => observer.disconnect();
    }, []);

    const handleLoad = () => {
        setIsLoaded(true);
    };

    const handleError = () => {
        setHasError(true);
        setIsLoaded(true);
    };

    // Generate a placeholder if none provided
    const defaultPlaceholder = placeholder || `data:image/svg+xml;base64,${btoa(`
        <svg width="${width || 400}" height="${height || 300}" xmlns="http://www.w3.org/2000/svg">
            <rect width="100%" height="100%" fill="#f3f4f6"/>
            <text x="50%" y="50%" text-anchor="middle" dy=".3em" fill="#9ca3af" font-family="sans-serif" font-size="14">
                Loading...
            </text>
        </svg>
    `)}`;

    const errorPlaceholder = `data:image/svg+xml;base64,${btoa(`
        <svg width="${width || 400}" height="${height || 300}" xmlns="http://www.w3.org/2000/svg">
            <rect width="100%" height="100%" fill="#fef2f2"/>
            <text x="50%" y="50%" text-anchor="middle" dy=".3em" fill="#ef4444" font-family="sans-serif" font-size="14">
                Failed to load
            </text>
        </svg>
    `)}`;

    return (
        <div className={`relative overflow-hidden ${className}`} ref={imgRef}>
            {/* Placeholder/Loading state */}
            {!isLoaded && (
                <img
                    src={defaultPlaceholder}
                    alt=""
                    className="absolute inset-0 w-full h-full object-cover transition-opacity duration-300"
                    aria-hidden="true"
                />
            )}

            {/* Actual image */}
            {(isInView || loading === 'eager') && (
                <img
                    src={hasError ? errorPlaceholder : src}
                    alt={alt}
                    className={`w-full h-full object-cover transition-opacity duration-300 ${
                        isLoaded ? 'opacity-100' : 'opacity-0'
                    }`}
                    onLoad={handleLoad}
                    onError={handleError}
                    loading={loading}
                    width={width}
                    height={height}
                />
            )}
        </div>
    );
}
