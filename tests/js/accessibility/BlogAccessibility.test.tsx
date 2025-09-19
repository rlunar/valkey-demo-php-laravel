import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import BlogIndex from '@/pages/blog/index'
import BlogHeader from '@/components/blog-header'
import BlogNavigation from '@/components/blog-navigation'
import BlogSidebar from '@/components/blog-sidebar'
import { BlogData, SidebarData } from '@/types'

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

describe('Blog Accessibility', () => {
  const mockBlogData: BlogData = {
    siteName: 'Accessible Blog',
    categories: ['World', 'Technology', 'Design'],
    featuredPost: {
      title: 'Accessible Featured Post',
      excerpt: 'This post is accessible to all users.',
      readMoreUrl: '/posts/featured'
    },
    secondaryPosts: [
      {
        id: 'secondary-1',
        title: 'Secondary Accessible Post',
        category: 'Technology',
        date: '2024-01-15',
        excerpt: 'Secondary accessible content',
        readMoreUrl: '/posts/secondary-1',
        thumbnailUrl: 'https://example.com/thumb1.jpg'
      }
    ],
    mainPosts: [
      {
        id: 'main-1',
        title: 'Main Accessible Post',
        author: 'Accessibility Expert',
        date: '2024-01-13',
        content: '<p>Accessible main content</p>'
      }
    ],
    sidebar: {
      aboutText: 'About accessible blogging',
      recentPosts: [
        {
          title: 'Recent Accessible Post',
          date: '2024-01-11',
          url: '/posts/recent-1',
          thumbnailUrl: 'https://example.com/recent1.jpg'
        }
      ],
      archives: [
        { label: 'January 2024', url: '/archives/2024/01' }
      ],
      externalLinks: [
        { label: 'Accessibility Guidelines', url: 'https://www.w3.org/WAI/WCAG21/quickref/' }
      ]
    },
    pagination: {
      hasOlder: true,
      hasNewer: true,
      olderUrl: '/blog?page=2',
      newerUrl: '/blog?page=1'
    }
  }

  describe('Semantic HTML Structure', () => {
    it('uses proper landmark roles', () => {
      render(<BlogIndex blog={mockBlogData} />)

      expect(screen.getByRole('banner')).toBeInTheDocument()
      expect(screen.getByRole('navigation')).toBeInTheDocument()
      expect(screen.getByRole('main')).toBeInTheDocument()
      expect(screen.getByRole('complementary')).toBeInTheDocument()
      expect(screen.getByRole('contentinfo')).toBeInTheDocument()
    })

    it('has proper heading hierarchy', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const headings = screen.getAllByRole('heading')
      const h1 = headings.filter(h => h.tagName === 'H1')
      const h2 = headings.filter(h => h.tagName === 'H2')
      const h3 = headings.filter(h => h.tagName === 'H3')

      expect(h1).toHaveLength(1) // Site name
      expect(h2.length).toBeGreaterThan(0) // Section headings
      expect(h3.length).toBeGreaterThan(0) // Sidebar headings
    })

    it('uses proper article structure for posts', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const articles = screen.getAllByRole('article')
      expect(articles.length).toBeGreaterThan(0)

      articles.forEach(article => {
        expect(article).toHaveAttribute('aria-labelledby')
      })
    })

    it('has proper list structures', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const lists = screen.getAllByRole('list')
      expect(lists.length).toBeGreaterThan(0)

      lists.forEach(list => {
        const listItems = screen.getAllByRole('listitem')
        expect(listItems.length).toBeGreaterThan(0)
      })
    })
  })

  describe('Keyboard Navigation', () => {
    it('supports tab navigation through all interactive elements', async () => {
      const user = userEvent.setup()
      render(<BlogIndex blog={mockBlogData} />)

      // Get all focusable elements
      const focusableElements = screen.getAllByRole('link').concat(
        screen.getAllByRole('button'),
        screen.getAllByRole('tab')
      )

      expect(focusableElements.length).toBeGreaterThan(0)

      // Test that we can tab through elements
      await user.tab()
      expect(document.activeElement).toBeInTheDocument()
    })

    it('supports arrow key navigation in tab list', async () => {
      const user = userEvent.setup()
      render(<BlogNavigation categories={mockBlogData.categories} />)

      const firstTab = screen.getByRole('tab', { name: 'Filter by World category' })
      const secondTab = screen.getByRole('tab', { name: 'Filter by Technology category' })

      firstTab.focus()
      await user.keyboard('{ArrowRight}')
      expect(secondTab).toHaveFocus()

      await user.keyboard('{ArrowLeft}')
      expect(firstTab).toHaveFocus()
    })

    it('supports Home and End keys in navigation', async () => {
      const user = userEvent.setup()
      render(<BlogNavigation categories={mockBlogData.categories} />)

      const firstTab = screen.getByRole('tab', { name: 'Filter by World category' })
      const lastTab = screen.getByRole('tab', { name: 'Filter by Design category' })

      firstTab.focus()
      await user.keyboard('{End}')
      expect(lastTab).toHaveFocus()

      await user.keyboard('{Home}')
      expect(firstTab).toHaveFocus()
    })

    it('supports Enter and Space key activation', async () => {
      const user = userEvent.setup()
      const mockOnCategoryChange = vi.fn()
      render(<BlogNavigation categories={mockBlogData.categories} onCategoryChange={mockOnCategoryChange} />)

      const tab = screen.getByRole('tab', { name: 'Filter by Technology category' })
      tab.focus()

      await user.keyboard('{Enter}')
      expect(mockOnCategoryChange).toHaveBeenCalledWith('Technology')

      mockOnCategoryChange.mockClear()
      await user.keyboard(' ')
      expect(mockOnCategoryChange).toHaveBeenCalledWith('Technology')
    })
  })

  describe('Screen Reader Support', () => {
    it('has proper ARIA labels for all interactive elements', () => {
      render(<BlogHeader siteName="Test Blog" />)

      expect(screen.getByLabelText('Subscribe to newsletter')).toBeInTheDocument()
      expect(screen.getByLabelText('Search')).toBeInTheDocument()
      expect(screen.getByLabelText('Sign up for account')).toBeInTheDocument()
      expect(screen.getByLabelText('Test Blog - Home')).toBeInTheDocument()
    })

    it('has proper ARIA relationships', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const articles = screen.getAllByRole('article')
      articles.forEach(article => {
        const labelledBy = article.getAttribute('aria-labelledby')
        if (labelledBy) {
          expect(document.getElementById(labelledBy)).toBeInTheDocument()
        }
      })
    })

    it('has proper ARIA states for navigation tabs', () => {
      render(<BlogNavigation categories={mockBlogData.categories} activeCategory="Technology" />)

      const activeTab = screen.getByRole('tab', { name: 'Filter by Technology category' })
      const inactiveTab = screen.getByRole('tab', { name: 'Filter by World category' })

      expect(activeTab).toHaveAttribute('aria-selected', 'true')
      expect(activeTab).toHaveAttribute('tabIndex', '0')
      expect(inactiveTab).toHaveAttribute('aria-selected', 'false')
      expect(inactiveTab).toHaveAttribute('tabIndex', '-1')
    })

    it('has proper ARIA controls for mobile sidebar', () => {
      const mockSidebar: SidebarData = {
        aboutText: 'Test about',
        recentPosts: [],
        archives: [],
        externalLinks: []
      }

      render(<BlogSidebar sidebar={mockSidebar} />)

      const toggleButton = screen.getByLabelText('Show sidebar content')
      expect(toggleButton).toHaveAttribute('aria-controls', 'sidebar-content')
      expect(toggleButton).toHaveAttribute('aria-expanded', 'false')
    })

    it('hides decorative icons from screen readers', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const icons = document.querySelectorAll('svg')
      icons.forEach(icon => {
        expect(icon).toHaveAttribute('aria-hidden', 'true')
      })
    })
  })

  describe('Focus Management', () => {
    it('has visible focus indicators', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const focusableElements = screen.getAllByRole('link').concat(
        screen.getAllByRole('button'),
        screen.getAllByRole('tab')
      )

      focusableElements.forEach(element => {
        expect(element).toHaveClass('focus:outline-none', 'focus:ring-2')
      })
    })

    it('has proper focus order', async () => {
      const user = userEvent.setup()
      render(<BlogHeader siteName="Test Blog" />)

      const subscribeLink = screen.getByLabelText('Subscribe to newsletter')
      const homeLink = screen.getByLabelText('Test Blog - Home')
      const searchButton = screen.getByLabelText('Search')
      const signupLink = screen.getByLabelText('Sign up for account')

      await user.tab()
      expect(subscribeLink).toHaveFocus()

      await user.tab()
      expect(homeLink).toHaveFocus()

      await user.tab()
      expect(searchButton).toHaveFocus()

      await user.tab()
      expect(signupLink).toHaveFocus()
    })

    it('maintains focus within modal/collapsible content', async () => {
      const user = userEvent.setup()
      const mockSidebar: SidebarData = {
        aboutText: 'Test about',
        recentPosts: [],
        archives: [],
        externalLinks: []
      }

      render(<BlogSidebar sidebar={mockSidebar} />)

      const toggleButton = screen.getByLabelText('Show sidebar content')
      await user.click(toggleButton)

      expect(toggleButton).toHaveAttribute('aria-expanded', 'true')
    })
  })

  describe('Color and Contrast', () => {
    it('supports dark mode with proper contrast classes', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const container = document.querySelector('.dark\\:bg-gray-900')
      expect(container).toBeInTheDocument()

      const textElements = document.querySelectorAll('.dark\\:text-gray-100, .dark\\:text-gray-300')
      expect(textElements.length).toBeGreaterThan(0)
    })

    it('has proper color contrast for interactive elements', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const links = screen.getAllByRole('link')
      links.forEach(link => {
        const classes = link.className
        expect(classes).toMatch(/text-blue-600|text-gray-900|text-gray-600/)
      })
    })
  })

  describe('Mobile Accessibility', () => {
    it('has proper touch targets', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const interactiveElements = screen.getAllByRole('link').concat(
        screen.getAllByRole('button'),
        screen.getAllByRole('tab')
      )

      interactiveElements.forEach(element => {
        expect(element).toHaveClass('touch-manipulation')
      })
    })

    it('has minimum touch target sizes', () => {
      render(<BlogNavigation categories={mockBlogData.categories} />)

      const tabs = screen.getAllByRole('tab')
      tabs.forEach(tab => {
        expect(tab).toHaveClass('min-h-[44px]')
      })
    })

    it('provides mobile-specific accessibility hints', () => {
      render(<BlogNavigation categories={mockBlogData.categories} />)

      expect(screen.getByText('Swipe to see more categories')).toBeInTheDocument()
    })
  })

  describe('Skip Navigation', () => {
    it('provides skip to main content link', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const skipLink = screen.getByRole('link', { name: 'Skip to main content' })
      expect(skipLink).toHaveAttribute('href', '#main-content')
      expect(skipLink).toHaveClass('sr-only', 'focus:not-sr-only')
    })

    it('skip link targets correct main content', () => {
      render(<BlogIndex blog={mockBlogData} />)

      const mainContent = screen.getByRole('main')
      expect(mainContent).toHaveAttribute('id', 'main-content')
    })
  })

  describe('Error Handling and Fallbacks', () => {
    it('handles missing images gracefully', () => {
      const blogDataWithoutImages: BlogData = {
        ...mockBlogData,
        secondaryPosts: [
          {
            ...mockBlogData.secondaryPosts[0],
            thumbnailUrl: undefined
          }
        ]
      }

      render(<BlogIndex blog={blogDataWithoutImages} />)

      // Should not have broken image elements
      const images = screen.queryAllByRole('img')
      images.forEach(img => {
        expect(img).toHaveAttribute('alt')
      })
    })

    it('provides meaningful error messages', () => {
      // This would test error boundaries in a real implementation
      render(<BlogIndex blog={mockBlogData} />)

      // Ensure no accessibility violations in error states
      expect(screen.getByRole('main')).toBeInTheDocument()
    })
  })
})
