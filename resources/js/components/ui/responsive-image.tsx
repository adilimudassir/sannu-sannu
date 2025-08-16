import * as React from 'react';
import { cn } from '@/lib/utils';

interface ResponsiveImageProps extends React.ImgHTMLAttributes<HTMLImageElement> {
    src: string;
    alt: string;
    fallback?: string;
    aspectRatio?: 'square' | 'video' | 'auto';
    sizes?: string;
    priority?: boolean;
}

export function ResponsiveImage({
    src,
    alt,
    fallback = '/images/placeholder.svg',
    aspectRatio = 'auto',
    sizes = '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw',
    priority = false,
    className,
    ...props
}: ResponsiveImageProps) {
    const [imageSrc, setImageSrc] = React.useState(src);
    const [isLoading, setIsLoading] = React.useState(true);
    const [hasError, setHasError] = React.useState(false);

    const aspectRatioClasses = {
        square: 'aspect-square',
        video: 'aspect-video',
        auto: 'aspect-auto',
    };

    const handleLoad = () => {
        setIsLoading(false);
        setHasError(false);
    };

    const handleError = () => {
        setIsLoading(false);
        setHasError(true);
        if (imageSrc !== fallback) {
            setImageSrc(fallback);
        }
    };

    React.useEffect(() => {
        setImageSrc(src);
        setIsLoading(true);
        setHasError(false);
    }, [src]);

    return (
        <div className={cn('relative overflow-hidden', aspectRatioClasses[aspectRatio])}>
            {isLoading && (
                <div className="absolute inset-0 bg-muted animate-pulse flex items-center justify-center">
                    <div className="w-8 h-8 border-2 border-muted-foreground border-t-transparent rounded-full animate-spin" />
                </div>
            )}
            
            <img
                src={imageSrc}
                alt={alt}
                sizes={sizes}
                onLoad={handleLoad}
                onError={handleError}
                className={cn(
                    'w-full h-full object-cover transition-opacity duration-300',
                    isLoading ? 'opacity-0' : 'opacity-100',
                    hasError && 'opacity-50',
                    className
                )}
                loading={priority ? 'eager' : 'lazy'}
                {...props}
            />
            
            {hasError && (
                <div className="absolute inset-0 bg-muted/80 flex items-center justify-center">
                    <div className="text-center text-muted-foreground">
                        <svg
                            className="w-8 h-8 mx-auto mb-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                        <p className="text-xs">Image not available</p>
                    </div>
                </div>
            )}
        </div>
    );
}

export default ResponsiveImage;