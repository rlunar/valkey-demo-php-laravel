import React from 'react';

interface ResponsiveImageProps {
    src: string;
    alt: string;
    className?: string;
    sizes?: string;
    loading?: 'lazy' | 'eager';
    priority?: boolean;
}

export default function ResponsiveImage({
    src,
    alt,
    className = '',
    sizes = '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw',
    loading = 'lazy',
    priority = false
}: ResponsiveImageProps) {
    return (
        <img
            src={src}
            alt={alt}
            className={`max-w-full h-auto ${className}`}
            sizes={sizes}
            loading={priority ? 'eager' : loading}
            decoding="async"
            style={{
                aspectRatio: 'auto',
                objectFit: 'cover'
            }}
        />
    );
}
