import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { vi } from 'vitest'
import BlogSidebar from '@/components/blog-sidebar'
import { SidebarData } from '@/types'

// Mock Inertia router
vi.mock('@inertiajs/react', () => ({
  Link: ({ children, href, ...props }: any) => (
    <a href={href} {...props}>
      {children}
    </a>
  ),
}))

describe('BlogSidebar', () => {
  const mockSidebar: SidebarData = {
    aboutText: 'This is a sample about text for the blog sidebar.',
    recentPosts: [
      {
        title: 'Recent Post 1',
        date: '2024-01-15',
        url: '/posts/recent-1',
        thumbnailUrl: 'https://example.com/thumb1.jpg'
      },
      {
        title: 'Recent Post 2 with a Very Long Title That Should Wrap',
        date: '2024-01-10',
        url: '/posts/recent-2'
      }
    ],
    archives: [
      { label: 'January 2024', url: '/archives/2024/01' },
      { label: 'December 2023', url: '/archives/2023/12' }
    ],
    externalLinks: [
      { label: 'GitHub', url: 'https://github.com' },
      { label: 'Twitter', url: 'https://twitter.com' }
    ]
  }

  it('renders the about section', () => {
    render(<BlogSidebar sidebar={mockSidebar} />)

    expect(screen.getByRole('heading', { name: 'About' })).toBeInTheDocument()
    expect(screen.getByText(mockSidebar.aboutText)).toBeInTheDocument()
  })

  it('renders recent posts section', () => {
    render(<BlogSidebar sidebar={mockSidebar} />)

    expect(screen.getByRole('heading', { name: 'Recent posts' })).toBeInTheDocument()
    expect(screen.getByLabelText('Read recent post: Recent Post 1')).toBeInTheDocument()
    expect(screen.getByLabelText('Read recent post: Recent Post 2 with a Very Long Title That Should Wrap')).toBeInTheDocument()
  })

  it('renders archives section', () => {
    render(<BlogSidebar sidebar={mockSidebar} />)

    expect(screen.getByRole('heading', { name: 'Archives' })).toBeInTheDocument()
    expect(screen.getByLabelText('View archive for January 2024')).toBeInTheDocument()
    expect(screen.getByLabelText('View archive for December 2023')).toBeInTheDocument()
  })

  it('renders external links section', () => {
    render(<BlogSidebar sidebar={mockSidebar} />)

    expect(screen.getByRole('heading', { name: 'Elsewhere' })).toBeInTheDocument()
    expect(screen.getByLabelText('Visit external link: GitHub (opens in new tab)')).toBeInTheDocument()
    expect(screen.getByLabelText('Visit external link: Twitter (opens in new tab)')).toBeInTheDocument()
  })

  it('has proper semantic structure', () => {
    render(<BlogSidebar sidebar={mockSidebar} />)

    const aside = screen.getByRole('complementary')
    expect(aside).toHaveAttribute('aria-label', 'Blog sidebar')

    // Check for proper list structures
    const lists = screen.getAllByRole('list')
    expect(lists.length).toBeGreaterThan(0)
  })

  it('handles mobile toggle functionality', async () => {
    const user = userEvent.setup()
    render(<BlogSidebar sidebar={mockSidebar} />)

    const toggleButton = screen.getByLabelText('Show sidebar content')
    expect(toggleButton).toHaveAttribute('aria-expanded', 'false')

    await user.click(toggleButton)
    expect(toggleButton).toHaveAttribute('aria-expanded', 'true')
    expect(toggleButton).toHaveAttribute('aria-label', 'Hide sidebar content')
  })

  it('supports keyboard navigation for mobile toggle', async () => {
    const user = userEvent.setup()
    render(<BlogSidebar sidebar={mockSidebar} />)

    const toggleButton = screen.getByLabelText('Show sidebar content')
    toggleButton.focus()

    await user.keyboard('{Enter}')
    expect(toggleButton).toHaveAttribute('aria-expanded', 'true')

    await user.keyboard(' ')
    expect(toggleButton).toHaveAttribute('aria-expanded', 'false')
  })

  it('renders thumbnails for recent posts when available', () => {
    render(<BlogSidebar sidebar={mockSidebar} />)

    const thumbnail = screen.getByRole('img', { name: 'Thumbnail for Recent Post 1' })
    expect(thumbnail).toHaveAttribute('src', 'https://example.com/thumb1.jpg')
    expect(thumbnail).toHaveAttribute('loading', 'lazy')
  })

  it('renders placeholder for posts without thumbnails', () => {
    render(<BlogSidebar sidebar={mockSidebar} />)

    const placeholder = screen.getByLabelText('No image available')
    expect(placeholder).toBeInTheDocument()
  })

  it('has proper external link attributes', () => {
    render(<BlogSidebar sidebar={mockSidebar} />)

    const githubLink = screen.getByLabelText('Visit external link: GitHub (opens in new tab)')
    expect(githubLink).toHaveAttribute('target', '_blank')
    expect(githubLink).toHaveAttribute('rel', 'noopener noreferrer')
    expect(githubLink).toHaveAttribute('href', 'https://github.com')
  })

  it('has proper responsive classes', () => {
    render(<BlogSidebar sidebar={mockSidebar} />)

    const aside = screen.getByRole('complementary')
    expect(aside).toHaveClass('lg:sticky', 'lg:top-4', 'lg:self-start')

    const toggleButton = screen.getByLabelText('Show sidebar content')
    expect(toggleButton).toHaveClass('lg:hidden')
  })

  it('has proper dark mode support', () => {
    render(<BlogSidebar sidebar={mockSidebar} />)

    const aboutSection = screen.getByText(mockSidebar.aboutText).closest('section')
    expect(aboutSection).toHaveClass('dark:bg-gray-800')
  })

  it('has proper touch targets for mobile', () => {
    render(<BlogSidebar sidebar={mockSidebar} />)

    const toggleButton = screen.getByLabelText('Show sidebar content')
    expect(toggleButton).toHaveClass('touch-manipulation', 'min-h-[44px]')

    const archiveLinks = screen.getAllByText(/January 2024|December 2023/)
    archiveLinks.forEach(link => {
      expect(link).toHaveClass('touch-manipulation')
    })
  })

  it('handles empty sections gracefully', () => {
    const emptySidebar: SidebarData = {
      aboutText: 'About text',
      recentPosts: [],
      archives: [],
      externalLinks: []
    }

    render(<BlogSidebar sidebar={emptySidebar} />)

    expect(screen.getByRole('heading', { name: 'About' })).toBeInTheDocument()
    expect(screen.queryByRole('heading', { name: 'Recent posts' })).not.toBeInTheDocument()
    expect(screen.queryByRole('heading', { name: 'Archives' })).not.toBeInTheDocument()
    expect(screen.queryByRole('heading', { name: 'Elsewhere' })).not.toBeInTheDocument()
  })

  it('truncates long post titles with line clamp', () => {
    render(<BlogSidebar sidebar={mockSidebar} />)

    const longTitleLink = screen.getByLabelText('Read recent post: Recent Post 2 with a Very Long Title That Should Wrap')
    const titleSpan = longTitleLink.querySelector('.line-clamp-2')
    expect(titleSpan).toBeInTheDocument()
  })

  it('has proper focus management', () => {
    render(<BlogSidebar sidebar={mockSidebar} />)

    const links = screen.getAllByRole('link')
    links.forEach(link => {
      expect(link).toHaveClass('focus:outline-none', 'focus:ring-2')
    })
  })

  it('shows external link icons', () => {
    render(<BlogSidebar sidebar={mockSidebar} />)

    const githubLink = screen.getByLabelText('Visit external link: GitHub (opens in new tab)')
    const icon = githubLink.querySelector('svg')
    expect(icon).toHaveAttribute('aria-hidden', 'true')
  })

  it('has proper ARIA controls for mobile toggle', () => {
    render(<BlogSidebar sidebar={mockSidebar} />)

    const toggleButton = screen.getByLabelText('Show sidebar content')
    expect(toggleButton).toHaveAttribute('aria-controls', 'sidebar-content')

    const sidebarContent = screen.getByRole('complementary').querySelector('#sidebar-content')
    expect(sidebarContent).toBeInTheDocument()
  })
})
