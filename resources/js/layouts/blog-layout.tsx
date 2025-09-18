import { Head } from '@inertiajs/react';
import { type ReactNode } from 'react';

interface BlogLayoutProps {
    children: ReactNode;
    title?: string;
    description?: string;
}

export default function BlogLayout({ children, title = 'Blog', description = 'Read our latest blog posts and articles' }: BlogLayoutProps) {
    return (
        <>
            <Head title={title}>
                <meta name="description" content={description} />
                <meta property="og:title" content={title} />
                <meta property="og:description" content={description} />
                <meta property="og:type" content="website" />
                <meta name="twitter:card" content="summary" />
                <meta name="twitter:title" content={title} />
                <meta name="twitter:description" content={description} />
            </Head>

            <div className="min-h-screen bg-white dark:bg-gray-900">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {children}
                </div>
            </div>
        </>
    );
}
