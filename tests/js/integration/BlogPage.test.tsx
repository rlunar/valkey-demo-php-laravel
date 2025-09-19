import { render, screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import BlogIndex from '@/pages/blog/index'
import { BlogData } from '@/types'

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

describe('BlogIndex Integration', () => {
  const mockBlogData: BlogData = {
    siteName: 'Test Blog',
    categories: ['World', 'U.S.', 'Technology', 'Design', 'Culture'],
    featuredPost: {
      title: 'Featured Post Title',
      excerpt: 'This is the featured post excerpt.',
      readMoreUrl: '/posts/featured'
    },
    secondaryPosts: [
      {
        id: 'secondary-1',
        title: 'Secondary Post 1',
        category: 'Technology',
        date: '2024-01-15',
        excerpt: 'Secondary post excerpt 1',
        readMoreUrl: '/posts/secondary-1',
        thumbnailUrl: 'https://example.com/thumb1.jpg'
      },
      {
        id: 'secondary-2',
        title: 'Secondary Post 2',
        category: 'Design',
        date: '2024-01-14',
        excerpt: 'Secondary post excerpt 2',
        readMoreUrl: '/posts/secondary-2'
      }
    ],
    mainPosts: [
      {
        id: 'main-1',
        title: 'Main Post 1',
        author: 'John Doe',
        date: '2024-01-13',
        content: '<p>Main post content 1</p>'
      },
      {
        id: 'main-2',
        title: 'Main Post 2',
        author: 'Jane Smith',
        date: '2024-01-12',
        content: '<p>Main post content 2</p>'
      }
    ],
    sidebar: {
      aboutText: 'This is the about text for the blog.',
      recentPosts: [
        {
          title: 'Recent Post 1',
          date: '2024-01-11',
          url: '/posts/recent-1',
          thumbnailUrl: 'https://example.com/recent1.jpg'
        }
      ],
      archives: [
        { label: 'January 2024', url: '/archives/2024/01' }
      ],
      externalLinks: [
        { label: 'GitHub', url: 'https://github.com' }
      ]
    },
    pagination: {
      hasOlder: true,
      hasNewer: false,
      olderUrl: '/blog?page=2'
    }
  }

  it('renders the complete blog page structure', () => {
    render(<BlogIndex blog={mockBlogData} />)

    // Check main structural elements
    expect(screen.getByRole('banner')).toBeInTheDocument() // Header
    expect(screen.getByRole('navigation', { name: 'Blog categories navigation' })).toBeInTheDocument()
    expect(screen.getByRole('main', { name: 'Blog content' })).toBeInTheDocument()
    expect(screen.getByRole('complementary', { name: 'Blog sidebar' })).toBeInTheDocument()
    expect(screen.getByRole('contentinfo')).toBeInTheDocument() // Footer
  })

  it('renders all blog sections in correct order', () => {
    render(<BlogIndex blog={mockBlogData} />)

    const main = screen.getByRole('main')
    const sections = within(main).getAllByRole('region')

    // Should have featured, secondary posts, main posts, and pagination sections
    expect(sections).toHaveLength(4)
  })

  it('renders featured post section', () => {
    render(<BlogIndex blog={mockBlogData} />)

    expect(screen.getByText('Featured Post Title')).toBeInTheDocument()
    expect(screen.getByText('This is the featured post excerpt.')).toBeInTheDocument()
  })

  it('renders secondary posts section', () => {
    render(<BlogIndex blog={mockBlogData} />)

    expect(screen.getByText('Secondary Post 1')).toBeInTheDocument()
    expect(screen.getByText('Secondary Post 2')).toBeInTheDocument()
  })

  it('renders main posts section with "From the Firehose" heading', () => {
    render(<BlogIndex blog={mockBlogData} />)

    expect(screen.getByRole('heading', { name: 'From the Firehose' })).toBeInTheDocument()
    expect(screen.getByText('Main Post 1')).toBeInTheDocument()
    expect(screen.getByText('Main Post 2')).toBeInTheDocument()
  })

  it('renders pagination when available', () => {
    render(<BlogIndex blog={mockBlogData} />)

    expect(screen.getByRole('navigation', { name: 'Blog pagination' })).toBeInTheDocument()
    expect(screen.getByRole('link', { name: 'Older posts' })).toBeInTheDocument()
  })

  it('renders sidebar with all sections', () => {
    render(<BlogIndex blog={mockBlogData} />)

    expect(screen.getByRole('heading', { name: 'About' })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: 'Recent posts' })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: 'Archives' })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: 'Elsewhere' })).toBeInTheDocument()
  })

  it('has proper responsive layout classes', () => {
    render(<BlogIndex blog={mockBlogData} />)

    const mainContent = screen.getByRole('main')
    const gridContainer = mainContent.querySelector('.grid')
    expect(gridContainer).toHaveClass('grid-cols-1', 'lg:grid-cols-4')

    const contentColumn = gridContainer?.querySelector('.lg\\:col-span-3')
    expect(contentColumn).toBeInTheDocument()

    const sidebarColumn = screen.getByRole('complementary')
    expect(sidebarColumn).toHaveClass('lg:col-span-1', 'order-first', 'lg:order-last')
  })

  it('includes skip navigation link', () => {
    render(<BlogIndex blog={mockBlogData} />)

    const skipLink = screen.getByRole('link', { name: 'Skip to main content' })
    expect(skipLink).toHaveAttribute('href', '#main-content')
    expect(skipLink).toHaveClass('sr-only', 'focus:not-sr-only')
  })

  it('has proper SEO meta tags', () => {
    render(<BlogIndex blog={mockBlogData} />)

    const head = screen.getByTestId('head')
    expect(head).toHaveAttribute('data-title', 'Test Blog - Blog')

    const description = within(head).getByText('A modern blog built with Laravel, React, and Tailwind CSS', { exact: false })
    expect(description).toBeInTheDocument()
  })

  it('supports keyboard navigation through all interactive elements', async () => {
    const user = userEvent.setup()
    render(<BlogIndex blog={mockBlogData} />)

    // Start with skip link
    await user.tab()
    expect(screen.getByRole('link', { name: 'Skip to main content' })).toHaveFocus()

    // Should be able to navigate through header links
    await user.tab()
    expect(screen.getByLabelText('Subscribe to newsletter')).toHaveFocus()

    await user.tab()
    expect(screen.getByLabelText('Test Blog - Home')).toHaveFocus()
  })

  it('handles missing optional sections gracefully', () => {
    const minimalBlogData: BlogData = {
      ...mockBlogData,
      featuredPost: null as any,
      secondaryPosts: [],
      mainPosts: [],
      pagination: {
        hasOlder: false,
        hasNewer: false
      }
    }

    render(<BlogIndex blog={minimalBlogData} />)

    // Should still render basic structure
    expect(screen.getByRole('banner')).toBeInTheDocument()
    expect(screen.getByRole('main')).toBeInTheDocument()
    expect(screen.getByRole('complementary')).toBeInTheDocument()

    // Optional sections should not be present
    expect(screen.queryByText('Featured Post Title')).not.toBeInTheDocument()
    expect(screen.queryByRole('heading', { name: 'From the Firehose' })).not.toBeInTheDocument()
    expect(screen.queryByRole('navigation', { name: 'Blog pagination' })).not.toBeInTheDocument()
  })

  it('has proper dark mode support throughout', () => {
    render(<BlogIndex blog={mockBlogData} />)

    const container = screen.getByRole('main').closest('div')
    expect(container).toHaveClass('dark:bg-gray-900')

    const footer = screen.getByRole('contentinfo')
    expect(footer).toHaveClass('dark:border-gray-700', 'dark:bg-gray-800/50')
  })

  it('has proper ARIA landmarks and labels', () => {
    render(<BlogIndex blog={mockBlogData} />)

    expect(screen.getByRole('banner')).toBeInTheDocument()
    expect(screen.getByRole('navigation', { name: 'Blog categories navigation' })).toBeInTheDocument()
    expect(screen.getByRole('main', { name: 'Blog content' })).toBeInTheDocument()
    expect(screen.getByRole('complementary', { name: 'Blog sidebar' })).toBeInTheDocument()
    expect(screen.getByRole('contentinfo')).toBeInTheDocument()
  })

  it('renders footer with copyright information', () => {
    render(<BlogIndex blog={mockBlogData} />)

    const footer = screen.getByRole('contentinfo')
    expect(within(footer).getByText(/© 2024 Test Blog/)).toBeInTheDocument()
    expect(within(footer).getByText(/Built with Laravel, React, and Tailwind CSS/)).toBeInTheDocument()
  })

  it('has proper main content ID for skip navigation', () => {
    render(<BlogIndex blog={mockBlogData} />)

    const mainContent = screen.getByRole('main')
    expect(mainContent).toHaveAttribute('id', 'main-content')
  })

  it('uses proper heading hierarchy', () => {
    render(<BlogIndex blog={mockBlogData} />)

    // Site name should be h1
    expect(screen.getByRole('heading', { level: 1 })).toHaveTextContent('Test Blog')

    // Featured post should be h2
    expect(screen.getByRole('heading', { level: 2, name: 'Featured Post Title' })).toBeInTheDocument()

    // Section headings should be h2
    expect(screen.getByRole('heading', { level: 2, name: 'From the Firehose' })).toBeInTheDocument()

    // Sidebar headings should be h3
    expect(screen.getByRole('heading', { level: 3, name: 'About' })).toBeInTheDocument()
  })

  it('has proper spacing and layout structure', () => {
    render(<BlogIndex blog={mockBlogData} />)

    const mainContent = screen.getByRole('main')
    expect(mainContent).toHaveClass('max-w-7xl', 'mx-auto', 'px-4', 'sm:px-6', 'lg:px-8')

    const contentColumn = mainContent.querySelector('.lg\\:col-span-3')
    expect(contentColumn).toHaveClass('space-y-4', 'sm:space-y-6', 'lg:space-y-8')
  })
})
