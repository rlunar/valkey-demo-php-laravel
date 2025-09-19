import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi } from 'vitest';
import BlogSidebar from '@/components/blog-sidebar';
import { SidebarData } from '@/types';

// Mock the WeatherWidget component
vi.mock('@/components/weather-widget', () => ({
  default: ({ defaultLocation }: { defaultLocation?: { lat: number; lon: number; name: string } }) => (
    <div data-testid="weather-widget">
      Weather Widget
      {defaultLocation && (
        <div data-testid="default-location">
          {defaultLocation.name} ({defaultLocation.lat}, {defaultLocation.lon})
        </div>
      )}
    </div>
  ),
}));

// Mock LazyImage component
vi.mock('@/components/lazy-image', () => ({
  default: ({ src, alt, className }: { src: string; alt: string; className: string }) => (
    <img src={src} alt={alt} className={className} />
  ),
}));

describe('BlogSidebar Weather Integration', () => {
  const baseSidebarData: SidebarData = {
    aboutText: 'Test about text',
    recentPosts: [],
    archives: [],
    externalLinks: [],
  };

  it('renders WeatherWidget when weather is enabled', () => {
    const sidebarData: SidebarData = {
      ...baseSidebarData,
      weather: {
        enabled: true,
        defaultLocation: {
          lat: 40.7128,
          lon: -74.0060,
          name: 'New York, NY'
        }
      }
    };

    render(<BlogSidebar sidebar={sidebarData} />);

    expect(screen.getByTestId('weather-widget')).toBeInTheDocument();
    expect(screen.getByTestId('default-location')).toHaveTextContent('New York, NY (40.7128, -74.006)');
  });

  it('does not render WeatherWidget when weather is disabled', () => {
    const sidebarData: SidebarData = {
      ...baseSidebarData,
      weather: {
        enabled: false,
        defaultLocation: {
          lat: 40.7128,
          lon: -74.0060,
          name: 'New York, NY'
        }
      }
    };

    render(<BlogSidebar sidebar={sidebarData} />);

    expect(screen.queryByTestId('weather-widget')).not.toBeInTheDocument();
  });

  it('does not render WeatherWidget when weather config is not provided', () => {
    const sidebarData: SidebarData = {
      ...baseSidebarData,
      // No weather config
    };

    render(<BlogSidebar sidebar={sidebarData} />);

    expect(screen.queryByTestId('weather-widget')).not.toBeInTheDocument();
  });

  it('renders WeatherWidget without defaultLocation when not provided', () => {
    const sidebarData: SidebarData = {
      ...baseSidebarData,
      weather: {
        enabled: true,
        // No defaultLocation
      }
    };

    render(<BlogSidebar sidebar={sidebarData} />);

    expect(screen.getByTestId('weather-widget')).toBeInTheDocument();
    expect(screen.queryByTestId('default-location')).not.toBeInTheDocument();
  });

  it('positions WeatherWidget after About section', () => {
    const sidebarData: SidebarData = {
      ...baseSidebarData,
      weather: {
        enabled: true,
      }
    };

    render(<BlogSidebar sidebar={sidebarData} />);

    const aboutSection = screen.getByText('About').closest('section');
    const weatherWidget = screen.getByTestId('weather-widget');

    expect(aboutSection).toBeInTheDocument();
    expect(weatherWidget).toBeInTheDocument();

    // Check that weather widget comes after about section in DOM order
    const sidebarContent = aboutSection?.parentElement;
    const children = Array.from(sidebarContent?.children || []);
    const aboutIndex = children.indexOf(aboutSection!);
    const weatherIndex = children.findIndex(child => child.contains(weatherWidget));

    expect(weatherIndex).toBeGreaterThan(aboutIndex);
  });
});
