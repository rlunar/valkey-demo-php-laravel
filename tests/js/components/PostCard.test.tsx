import { render, screen } from '@testing-library/react'
import { vi } from 'vitest'
import PostCard from '@/components/post-card'
import { PostCardData } from '@/types'

// Mock Inertia router
vi.mock('@inertiajs/react', () => ({
  Link: ({ children, href, ...props }: any) => (
    <a href={href} {...props}>
      {children}
    </a>
  ),
}))

describe('PostCard', () => {
  const mockPost: PostCardData = {
    id: 'post-1',
    title: 'Sample Post Title',
    category: 'Technology',
    date: '2024-01-15',
    excerpt: 'This is a sample excerpt that provides a brief overview of the post content.',
    readMoreUrl: '/posts/sample-post',
    thumbnailUrl: 'https://example.com/thumbnail.jpg'
  }

  it('renders the post title correctly', () => {
    render(<PostCard post={mockPost} />)

    const titleLink = screen.getByRole('link', { name: mockPost.title })
    expect(titleLink).toHaveTextContent('Sample Post Title')
    expect(titleLink).toHaveAttribute('id', `post-card-title-${mockPost.id}`)
  })

  it('renders the category badge', () => {
    render(<PostCard post={mockPost} />)

    const categoryBadge = screen.getByLabelText('Category: Technology')
    expect(categoryBadge).toHaveTextContent('Technology')
  })

  it('renders and formats the date correctly', () => {
    render(<PostCard post={mockPost} />)

    const dateElement = screen.getByLabelText('Published on January 15, 2024')
    expect(dateElement).toHaveAttribute('datetime', '2024-01-15')
    expect(dateElement).toHaveTextContent('January 15, 2024')
  })

  it('renders the excerpt', () => {
    render(<PostCard post={mockPost} />)

    const excerpt = screen.getByText(mockPost.excerpt)
    expect(excerpt).toHaveAttribute('id', `post-card-excerpt-${mockPost.id}`)
  })

  it('renders thumbnail image when provided', () => {
    render(<PostCard post={mockPost} />)

    const thumbnail = screen.getByRole('img', { name: `Thumbnail for ${mockPost.title}` })
    expect(thumbnail).toHaveAttribute('src', mockPost.thumbnailUrl)
    expect(thumbnail).toHaveAttribute('loading', 'lazy')
  })

  it('does not render thumbnail when not provided', () => {
    const postWithoutThumbnail = { ...mockPost, thumbnailUrl: undefined }
    render(<PostCard post={postWithoutThumbnail} />)

    expect(screen.queryByRole('img')).not.toBeInTheDocument()
  })

  it('renders continue reading link with proper attributes', () => {
    render(<PostCard post={mockPost} />)

    const continueLink = screen.getByRole('link', { name: `Continue reading: ${mockPost.title}` })
    expect(continueLink).toHaveAttribute('href', mockPost.readMoreUrl)
  })

  it('has proper semantic structure', () => {
    render(<PostCard post={mockPost} />)

    const article = screen.getByRole('article')
    expect(article).toHaveAttribute('aria-labelledby', `post-card-title-${mockPost.id}`)
  })

  it('has proper ARIA relationships', () => {
    render(<PostCard post={mockPost} />)

    const titleLink = screen.getByRole('link', { name: mockPost.title })
    expect(titleLink).toHaveAttribute('aria-describedby', `post-card-excerpt-${mockPost.id}`)
  })

  it('has responsive design classes', () => {
    render(<PostCard post={mockPost} />)

    const article = screen.getByRole('article')
    expect(article).toHaveClass('flex-col', 'sm:flex-row')

    const title = screen.getByRole('link', { name: mockPost.title })
    expect(title).toHaveClass('text-lg', 'sm:text-xl')
  })

  it('has proper dark mode support', () => {
    render(<PostCard post={mockPost} />)

    const article = screen.getByRole('article')
    expect(article).toHaveClass('dark:border-gray-700', 'dark:bg-gray-800')
  })

  it('has proper focus management', () => {
    render(<PostCard post={mockPost} />)

    const titleLink = screen.getByRole('link', { name: mockPost.title })
    expect(titleLink).toHaveClass('focus:outline-none', 'focus:ring-2')

    const continueLink = screen.getByRole('link', { name: `Continue reading: ${mockPost.title}` })
    expect(continueLink).toHaveClass('focus:outline-none', 'focus:ring-2')
  })

  it('has proper touch targets for mobile', () => {
    render(<PostCard post={mockPost} />)

    const continueLink = screen.getByRole('link', { name: `Continue reading: ${mockPost.title}` })
    expect(continueLink).toHaveClass('touch-manipulation')
  })

  it('handles invalid dates gracefully', () => {
    const postWithInvalidDate = { ...mockPost, date: 'invalid-date' }
    render(<PostCard post={postWithInvalidDate} />)

    expect(screen.getByText('invalid-date')).toBeInTheDocument()
  })

  it('truncates long excerpts with line clamp', () => {
    const postWithLongExcerpt = {
      ...mockPost,
      excerpt: 'This is a very long excerpt that should be truncated with line clamp to maintain a consistent card height and prevent the layout from breaking on different screen sizes.'
    }

    render(<PostCard post={postWithLongExcerpt} />)

    const excerpt = screen.getByText(postWithLongExcerpt.excerpt)
    expect(excerpt.querySelector('.line-clamp-3')).toBeInTheDocument()
  })

  it('renders arrow icon with proper accessibility', () => {
    render(<PostCard post={mockPost} />)

    const continueLink = screen.getByRole('link', { name: `Continue reading: ${mockPost.title}` })
    const icon = continueLink.querySelector('svg')
    expect(icon).toHaveAttribute('aria-hidden', 'true')
  })

  it('has proper hover effects', () => {
    render(<PostCard post={mockPost} />)

    const article = screen.getByRole('article')
    expect(article).toHaveClass('hover:shadow-md', 'transition-shadow')
  })

  it('has proper responsive thumbnail sizing', () => {
    render(<PostCard post={mockPost} />)

    const thumbnail = screen.getByRole('img')
    const thumbnailContainer = thumbnail.parentElement
    expect(thumbnailContainer).toHaveClass('w-full', 'h-48', 'sm:w-48', 'sm:h-32', 'md:w-56', 'md:h-36')
  })
})
