import { render, screen } from '@testing-library/react'
import FeaturedPost from '@/components/featured-post'
import { FeaturedPostData } from '@/types'

import { vi } from 'vitest'

// Mock Inertia router
vi.mock('@inertiajs/react', () => ({
  Link: ({ children, href, ...props }: any) => (
    <a href={href} {...props}>
      {children}
    </a>
  ),
}))

describe('FeaturedPost', () => {
  const mockPost: FeaturedPostData = {
    title: 'Sample Featured Post Title',
    excerpt: 'This is a sample excerpt for the featured post that provides an overview of the content.',
    readMoreUrl: '/posts/featured-post'
  }

  it('renders the post title correctly', () => {
    render(<FeaturedPost post={mockPost} />)

    const title = screen.getByRole('heading', { level: 1 })
    expect(title).toHaveTextContent('Sample Featured Post Title')
    expect(title).toHaveAttribute('id', 'featured-post-title')
  })

  it('renders the excerpt', () => {
    render(<FeaturedPost post={mockPost} />)

    const excerpt = screen.getByText(mockPost.excerpt)
    expect(excerpt).toBeInTheDocument()
  })

  it('renders continue reading link with proper attributes', () => {
    render(<FeaturedPost post={mockPost} />)

    const continueLink = screen.getByRole('link', { name: `Continue reading: ${mockPost.title}` })
    expect(continueLink).toHaveAttribute('href', mockPost.readMoreUrl)
  })

  it('has proper semantic structure', () => {
    render(<FeaturedPost post={mockPost} />)

    const article = screen.getByRole('article')
    expect(article).toHaveAttribute('aria-labelledby', 'featured-post-title')
  })

  it('has proper responsive typography classes', () => {
    render(<FeaturedPost post={mockPost} />)

    const title = screen.getByRole('heading', { level: 1 })
    expect(title).toHaveClass('text-2xl', 'sm:text-3xl', 'md:text-4xl', 'lg:text-5xl')

    const excerpt = screen.getByText(mockPost.excerpt)
    expect(excerpt).toHaveClass('text-base', 'sm:text-lg', 'md:text-xl')
  })

  it('has proper dark mode support', () => {
    render(<FeaturedPost post={mockPost} />)

    const article = screen.getByRole('article')
    expect(article).toHaveClass('dark:bg-gray-800')

    const title = screen.getByRole('heading', { level: 1 })
    expect(title).toHaveClass('dark:text-gray-100')
  })

  it('has proper focus management', () => {
    render(<FeaturedPost post={mockPost} />)

    const continueLink = screen.getByRole('link', { name: `Continue reading: ${mockPost.title}` })
    expect(continueLink).toHaveClass('focus:outline-none', 'focus:ring-2')
  })

  it('has proper touch targets for mobile', () => {
    render(<FeaturedPost post={mockPost} />)

    const continueLink = screen.getByRole('link', { name: `Continue reading: ${mockPost.title}` })
    expect(continueLink).toHaveClass('touch-manipulation')
  })

  it('uses serif font for title', () => {
    render(<FeaturedPost post={mockPost} />)

    const title = screen.getByRole('heading', { level: 1 })
    expect(title).toHaveClass('font-serif', 'font-bold')
  })

  it('has proper spacing and layout classes', () => {
    render(<FeaturedPost post={mockPost} />)

    const article = screen.getByRole('article')
    expect(article).toHaveClass('p-4', 'sm:p-6', 'md:p-8')
  })

  it('renders arrow icon with proper accessibility', () => {
    render(<FeaturedPost post={mockPost} />)

    const continueLink = screen.getByRole('link', { name: `Continue reading: ${mockPost.title}` })
    const icon = continueLink.querySelector('svg')
    expect(icon).toHaveAttribute('aria-hidden', 'true')
  })

  it('has proper background styling', () => {
    render(<FeaturedPost post={mockPost} />)

    const article = screen.getByRole('article')
    expect(article).toHaveClass('bg-gray-100', 'dark:bg-gray-800', 'rounded-lg')
  })

  it('has proper hover effects', () => {
    render(<FeaturedPost post={mockPost} />)

    const continueLink = screen.getByRole('link', { name: `Continue reading: ${mockPost.title}` })
    expect(continueLink).toHaveClass('hover:text-blue-800', 'transition-colors')
  })
})
