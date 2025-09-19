import { render, screen } from '@testing-library/react'
import BlogIndex from '@/pages/blog/index'
import BlogHeader from '@/components/blog-header'
import BlogNavigation from '@/components/blog-navigation'
import PostCard from '@/components/post-card'
import BlogSidebar from '@/components/blog-sidebar'
import { BlogData, PostCardData, SidebarData } from '@/types'

import { vi } from 'vitest'

// Mock Inertia.js
vi.mock('@inertiajs/react', () => ({
  Head: ({ children, title }: any) => (
    <div data-testid="head" data-title={title}>
      {children}
    </div>
  ),
  Link: ({ children, href, ...props }: any) => (
    <a href={href} {...props}>
      {children}
    </a>
  ),
}))

// Mock window.matchMedia for responsive tests
const mockMatchMedia = (matches: boolean) => {
  Object.defineProperty(window, 'matchMedia', {
    writable: true,
    value: vi.fn().mockImplementation(query => ({
      matches,
      media: query,
      onchange: null,
      addListener: vi.fn(),
      removeListener: vi.fn(),
      addEventListener: vi.fn(),
      removeEventListener: vi.fn(),
      dispatchEvent: vi.fn(),
    })),
  })
}

describe('Blog Responsive Design', () => {
  const mockBlogData: BlogData = {
    siteName: 'Responsive Blog',
    categories: ['World', 'Technology', 'Design', 'Culture', 'Business'],
    featuredPost: {
      title: 'Responsive Featured Post',
      excerpt: 'This post adapts to all screen sizes.',
      readMoreUrl: '/posts/featured'
    },
    secondaryPosts: [
      {
        id: 'secondary-1',
        title: 'Secondary Responsive Post',
        category: 'Technology',
        date: '2024-01-15',
        excerpt: 'Secondary responsive content',
        readMoreUrl: '/posts/secondary-1',
        thumbnailUrl: 'https://example.com/thumb1.jpg'
      },
      {
        id: 'secondary-2',
        title: 'Another Secondary Post',
        category: 'Design',
        date: '2024-01-14',
        excerpt: 'More responsive content',
        readMoreUrl: '/posts/secondary-2'
      }
    ],
    mainPosts: [
      {
        id: 'main-1',
        title: 'Main Responsive Post',
        author: 'Responsive Designer',
        date: '2024-01-13',
        content: '<p>Responsive main content</p>'
      }
    ],
    sidebar: {
      aboutText: 'About responsive design',
      recentPosts: [
        {
          title: 'Recent Responsive Post',
          date: '2024-01-11',
          url: '/posts/recent-1',
          thumbnailUrl: 'https://example.com/recent1.jpg'
        }
      ],
      archives: [
        { label: 'January 2024', url: '/archives/2024/01' }
      ],
      externalLinks: [
        { label: 'Responsive Guidelines', url: 'https://web.dev/responsive-web-design-basics/' }
      ]
    },
    pagination: {
      hasOlder: true,
      hasNewer: false,
      olderUrl: '/blog?page=2'
    }
  }

  describe('Layout Responsiveness', () => {
    it('has proper responsive grid layout', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const mainContent = screen.getByRole('main')
      const gridContainer = mainContent.querySelector('.grid')

      expect(gridContainer).toHaveClass('grid-cols-1', 'lg:grid-cols-4')

      const contentColumn = gridContainer?.querySelector('.lg\\:col-span-3')
      const sidebarColumn = screen.getByRole('complementary')

      expect(contentColumn).toBeInTheDocument()
      expect(sidebarColumn).toHaveClass('lg:col-span-1')
    })

    it('has responsive spacing classes', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const mainContent = screen.getByRole('main')
      expect(mainContent).toHaveClass('px-4', 'sm:px-6', 'lg:px-8')
      expect(mainContent).toHaveClass('py-4', 'sm:py-6', 'lg:py-8')

      const contentColumn = mainContent.querySelector('.lg\\:col-span-3')
      expect(contentColumn).toHaveClass('space-y-4', 'sm:space-y-6', 'lg:space-y-8')
    })

    it('has responsive sidebar ordering', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const sidebar = screen.getByRole('complementary')
      expect(sidebar).toHaveClass('order-first', 'lg:order-last')
    })

    it('has responsive secondary posts grid', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const secondaryPostsContainer = screen.getByText('Secondary Responsive Post').closest('.grid')
      expect(secondaryPostsContainer).toHaveClass('grid-cols-1', 'sm:grid-cols-2')
    })
  })

  describe('Typography Responsiveness', () => {
    it('has responsive header typography', () => {
      render(<BlogHeader siteName="Responsive Blog" />)

      const title = screen.getByRole('heading', { level: 1 })
      expect(title).toHaveClass('text-xl', 'sm:text-2xl', 'lg:text-3xl')
    })

    it('has responsive featured post typography', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const featuredTitle = screen.getByText('Responsive Featured Post')
      expect(featuredTitle).toHaveClass('text-2xl', 'sm:text-3xl', 'md:text-4xl', 'lg:text-5xl')
    })

    it('has responsive main post typography', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const mainPostTitle = screen.getByText('Main Responsive Post')
      expect(mainPostTitle).toHaveClass('text-xl', 'sm:text-2xl', 'md:text-3xl')
    })

    it('has responsive section heading typography', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const sectionHeading = screen.getByRole('heading', { name: 'From the Firehose' })
      expect(sectionHeading).toHaveClass('text-xl', 'sm:text-2xl', 'md:text-3xl')
    })
  })

  describe('Component Responsiveness', () => {
    it('has responsive header layout', () => {
      render(<BlogHeader siteName="Responsive Blog" />)

      const header = screen.getByRole('banner')
      expect(header).toHaveClass('py-3', 'sm:py-4')

      // Check for responsive flex layout
      const headerContent = header.querySelector('.flex')
      expect(headerContent).toHaveClass('items-center', 'justify-between')
    })

    it('has responsive navigation with scroll hint', () => {
      render(<BlogNavigation categories={mockBlogData.categories} />)

      const navigation = screen.getByRole('navigation')
      expect(navigation).toHaveClass('sticky', 'top-0')

      const tablist = screen.getByRole('tablist')
      expect(tablist).toHaveClass('overflow-x-auto', 'scroll-smooth')

      expect(screen.getByText('Swipe to see more categories')).toBeInTheDocument()
    })

    it('has responsive post card layout', () => {
      const mockPost: PostCardData = {
        id: 'test-post',
        title: 'Test Post',
        category: 'Technology',
        date: '2024-01-15',
        excerpt: 'Test excerpt',
        readMoreUrl: '/posts/test',
        thumbnailUrl: 'https://example.com/thumb.jpg'
      }

      render(<PostCard post={mockPost} />)

      const article = screen.getByRole('article')
      expect(article).toHaveClass('flex-col', 'sm:flex-row')

      const title = screen.getByRole('link', { name: mockPost.title })
      expect(title).toHaveClass('text-lg', 'sm:text-xl')
    })

    it('has responsive sidebar with mobile toggle', () => {
      const mockSidebar: SidebarData = {
        aboutText: 'Test about',
        recentPosts: [],
        archives: [],
        externalLinks: []
      }

      render(<BlogSidebar sidebar={mockSidebar} />)

      const sidebar = screen.getByRole('complementary')
      expect(sidebar).toHaveClass('lg:sticky', 'lg:top-4', 'lg:self-start')

      const toggleButton = screen.getByLabelText('Show sidebar content')
      expect(toggleButton).toHaveClass('lg:hidden')
    })
  })

  describe('Image Responsiveness', () => {
    it('has responsive thumbnail sizing in post cards', () => {
      const mockPost: PostCardData = {
        id: 'test-post',
        title: 'Test Post',
        category: 'Technology',
        date: '2024-01-15',
        excerpt: 'Test excerpt',
        readMoreUrl: '/posts/test',
        thumbnailUrl: 'https://example.com/thumb.jpg'
      }

      render(<PostCard post={mockPost} />)

      const thumbnail = screen.getByRole('img')
      const thumbnailContainer = thumbnail.parentElement
      expect(thumbnailContainer).toHaveClass('w-full', 'h-48', 'sm:w-48', 'sm:h-32', 'md:w-56', 'md:h-36')
    })

    it('hides thumbnails appropriately on mobile', () => {
      const mockPost: PostCardData = {
        id: 'test-post',
        title: 'Test Post',
        category: 'Technology',
        date: '2024-01-15',
        excerpt: 'Test excerpt',
        readMoreUrl: '/posts/test',
        thumbnailUrl: 'https://example.com/thumb.jpg'
      }

      render(<PostCard post={mockPost} />)

      const thumbnail = screen.getByRole('img')
      const thumbnailContainer = thumbnail.parentElement
      expect(thumbnailContainer).toHaveClass('hidden', 'lg:block')
    })

    it('has responsive sidebar thumbnails', () => {
      const mockSidebar: SidebarData = {
        aboutText: 'Test about',
        recentPosts: [
          {
            title: 'Recent Post',
            date: '2024-01-11',
            url: '/posts/recent',
            thumbnailUrl: 'https://example.com/recent.jpg'
          }
        ],
        archives: [],
        externalLinks: []
      }

      render(<BlogSidebar sidebar={mockSidebar} />)

      const thumbnail = screen.getByRole('img')
      expect(thumbnail).toHaveAttribute('loading', 'lazy')
    })
  })

  describe('Mobile-Specific Features', () => {
    beforeEach(() => {
      mockMatchMedia(true) // Simulate mobile viewport
    })

    afterEach(() => {
      mockMatchMedia(false) // Reset to desktop
    })

    it('has proper touch targets', () => {
      render(<BlogNavigation categories={mockBlogData.categories} />)

      const tabs = screen.getAllByRole('tab')
      tabs.forEach(tab => {
        expect(tab).toHaveClass('touch-manipulation', 'min-h-[44px]')
      })
    })

    it('has mobile-optimized spacing', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const mainContent = screen.getByRole('main')
      expect(mainContent).toHaveClass('px-4', 'py-4')
    })

    it('has mobile navigation scroll behavior', () => {
      render(<BlogNavigation categories={mockBlogData.categories} />)

      const tablist = screen.getByRole('tablist')
      expect(tablist).toHaveClass('overflow-x-auto', 'scroll-smooth')
    })
  })

  describe('Tablet Responsiveness', () => {
    it('has proper tablet layout classes', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const secondaryPostsContainer = screen.getByText('Secondary Responsive Post').closest('.grid')
      expect(secondaryPostsContainer).toHaveClass('sm:grid-cols-2')
    })

    it('has tablet typography scaling', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const featuredTitle = screen.getByText('Responsive Featured Post')
      expect(featuredTitle).toHaveClass('sm:text-3xl', 'md:text-4xl')
    })
  })

  describe('Desktop Responsiveness', () => {
    it('has proper desktop grid layout', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const mainContent = screen.getByRole('main')
      const gridContainer = mainContent.querySelector('.grid')
      expect(gridContainer).toHaveClass('lg:grid-cols-4')
    })

    it('has desktop sidebar positioning', () => {
      const mockSidebar: SidebarData = {
        aboutText: 'Test about',
        recentPosts: [],
        archives: [],
        externalLinks: []
      }

      render(<BlogSidebar sidebar={mockSidebar} />)

      const sidebar = screen.getByRole('complementary')
      expect(sidebar).toHaveClass('lg:sticky', 'lg:top-4')
    })

    it('shows thumbnails on desktop', () => {
      const mockPost: PostCardData = {
        id: 'test-post',
        title: 'Test Post',
        category: 'Technology',
        date: '2024-01-15',
        excerpt: 'Test excerpt',
        readMoreUrl: '/posts/test',
        thumbnailUrl: 'https://example.com/thumb.jpg'
      }

      render(<PostCard post={mockPost} />)

      const thumbnail = screen.getByRole('img')
      const thumbnailContainer = thumbnail.parentElement
      expect(thumbnailContainer).toHaveClass('lg:block')
    })
  })

  describe('Responsive Utilities', () => {
    it('has proper responsive gap classes', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const mainContent = screen.getByRole('main')
      const gridContainer = mainContent.querySelector('.grid')
      expect(gridContainer).toHaveClass('gap-4', 'sm:gap-6', 'lg:gap-8')
    })

    it('has responsive margin and padding', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const footer = screen.getByRole('contentinfo')
      const footerContent = footer.querySelector('.py-6')
      expect(footerContent).toHaveClass('py-6', 'sm:py-8')
    })

    it('has responsive text alignment', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const footer = screen.getByRole('contentinfo')
      const footerText = footer.querySelector('.text-center')
      expect(footerText).toHaveClass('text-center')
    })
  })

  describe('Responsive Breakpoint Behavior', () => {
    it('handles viewport changes gracefully', () => {
      const { rerender } = render(<BlogIndex blog={mockBlogData} />)

      // Simulate mobile
      mockMatchMedia(true)
      rerender(<BlogIndex blog={mockBlogData} />)

      expect(screen.getByRole('main')).toBeInTheDocument()

      // Simulate desktop
      mockMatchMedia(false)
      rerender(<BlogIndex blog={mockBlogData} />)

      expect(screen.getByRole('main')).toBeInTheDocument()
    })

    it('maintains accessibility across breakpoints', () => {
      render(<BlogIndex blog={mockBlogData} />)

      // Check that ARIA labels are present regardless of screen size
      expect(screen.getByRole('banner')).toBeInTheDocument()
      expect(screen.getByRole('navigation')).toBeInTheDocument()
      expect(screen.getByRole('main')).toBeInTheDocument()
      expect(screen.getByRole('complementary')).toBeInTheDocument()
    })
  })
})
