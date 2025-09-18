import React from 'react';
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
      className="flex justify-between items-center py-6 border-t border-gray-200 dark:border-gray-700"
      aria-label="Blog pagination"
    >
      <div className="flex-1 flex justify-start">
        {hasOlder && (
          <>
            {olderUrl ? (
              <Button
                variant="outline"
                size="default"
                asChild
                className="flex items-center gap-2"
                aria-label="View older posts"
              >
                <Link href={olderUrl}>
                  <ChevronLeft className="size-4" />
                  Older
                </Link>
              </Button>
            ) : (
              <Button
                variant="outline"
                size="default"
                onClick={onOlderClick}
                disabled={!hasOlder}
                className="flex items-center gap-2"
                aria-label="View older posts"
              >
                <ChevronLeft className="size-4" />
                Older
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
                className="flex items-center gap-2"
                aria-label="View newer posts"
              >
                <Link href={newerUrl}>
                  Newer
                  <ChevronRight className="size-4" />
                </Link>
              </Button>
            ) : (
              <Button
                variant="outline"
                size="default"
                onClick={onNewerClick}
                disabled={!hasNewer}
                className="flex items-center gap-2"
                aria-label="View newer posts"
              >
                Newer
                <ChevronRight className="size-4" />
              </Button>
            )}
          </>
        )}
      </div>
    </nav>
  );
}
