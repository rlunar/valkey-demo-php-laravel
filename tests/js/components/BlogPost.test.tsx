import { render, screen } from '@testing-library/react'
import { vi } from 'vitest'
import BlogPost from '@/components/blog-post'
import { BlogPostData } from '@/types'

describe('BlogPost', () => {
  const mockPost: BlogPostData = {
    id: 'post-1',
    title: 'Sample Blog Post Title',
    author: 'John Doe',
    date: '2024-01-15',
    content: '<p>This is the <strong>main content</strong> of the blog post.</p><h3>Subheading</h3><p>More content here.</p>'
  }

  it('renders the post title correctly', () => {
    render(<BlogPost post={mockPost} />)

    const title = screen.getByRole('heading', { level: 2 })
    expect(title).toHaveTextContent('Sample Blog Post Title')
    expect(title).toHaveAttribute('id', `post-title-${mockPost.id}`)
  })

  it('renders the author information', () => {
    render(<BlogPost post={mockPost} />)

    expect(screen.getByText('By John Doe')).toBeInTheDocument()
    expect(screen.getByText('Author:', { exact: false })).toHaveClass('sr-only')
  })

  it('renders and formats the date correctly', () => {
    render(<BlogPost post={mockPost} />)

    const dateElement = screen.getByLabelText('Published on January 15, 2024')
    expect(dateElement).toHaveAttribute('datetime', '2024-01-15')
    expect(dateElement).toHaveTextContent('January 15, 2024')
  })

  it('renders the post content with HTML', () => {
    render(<BlogPost post={mockPost} />)

    expect(screen.getByText('main content')).toBeInTheDocument()
    expect(screen.getByText('Subheading')).toBeInTheDocument()
    expect(screen.getByText('More content here.')).toBeInTheDocument()
  })

  it('has proper semantic structure', () => {
    render(<BlogPost post={mockPost} />)

    const article = screen.getByRole('article')
    expect(article).toHaveAttribute('aria-labelledby', `post-title-${mockPost.id}`)

    const header = article.querySelector('header')
    expect(header).toBeInTheDocument()

    const contentDiv = screen.getByLabelText('Article content')
    expect(contentDiv).toHaveAttribute('role', 'main')
  })

  it('has responsive typography classes', () => {
    render(<BlogPost post={mockPost} />)

    const title = screen.getByRole('heading', { level: 2 })
    expect(title).toHaveClass('text-xl', 'sm:text-2xl', 'md:text-3xl')

    const contentDiv = screen.getByLabelText('Article content')
    expect(contentDiv).toHaveClass('text-sm', 'sm:text-base')
  })

  it('has proper dark mode support', () => {
    render(<BlogPost post={mockPost} />)

    const article = screen.getByRole('article')
    expect(article).toHaveClass('dark:border-gray-700')

    const title = screen.getByRole('heading', { level: 2 })
    expect(title).toHaveClass('dark:text-gray-100')
  })

  it('has proper spacing and layout classes', () => {
    render(<BlogPost post={mockPost} />)

    const article = screen.getByRole('article')
    expect(article).toHaveClass('mb-6', 'sm:mb-8', 'pb-6', 'sm:pb-8', 'border-b')

    const contentDiv = screen.getByLabelText('Article content')
    expect(contentDiv).toHaveClass('leading-relaxed', 'prose')
  })

  it('handles invalid dates gracefully', () => {
    const postWithInvalidDate = { ...mockPost, date: 'invalid-date' }
    render(<BlogPost post={postWithInvalidDate} />)

    expect(screen.getByText('invalid-date')).toBeInTheDocument()
  })

  it('renders content with dangerouslySetInnerHTML', () => {
    const postWithComplexHTML = {
      ...mockPost,
      content: '<div><h4>Complex HTML</h4><ul><li>Item 1</li><li>Item 2</li></ul><blockquote>Quote text</blockquote></div>'
    }

    render(<BlogPost post={postWithComplexHTML} />)

    expect(screen.getByText('Complex HTML')).toBeInTheDocument()
    expect(screen.getByText('Item 1')).toBeInTheDocument()
    expect(screen.getByText('Quote text')).toBeInTheDocument()
  })

  it('has proper prose styling for content', () => {
    render(<BlogPost post={mockPost} />)

    const contentDiv = screen.getByLabelText('Article content')
    expect(contentDiv).toHaveClass('prose', 'prose-gray', 'dark:prose-invert')
  })

  it('shows author and date in responsive layout', () => {
    render(<BlogPost post={mockPost} />)

    const metaContainer = screen.getByText('By John Doe').parentElement
    expect(metaContainer).toHaveClass('flex-col', 'sm:flex-row', 'sm:items-center')
  })

  it('has proper separator between author and date on desktop', () => {
    render(<BlogPost post={mockPost} />)

    const separator = screen.getByText('•')
    expect(separator).toHaveClass('hidden', 'sm:inline')
    expect(separator).toHaveAttribute('aria-hidden', 'true')
  })

  it('handles empty content gracefully', () => {
    const postWithEmptyContent = { ...mockPost, content: '' }
    render(<BlogPost post={postWithEmptyContent} />)

    const contentDiv = screen.getByLabelText('Article content')
    expect(contentDiv).toBeInTheDocument()
    expect(contentDiv).toBeEmptyDOMElement()
  })

  it('has proper border styling for last post', () => {
    render(<BlogPost post={mockPost} />)

    const article = screen.getByRole('article')
    expect(article).toHaveClass('last:border-b-0')
  })

  it('uses serif font for title', () => {
    render(<BlogPost post={mockPost} />)

    const title = screen.getByRole('heading', { level: 2 })
    expect(title).toHaveClass('font-serif', 'font-bold')
  })
})
