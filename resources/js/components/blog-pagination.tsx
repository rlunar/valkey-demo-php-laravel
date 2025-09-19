import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight } from 'lucide-react';

interface BlogPaginationProps {
  hasOlder?: boolean;
  hasNewer?: boolean;
  onOlderClick?: () => void;
  onNewerClick?: () => void;
  olderUrl?: string;
  newerUrl?: string;
}

export function BlogPagination({
  hasOlder = false,
  hasNewer = false,
  onOlderClick,
  onNewerClick,
  olderUrl,
  newerUrl,
}: BlogPaginationProps) {
  return (
    <nav
      className="flex justify-between items-center py-4 sm:py-6 border-t border-gray-200 dark:border-gray-700"
      aria-label="Blog pagination"
      role="navigation"
    >
      <div className="flex-1 flex justify-start">
        {hasOlder && (
          <>
            {olderUrl ? (
              <Button
                variant="outline"
                size="default"
                asChild
                className="flex items-center gap-2 min-h-[44px] px-4 sm:px-6 text-sm sm:text-base touch-manipulation focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                aria-label="View older posts"
              >
                <Link href={olderUrl}>
                  <ChevronLeft className="size-4 sm:size-5" aria-hidden="true" />
                  <span className="hidden xs:inline">Older</span>
                  <span className="xs:hidden" aria-label="Older posts">←</span>
                </Link>
              </Button>
            ) : (
              <Button
                variant="outline"
                size="default"
                onClick={onOlderClick}
                disabled={!hasOlder}
                className="flex items-center gap-2 min-h-[44px] px-4 sm:px-6 text-sm sm:text-base touch-manipulation focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                aria-label="View older posts"
              >
                <ChevronLeft className="size-4 sm:size-5" aria-hidden="true" />
                <span className="hidden xs:inline">Older</span>
                <span className="xs:hidden" aria-label="Older posts">←</span>
              </Button>
            )}
          </>
        )}
      </div>

      <div className="flex-1 flex justify-end">
        {hasNewer && (
          <>
            {newerUrl ? (
              <Button
                variant="outline"
                size="default"
                asChild
                className="flex items-center gap-2 min-h-[44px] px-4 sm:px-6 text-sm sm:text-base touch-manipulation focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                aria-label="View newer posts"
              >
                <Link href={newerUrl}>
                  <span className="hidden xs:inline">Newer</span>
                  <span className="xs:hidden" aria-label="Newer posts">→</span>
                  <ChevronRight className="size-4 sm:size-5" aria-hidden="true" />
                </Link>
              </Button>
            ) : (
              <Button
                variant="outline"
                size="default"
                onClick={onNewerClick}
                disabled={!hasNewer}
                className="flex items-center gap-2 min-h-[44px] px-4 sm:px-6 text-sm sm:text-base touch-manipulation focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                aria-label="View newer posts"
              >
                <span className="hidden xs:inline">Newer</span>
                <span className="xs:hidden" aria-label="Newer posts">→</span>
                <ChevronRight className="size-4 sm:size-5" aria-hidden="true" />
              </Button>
            )}
          </>
        )}
      </div>
    </nav>
  );
}
