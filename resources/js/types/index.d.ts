import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

// Blog-related interfaces
export interface BlogData {
    siteName: string;
    categories: string[];
    featuredPost: FeaturedPostData;
    secondaryPosts: PostCardData[];
    mainPosts: BlogPostData[];
    sidebar: SidebarData;
    pagination: BlogPaginationData;
}

export interface FeaturedPostData {
    title: string;
    excerpt: string;
    readMoreUrl: string;
}

export interface PostCardData {
    id: string;
    title: string;
    category: string;
    date: string;
    excerpt: string;
    readMoreUrl: string;
    thumbnailUrl?: string;
}

export interface BlogPostData {
    id: string;
    title: string;
    author: string;
    date: string;
    content: string; // HTML content
}

export interface SidebarData {
    aboutText: string;
    recentPosts: RecentPost[];
    archives: ArchiveLink[];
    externalLinks: ExternalLink[];
}

export interface RecentPost {
    title: string;
    date: string;
    url: string;
    thumbnailUrl?: string;
}

export interface ArchiveLink {
    label: string;
    url: string;
}

export interface ExternalLink {
    label: string;
    url: string;
}

export interface BlogPaginationData {
    hasOlder: boolean;
    hasNewer: boolean;
    olderUrl?: string;
    newerUrl?: string;
}
