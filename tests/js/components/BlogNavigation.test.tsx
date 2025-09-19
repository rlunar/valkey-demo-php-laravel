import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { vi } from 'vitest'
import BlogNavigation from '@/components/blog-navigation'

describe('BlogNavigation', () => {
  const defaultCategories = ['World', 'U.S.', 'Technology', 'Design', 'Culture']
  const mockOnCategoryChange = vi.fn()

  beforeEach(() => {
    mockOnCategoryChange.mockClear()
  })

  it('renders all categories', () => {
    render(<BlogNavigation categories={defaultCategories} />)

    defaultCategories.forEach(category => {
      expect(screen.getByRole('tab', { name: `Filter by ${category} category` })).toBeInTheDocument()
    })
  })

  it('has proper ARIA attributes', () => {
    render(<BlogNavigation categories={defaultCategories} activeCategory="World" />)

    const navigation = screen.getByRole('navigation')
    expect(navigation).toHaveAttribute('aria-label', 'Blog categories navigation')

    const tablist = screen.getByRole('tablist')
    expect(tablist).toHaveAttribute('aria-label', 'Category filters')
    expect(tablist).toHaveAttribute('aria-orientation', 'horizontal')
  })

  it('marks active category correctly', () => {
    render(<BlogNavigation categories={defaultCategories} activeCategory="Technology" />)

    const activeTab = screen.getByRole('tab', { name: 'Filter by Technology category' })
    expect(activeTab).toHaveAttribute('aria-selected', 'true')
    expect(activeTab).toHaveAttribute('tabIndex', '0')

    const inactiveTab = screen.getByRole('tab', { name: 'Filter by World category' })
    expect(inactiveTab).toHaveAttribute('aria-selected', 'false')
    expect(inactiveTab).toHaveAttribute('tabIndex', '-1')
  })

  it('calls onCategoryChange when category is clicked', async () => {
    const user = userEvent.setup()
    render(<BlogNavigation categories={defaultCategories} onCategoryChange={mockOnCategoryChange} />)

    const technologyTab = screen.getByRole('tab', { name: 'Filter by Technology category' })
    await user.click(technologyTab)

    expect(mockOnCategoryChange).toHaveBeenCalledWith('Technology')
  })

  it('supports keyboard navigation with arrow keys', async () => {
    const user = userEvent.setup()
    render(<BlogNavigation categories={defaultCategories} activeCategory="World" />)

    const worldTab = screen.getByRole('tab', { name: 'Filter by World category' })
    const usTab = screen.getByRole('tab', { name: 'Filter by U.S. category' })

    // Focus first tab
    worldTab.focus()
    expect(worldTab).toHaveFocus()

    // Arrow right should move to next tab
    await user.keyboard('{ArrowRight}')
    expect(usTab).toHaveFocus()

    // Arrow left should move back
    await user.keyboard('{ArrowLeft}')
    expect(worldTab).toHaveFocus()
  })

  it('supports Home and End key navigation', async () => {
    const user = userEvent.setup()
    render(<BlogNavigation categories={defaultCategories} />)

    const firstTab = screen.getByRole('tab', { name: 'Filter by World category' })
    const lastTab = screen.getByRole('tab', { name: 'Filter by Culture category' })

    // Focus middle tab
    const middleTab = screen.getByRole('tab', { name: 'Filter by Technology category' })
    middleTab.focus()

    // Home key should go to first tab
    await user.keyboard('{Home}')
    expect(firstTab).toHaveFocus()

    // End key should go to last tab
    await user.keyboard('{End}')
    expect(lastTab).toHaveFocus()
  })

  it('supports Enter and Space key activation', async () => {
    const user = userEvent.setup()
    render(<BlogNavigation categories={defaultCategories} onCategoryChange={mockOnCategoryChange} />)

    const technologyTab = screen.getByRole('tab', { name: 'Filter by Technology category' })
    technologyTab.focus()

    // Enter key should activate
    await user.keyboard('{Enter}')
    expect(mockOnCategoryChange).toHaveBeenCalledWith('Technology')

    mockOnCategoryChange.mockClear()

    // Space key should also activate
    await user.keyboard(' ')
    expect(mockOnCategoryChange).toHaveBeenCalledWith('Technology')
  })

  it('wraps around with arrow key navigation', async () => {
    const user = userEvent.setup()
    render(<BlogNavigation categories={defaultCategories} />)

    const firstTab = screen.getByRole('tab', { name: 'Filter by World category' })
    const lastTab = screen.getByRole('tab', { name: 'Filter by Culture category' })

    // Focus first tab and go left (should wrap to last)
    firstTab.focus()
    await user.keyboard('{ArrowLeft}')
    expect(lastTab).toHaveFocus()

    // From last tab, go right (should wrap to first)
    await user.keyboard('{ArrowRight}')
    expect(firstTab).toHaveFocus()
  })

  it('has proper mobile scroll hint', () => {
    render(<BlogNavigation categories={defaultCategories} />)

    expect(screen.getByText('Swipe to see more categories')).toBeInTheDocument()
  })

  it('has proper CSS classes for responsive design', () => {
    render(<BlogNavigation categories={defaultCategories} />)

    const navigation = screen.getByRole('navigation')
    expect(navigation).toHaveClass('sticky', 'top-0', 'z-10')

    const tablist = screen.getByRole('tablist')
    expect(tablist).toHaveClass('overflow-x-auto', 'scroll-smooth')
  })

  it('shows active indicator for selected category', () => {
    render(<BlogNavigation categories={defaultCategories} activeCategory="Technology" />)

    const activeTab = screen.getByRole('tab', { name: 'Filter by Technology category' })
    const indicator = activeTab.querySelector('span[aria-hidden="true"]')
    expect(indicator).toBeInTheDocument()
  })

  it('has proper touch targets for mobile', () => {
    render(<BlogNavigation categories={defaultCategories} />)

    const tabs = screen.getAllByRole('tab')
    tabs.forEach(tab => {
      expect(tab).toHaveClass('touch-manipulation', 'min-h-[44px]')
    })
  })

  it('handles empty categories array gracefully', () => {
    render(<BlogNavigation categories={[]} />)

    const tablist = screen.getByRole('tablist')
    expect(tablist).toBeInTheDocument()
    expect(screen.queryAllByRole('tab')).toHaveLength(0)
  })

  it('supports dark mode classes', () => {
    render(<BlogNavigation categories={defaultCategories} />)

    const navigation = screen.getByRole('navigation')
    expect(navigation).toHaveClass('dark:border-gray-700', 'dark:bg-gray-900')
  })
})
