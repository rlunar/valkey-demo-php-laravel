import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { vi } from 'vitest'
import BlogHeader from '@/components/blog-header'

describe('BlogHeader', () => {
  const defaultProps = {
    siteName: 'Test Blog'
  }

  it('renders the site name correctly', () => {
    render(<BlogHeader {...defaultProps} />)

    expect(screen.getByRole('heading', { level: 1 })).toHaveTextContent('Test Blog')
  })

  it('has proper semantic structure', () => {
    render(<BlogHeader {...defaultProps} />)

    expect(screen.getByRole('banner')).toBeInTheDocument()
    expect(screen.getByRole('heading', { level: 1 })).toBeInTheDocument()
  })

  it('renders all navigation links', () => {
    render(<BlogHeader {...defaultProps} />)

    expect(screen.getByLabelText('Subscribe to newsletter')).toBeInTheDocument()
    expect(screen.getByLabelText('Search')).toBeInTheDocument()
    expect(screen.getByLabelText('Sign up for account')).toBeInTheDocument()
  })

  it('has proper accessibility attributes', () => {
    render(<BlogHeader {...defaultProps} />)

    const subscribeLink = screen.getByLabelText('Subscribe to newsletter')
    const searchButton = screen.getByLabelText('Search')
    const signupLink = screen.getByLabelText('Sign up for account')
    const homeLink = screen.getByLabelText('Test Blog - Home')

    expect(subscribeLink).toHaveAttribute('aria-label', 'Subscribe to newsletter')
    expect(searchButton).toHaveAttribute('aria-label', 'Search')
    expect(signupLink).toHaveAttribute('aria-label', 'Sign up for account')
    expect(homeLink).toHaveAttribute('aria-label', 'Test Blog - Home')
  })

  it('has proper focus management', async () => {
    const user = userEvent.setup()
    render(<BlogHeader {...defaultProps} />)

    const subscribeLink = screen.getByLabelText('Subscribe to newsletter')
    const homeLink = screen.getByLabelText('Test Blog - Home')
    const searchButton = screen.getByLabelText('Search')
    const signupLink = screen.getByLabelText('Sign up for account')

    // Test tab order
    await user.tab()
    expect(subscribeLink).toHaveFocus()

    await user.tab()
    expect(homeLink).toHaveFocus()

    await user.tab()
    expect(searchButton).toHaveFocus()

    await user.tab()
    expect(signupLink).toHaveFocus()
  })

  it('has proper keyboard interaction for search button', async () => {
    const user = userEvent.setup()
    render(<BlogHeader {...defaultProps} />)

    const searchButton = screen.getByLabelText('Search')

    await user.click(searchButton)
    // Note: In a real implementation, this would trigger search functionality
    expect(searchButton).toHaveAttribute('type', 'button')
  })

  it('renders search icon with proper accessibility', () => {
    render(<BlogHeader {...defaultProps} />)

    const searchIcon = screen.getByLabelText('Search').querySelector('svg')
    expect(searchIcon).toHaveAttribute('aria-hidden', 'true')
  })

  it('has responsive classes for mobile optimization', () => {
    render(<BlogHeader {...defaultProps} />)

    const header = screen.getByRole('banner')
    expect(header).toHaveClass('py-3', 'sm:py-4')

    const title = screen.getByRole('heading', { level: 1 })
    expect(title).toHaveClass('text-xl', 'sm:text-2xl', 'lg:text-3xl')
  })

  it('supports dark mode classes', () => {
    render(<BlogHeader {...defaultProps} />)

    const header = screen.getByRole('banner')
    expect(header).toHaveClass('dark:border-gray-700')

    const title = screen.getByRole('heading', { level: 1 })
    expect(title).toHaveClass('dark:text-gray-100')
  })

  it('has proper touch targets for mobile', () => {
    render(<BlogHeader {...defaultProps} />)

    const interactiveElements = [
      screen.getByLabelText('Subscribe to newsletter'),
      screen.getByLabelText('Search'),
      screen.getByLabelText('Sign up for account'),
      screen.getByLabelText('Test Blog - Home')
    ]

    interactiveElements.forEach(element => {
      expect(element).toHaveClass('touch-manipulation')
    })
  })
})
