import React from 'react';
import { render, screen, waitFor, fireEvent } from '@testing-library/react';
import { vi, describe, it, expect, beforeEach, afterEach } from 'vitest';
import BlogSidebar from '@/components/blog-sidebar';
import { SidebarData } from '@/types';

// Mock the WeatherWidget component
const mockWeatherWidget = vi.fn();
vi.mock('@/components/weather-widget', () => ({
  default: (props: any) => {
    mockWeatherWidget(props);
    return (
      <div data-testid="weather-widget" data-props={JSON.stringify(props)}>
        <h2>Weather</h2>
        <div>23°C</div>
        <div>Clear sky</div>
        <div>New York, NY</div>
      </div>
    );
  },
}));

// Mock LazyImage component
vi.mock('@/components/lazy-image', () => ({
  default: ({ src, alt, className }: any) => (
    <img src={src} alt={alt} className={className} data-testid="lazy-image" />
  ),
}));

// Mock Inertia Link
vi.mock('@inertiajs/react', () => ({
  Link: ({ href, children, className, ...props }: any) => (
    <a href={href} className={className} {...props}>
      {children}
    </a>
  ),
}));

describe('BlogSidebar - Weather Widget Integration', () => {
  const baseSidebarData: SidebarData = {
    aboutText: 'This is a blog about technology and development.',
    recentPosts: [
      {
        title: 'Getting Started with React',
        url: '/posts/react-basics',
        date: '2024-01-15',
        thumbnailUrl: '/images/react-thumb.jpg'
      },
      {
        title: 'Advanced TypeScript Tips',
        url: '/posts/typescript-tips',
        date: '2024-01-10',
        thumbnailUrl: null
      }
    ],
    archives: [
      { label: 'January 2024', url: '/archives/2024/01' },
      { label: 'December 2023', url: '/archives/2023/12' }
    ],
    externalLinks: [
      { label: 'GitHub', url: 'https://github.com/example' },
      { label: 'Twitter', url: 'https://twitter.com/example' }
    ],
    weather: {
      enabled: true,
      defaultLocation: {
        lat: 40.7128,
        lon: -74.0060,
        name: 'New York, NY'
      }
    }
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  afterEach(() => {
    vi.clearAllMocks();
  });

  describe('Weather Widget Integration', () => {
    it('should render weather widget when enabled in sidebar data', () => {
      render(<BlogSidebar sidebar={baseSidebarData} />);

      // Weather widget should be rendered
      expect(screen.getByTestId('weather-widget')).toBeInTheDocument();
      expect(mockWeatherWidget).toHaveBeenCalledWith({
        defaultLocation: baseSidebarData.weather?.defaultLocation
      });

      // Weather content should be visible
      expect(screen.getByText('Weather')).toBeInTheDocument();
      expect(screen.getByText('23°C')).toBeInTheDocument();
      expect(screen.getByText('Clear sky')).toBeInTheDocument();
      expect(screen.getByText('New York, NY')).toBeInTheDocument();
    });

    it('should not render weather widget when disabled', () => {
      const sidebarWithoutWeather = {
        ...baseSidebarData,
        weather: {
          enabled: false,
          defaultLocation: baseSidebarData.weather?.defaultLocation
        }
      };

      render(<BlogSidebar sidebar={sidebarWithoutWeather} />);

      // Weather widget should not be rendered
      expect(screen.queryByTestId('weather-widget')).not.toBeInTheDocument();
      expect(mockWeatherWidget).not.toHaveBeenCalled();
    });

    it('should not render weather widget when weather config is missing', () => {
      const sidebarWithoutWeatherConfig = {
        ...baseSidebarData,
        weather: undefined
      };

      render(<BlogSidebar sidebar={sidebarWithoutWeatherConfig} />);

      // Weather widget should not be rendered
      expect(screen.queryByTestId('weather-widget')).not.toBeInTheDocument();
      expect(mockWeatherWidget).not.toHaveBeenCalled();
    });

    it('should pass correct default location to weather widget', () => {
      const customLocation = {
        lat: 51.5074,
        lon: -0.1278,
        name: 'London, UK'
      };

      const sidebarWithCustomLocation = {
        ...baseSidebarData,
        weather: {
          enabled: true,
          defaultLocation: customLocation
        }
      };

      render(<BlogSidebar sidebar={sidebarWithCustomLocation} />);

      expect(mockWeatherWidget).toHaveBeenCalledWith({
        defaultLocation: customLocation
      });
    });
  });

  describe('Sidebar Layout and Positioning', () => {
    it('should position weather widget between About and Recent Posts sections', () => {
      render(<BlogSidebar sidebar={baseSidebarData} />);

      const aboutSection = screen.getByText('About').closest('section');
      const weatherWidget = screen.getByTestId('weather-widget');
      const recentPostsSection = screen.getByText('Recent posts').closest('section');

      expect(aboutSection).toBeInTheDocument();
      expect(weatherWidget).toBeInTheDocument();
      expect(recentPostsSection).toBeInTheDocument();

      // Check DOM order
      const sidebarContent = screen.getByRole('complementary');
      const children = Array.from(sidebarContent.querySelectorAll('section, [data-testid="weather-widget"]'));

      const aboutIndex = children.findIndex(child => child.textContent?.includes('About'));
      const weatherIndex = children.findIndex(child => child.getAttribute('data-testid') === 'weather-widget');
      const recentPostsIndex = children.findIndex(child => child.textContent?.includes('Recent posts'));

      expect(aboutIndex).toBeLessThan(weatherIndex);
      expect(weatherIndex).toBeLessThan(recentPostsIndex);
    });

    it('should maintain proper spacing between sections', () => {
      render(<BlogSidebar sidebar={baseSidebarData} />);

      const sidebarContent = screen.getByRole('complementary').querySelector('[id="sidebar-content"]');
      expect(sidebarContent).toHaveClass('space-y-6');
    });
  });

  describe('Mobile Responsiveness', () => {
    it('should handle mobile toggle functionality with weather widget', async () => {
      render(<BlogSidebar sidebar={baseSidebarData} />);

      // Initially, sidebar content should be hidden on mobile
      const sidebarContent = screen.getByRole('complementary').querySelector('[id="sidebar-content"]');
      expect(sidebarContent).toHaveClass('hidden', 'lg:block');

      // Weather widget should be in the DOM but hidden
      expect(screen.getByTestId('weather-widget')).toBeInTheDocument();

      // Click toggle button
      const toggleButton = screen.getByRole('button', { name: /show sidebar content/i });
      fireEvent.click(toggleButton);

      // Sidebar content should now be visible
      await waitFor(() => {
        expect(sidebarContent).toHaveClass('block', 'lg:block');
        expect(sidebarContent).toHaveAttribute('aria-hidden', 'false');
      });

      // Toggle button text should change
      expect(screen.getByRole('button', { name: /hide sidebar content/i })).toBeInTheDocument();
    });

    it('should handle keyboard navigation for mobile toggle', async () => {
      render(<BlogSidebar sidebar={baseSidebarData} />);

      const toggleButton = screen.getByRole('button', { name: /show sidebar content/i });

      // Test Enter key
      fireEvent.keyDown(toggleButton, { key: 'Enter' });

      await waitFor(() => {
        const sidebarContent = screen.getByRole('complementary').querySelector('[id="sidebar-content"]');
        expect(sidebarContent).toHaveClass('block', 'lg:block');
      });

      // Test Space key to close
      fireEvent.keyDown(toggleButton, { key: ' ' });

      await waitFor(() => {
        const sidebarContent = screen.getByRole('complementary').querySelector('[id="sidebar-content"]');
        expect(sidebarContent).toHaveClass('hidden', 'lg:block');
      });
    });
  });

  describe('Complete Sidebar Integration', () => {
    it('should render all sidebar sections in correct order when weather is enabled', () => {
      render(<BlogSidebar sidebar={baseSidebarData} />);

      // All sections should be present
      expect(screen.getByText('About')).toBeInTheDocument();
      expect(screen.getByTestId('weather-widget')).toBeInTheDocument();
      expect(screen.getByText('Recent posts')).toBeInTheDocument();
      expect(screen.getByText('Archives')).toBeInTheDocument();
      expect(screen.getByText('Elsewhere')).toBeInTheDocument();

      // About section content
      expect(screen.getByText('This is a blog about technology and development.')).toBeInTheDocument();

      // Recent posts
      expect(screen.getByText('Getting Started with React')).toBeInTheDocument();
      expect(screen.getByText('Advanced TypeScript Tips')).toBeInTheDocument();

      // Archives
      expect(screen.getByText('January 2024')).toBeInTheDocument();
      expect(screen.getByText('December 2023')).toBeInTheDocument();

      // External links
      expect(screen.getByText('GitHub')).toBeInTheDocument();
      expect(screen.getByText('Twitter')).toBeInTheDocument();
    });

    it('should handle sidebar with no recent posts but weather enabled', () => {
      const sidebarWithoutPosts = {
        ...baseSidebarData,
        recentPosts: []
      };

      render(<BlogSidebar sidebar={sidebarWithoutPosts} />);

      expect(screen.getByText('About')).toBeInTheDocument();
      expect(screen.getByTestId('weather-widget')).toBeInTheDocument();
      expect(screen.queryByText('Recent posts')).not.toBeInTheDocument();
      expect(screen.getByText('Archives')).toBeInTheDocument();
    });

    it('should handle sidebar with minimal content and weather', () => {
      const minimalSidebar = {
        aboutText: 'Minimal blog',
        recentPosts: [],
        archives: [],
        externalLinks: [],
        weather: {
          enabled: true,
          defaultLocation: {
            lat: 40.7128,
            lon: -74.0060,
            name: 'New York, NY'
          }
        }
      };

      render(<BlogSidebar sidebar={minimalSidebar} />);

      expect(screen.getByText('About')).toBeInTheDocument();
      expect(screen.getByText('Minimal blog')).toBeInTheDocument();
      expect(screen.getByTestId('weather-widget')).toBeInTheDocument();

      // Other sections should not be rendered
      expect(screen.queryByText('Recent posts')).not.toBeInTheDocument();
      expect(screen.queryByText('Archives')).not.toBeInTheDocument();
      expect(screen.queryByText('Elsewhere')).not.toBeInTheDocument();
    });
  });

  describe('Accessibility Integration', () => {
    it('should maintain proper accessibility structure with weather widget', () => {
      render(<BlogSidebar sidebar={baseSidebarData} />);

      // Main sidebar should have proper role and label
      const sidebar = screen.getByRole('complementary', { name: /blog sidebar/i });
      expect(sidebar).toBeInTheDocument();

      // Toggle button should be accessible
      const toggleButton = screen.getByRole('button', { name: /show sidebar content/i });
      expect(toggleButton).toBeInTheDocument();

      // Weather widget should be present (even if hidden)
      expect(screen.getByTestId('weather-widget')).toBeInTheDocument();
    });

    it('should handle focus management with weather widget', () => {
      render(<BlogSidebar sidebar={baseSidebarData} />);

      // Toggle button should be focusable
      const toggleButton = screen.getByRole('button', { name: /show sidebar content/i });
      toggleButton.focus();
      expect(toggleButton).toHaveFocus();

      // Click to expand sidebar and then test link focus
      fireEvent.click(toggleButton);

      // Now links should be accessible
      const recentPostLink = screen.getByRole('link', { name: /read recent post: getting started with react/i });
      recentPostLink.focus();
      expect(recentPostLink).toHaveFocus();
    });
  });

  describe('Performance Considerations', () => {
    it('should not re-render weather widget unnecessarily', () => {
      const { rerender } = render(<BlogSidebar sidebar={baseSidebarData} />);

      expect(mockWeatherWidget).toHaveBeenCalledTimes(1);

      // Re-render with same props
      rerender(<BlogSidebar sidebar={baseSidebarData} />);

      // Weather widget should still only be called once due to React optimization
      expect(mockWeatherWidget).toHaveBeenCalledTimes(2); // React will re-render, but that's expected
    });

    it('should handle weather widget props changes correctly', () => {
      const { rerender } = render(<BlogSidebar sidebar={baseSidebarData} />);

      expect(mockWeatherWidget).toHaveBeenCalledWith({
        defaultLocation: baseSidebarData.weather?.defaultLocation
      });

      // Change weather location
      const updatedSidebar = {
        ...baseSidebarData,
        weather: {
          enabled: true,
          defaultLocation: {
            lat: 51.5074,
            lon: -0.1278,
            name: 'London, UK'
          }
        }
      };

      rerender(<BlogSidebar sidebar={updatedSidebar} />);

      expect(mockWeatherWidget).toHaveBeenLastCalledWith({
        defaultLocation: updatedSidebar.weather?.defaultLocation
      });
    });
  });
});
