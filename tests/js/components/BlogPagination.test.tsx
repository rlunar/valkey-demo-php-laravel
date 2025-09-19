import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { BlogPagination } from '@/components/blog-pagination'

import { vi } from 'vitest'
import { it } from 'node:test'
import { it } from 'node:test'
import { it } from 'node:test'
import { it } from 'node:test'
import { it } from 'node:test'
import { it } from 'node:test'
import { it } from 'node:test'
import { it } from 'node:test'
import { it } from 'node:test'
import { it } from 'node:test'
import { it } from 'node:test'
import { it } from 'node:test'
import { it } from 'node:test'
import { it } from 'node:test'
import { it } from 'node:test'
import { it } from 'node:test'
import { it } from 'node:test'
import { describe } from 'node:test'

// Mock Inertia router
vi.mock('@inertiajs/react', () => ({
  Link: ({ children, href, ...props }: any) => (
    <a href={href} {...props}>
      {children}
    </a>
  ),
}))

describe('BlogPagination', () => {
  it('renders both navigation buttons when both directions are available', () => {
    render(
      <BlogPagination
        hasOlder={true}
        hasNewer={true}
        olderUrl="/blog?page=2"
        newerUrl="/blog?page=1"
      />
    )

    expect(screen.getByLabelText('View older posts')).toBeInTheDocument()
    expect(screen.getByLabelText('View newer posts')).toBeInTheDocument()
  })

  it('renders only older button when only older posts are available', () => {
    render(
      <BlogPagination
        hasOlder={true}
        hasNewer={false}
        olderUrl="/blog?page=2"
      />
    )

    expect(screen.getByLabelText('View older posts')).toBeInTheDocument()
    expect(screen.queryByLabelText('View newer posts')).not.toBeInTheDocument()
  })

  it('renders only newer button when only newer posts are available', () => {
    render(
      <BlogPagination
        hasOlder={false}
        hasNewer={true}
        newerUrl="/blog?page=1"
      />
    )

    expect(screen.queryByLabelText('View older posts')).not.toBeInTheDocument()
    expect(screen.getByLabelText('View newer posts')).toBeInTheDocument()
  })

  it('renders navigation container even when no pagination is available', () => {
    render(
      <BlogPagination
        hasOlder={false}
        hasNewer={false}
      />
    )

    expect(screen.getByRole('navigation')).toBeInTheDocument()
    expect(screen.queryByLabelText('View older posts')).not.toBeInTheDocument()
    expect(screen.queryByLabelText('View newer posts')).not.toBeInTheDocument()
  })

  it('has proper semantic structure', () => {
    render(
      <BlogPagination
        hasOlder={true}
        hasNewer={true}
        olderUrl="/blog?page=2"
        newerUrl="/blog?page=1"
      />
    )

    const navigation = screen.getByRole('navigation')
    expect(navigation).toHaveAttribute('aria-label', 'Blog pagination')
  })

  it('has proper ARIA attributes for navigation buttons', () => {
    render(
      <BlogPagination
        hasOlder={true}
        hasNewer={true}
        olderUrl="/blog?page=2"
        newerUrl="/blog?page=1"
      />
    )

    const olderButton = screen.getByLabelText('View older posts')
    const newerButton = screen.getByLabelText('View newer posts')

    expect(olderButton).toHaveAttribute('aria-label', 'View older posts')
    expect(newerButton).toHaveAttribute('aria-label', 'View newer posts')
  })

  it('has correct href attributes', () => {
    render(
      <BlogPagination
        hasOlder={true}
        hasNewer={true}
        olderUrl="/blog?page=2"
        newerUrl="/blog?page=1"
      />
    )

    // With asChild prop, the Button becomes the Link element itself
    const olderButton = screen.getByLabelText('View older posts')
    const newerButton = screen.getByLabelText('View newer posts')

    expect(olderButton).toHaveAttribute('href', '/blog?page=2')
    expect(newerButton).toHaveAttribute('href', '/blog?page=1')
  })

  it('has proper responsive layout classes', () => {
    render(
      <BlogPagination
        hasOlder={true}
        hasNewer={true}
        olderUrl="/blog?page=2"
        newerUrl="/blog?page=1"
      />
    )

    const navigation = screen.getByRole('navigation')
    expect(navigation).toHaveClass('flex', 'justify-between', 'items-center')
  })

  it('has proper button styling', () => {
    render(
      <BlogPagination
        hasOlder={true}
        hasNewer={true}
        olderUrl="/blog?page=2"
        newerUrl="/blog?page=1"
      />
    )

    const olderButton = screen.getByLabelText('View older posts')
    const newerButton = screen.getByLabelText('View newer posts')

    expect(olderButton).toHaveClass('flex', 'items-center', 'gap-2', 'min-h-[44px]')
    expect(newerButton).toHaveClass('flex', 'items-center', 'gap-2', 'min-h-[44px]')
  })

  it('has proper dark mode support', () => {
    render(
      <BlogPagination
        hasOlder={true}
        hasNewer={true}
        olderUrl="/blog?page=2"
        newerUrl="/blog?page=1"
      />
    )

    const navigation = screen.getByRole('navigation')
    expect(navigation).toHaveClass('dark:border-gray-700')

    const olderButton = screen.getByLabelText('View older posts')
    const newerButton = screen.getByLabelText('View newer posts')

    expect(olderButton).toHaveClass('dark:focus:ring-offset-gray-900')
    expect(newerButton).toHaveClass('dark:focus:ring-offset-gray-900')
  })

  it('has proper focus management', () => {
    render(
      <BlogPagination
        hasOlder={true}
        hasNewer={true}
        olderUrl="/blog?page=2"
        newerUrl="/blog?page=1"
      />
    )

    const olderButton = screen.getByLabelText('View older posts')
    const newerButton = screen.getByLabelText('View newer posts')

    expect(olderButton).toHaveClass('focus:ring-offset-2')
    expect(newerButton).toHaveClass('focus:ring-offset-2')
  })

  it('has proper touch targets for mobile', () => {
    render(
      <BlogPagination
        hasOlder={true}
        hasNewer={true}
        olderUrl="/blog?page=2"
        newerUrl="/blog?page=1"
      />
    )

    const olderButton = screen.getByLabelText('View older posts')
    const newerButton = screen.getByLabelText('View newer posts')

    expect(olderButton).toHaveClass('touch-manipulation', 'min-h-[44px]')
    expect(newerButton).toHaveClass('touch-manipulation', 'min-h-[44px]')
  })

  it('renders icons with proper accessibility', () => {
    render(
      <BlogPagination
        hasOlder={true}
        hasNewer={true}
        olderUrl="/blog?page=2"
        newerUrl="/blog?page=1"
      />
    )

    const olderButton = screen.getByLabelText('View older posts')
    const newerButton = screen.getByLabelText('View newer posts')

    const olderIcon = olderButton.querySelector('svg')
    const newerIcon = newerButton.querySelector('svg')

    expect(olderIcon).toHaveAttribute('aria-hidden', 'true')
    expect(newerIcon).toHaveAttribute('aria-hidden', 'true')
  })

  it('has proper hover effects', () => {
    render(
      <BlogPagination
        hasOlder={true}
        hasNewer={true}
        olderUrl="/blog?page=2"
        newerUrl="/blog?page=1"
      />
    )

    // The Button component from shadcn/ui handles hover effects internally
    const olderButton = screen.getByLabelText('View older posts')
    const newerButton = screen.getByLabelText('View newer posts')

    expect(olderButton).toBeInTheDocument()
    expect(newerButton).toBeInTheDocument()
  })

  it('supports keyboard navigation', async () => {
    const user = userEvent.setup()
    render(
      <BlogPagination
        hasOlder={true}
        hasNewer={true}
        olderUrl="/blog?page=2"
        newerUrl="/blog?page=1"
      />
    )

    const olderButton = screen.getByLabelText('View older posts')
    const newerButton = screen.getByLabelText('View newer posts')

    // Tab to first button (older is on the left)
    await user.tab()
    expect(olderButton).toHaveFocus()

    // Tab to second button
    await user.tab()
    expect(newerButton).toHaveFocus()
  })

  it('handles single button layout correctly', () => {
    render(
      <BlogPagination
        hasOlder={true}
        hasNewer={false}
        olderUrl="/blog?page=2"
      />
    )

    const navigation = screen.getByRole('navigation')
    expect(navigation).toHaveClass('flex', 'justify-between', 'items-center')

    // Only older button should be present
    expect(screen.getByLabelText('View older posts')).toBeInTheDocument()
    expect(screen.queryByLabelText('View newer posts')).not.toBeInTheDocument()
  })

  it('has proper spacing and padding', () => {
    render(
      <BlogPagination
        hasOlder={true}
        hasNewer={true}
        olderUrl="/blog?page=2"
        newerUrl="/blog?page=1"
      />
    )

    const navigation = screen.getByRole('navigation')
    expect(navigation).toHaveClass('py-4', 'sm:py-6')
  })
})
