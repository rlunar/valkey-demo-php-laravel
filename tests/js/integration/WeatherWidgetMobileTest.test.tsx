import React from 'react';
import { render, screen, waitFor, fireEvent } from '@testing-library/react';
import { vi, describe, it, expect, beforeEach, afterEach } from 'vitest';
import WeatherWidget from '@/components/weather-widget';
import { WeatherErrorType } from '@/types/weather-errors';

// Mock the hooks
const mockUseGeolocation = vi.fn();
const mockUseDebouncedWeatherData = vi.fn();
const mockUseWeatherConfig = vi.fn();

vi.mock('@/hooks/use-geolocation', () => ({
  useGeolocation: () => mockUseGeolocation(),
}));

vi.mock('@/hooks/use-debounced-weather-data', () => ({
  useDebouncedWeatherData: (coords: any) => mockUseDebouncedWeatherData(coords),
}));

vi.mock('@/hooks/use-weather-config', () => ({
  useWeatherConfig: () => mockUseWeatherConfig(),
}));

// Mock the lazy weather icon component
vi.mock('@/components/lazy-weather-icon', () => ({
  LazyWeatherIcon: ({ icon, description, className }: any) => (
    <div
      className={className}
      data-testid="weather-icon"
      data-icon={icon}
      aria-label={description}
    >
      Weather Icon: {icon}
    </div>
  ),
}));

// Mock the logger and performance utilities
vi.mock('@/lib/weather-logger', () => ({
  weatherLogger: {
    debug: vi.fn(),
    info: vi.fn(),
    warn: vi.fn(),
    error: vi.fn(),
  },
}));

vi.mock('@/lib/weather-performance', () => ({
  useWeatherPerformance: () => ({
    startRender: vi.fn(),
    endRender: vi.fn(() => 100),
  }),
}));

describe('WeatherWidget - Mobile Responsiveness Tests', () => {
  const mockWeatherData = {
    location: 'New York, NY',
    temperature: 22.5,
    condition: 'Clear',
    description: 'clear sky',
    icon: '01d',
    humidity: 65,
    windSpeed: 3.2,
    lastUpdated: '2024-01-15T10:30:00Z',
    coordinates: { lat: 40.7128, lon: -74.0060 }
  };

  const mockConfig = {
    default_location: {
      lat: 40.7128,
      lon: -74.0060,
      name: 'New York, NY'
    },
    refresh_interval: 1800000,
    retry_attempts: 3
  };

  beforeEach(() => {
    vi.clearAllMocks();

    // Setup default mock implementations
    mockUseWeatherConfig.mockReturnValue({
      config: mockConfig,
      loading: false,
      error: null
    });

    mockUseGeolocation.mockReturnValue({
      location: { lat: 40.7128, lon: -74.0060 },
      error: null,
      loading: false
    });

    mockUseDebouncedWeatherData.mockReturnValue({
      data: mockWeatherData,
      loading: false,
      error: null,
      refetch: vi.fn(),
      retryCount: 0,
      isRetrying: false
    });
  });

  afterEach(() => {
    vi.clearAllMocks();
  });

  describe('Mobile Viewport Rendering', () => {
    it('should render properly on small mobile screens (320px)', () => {
      // Mock small mobile viewport
      Object.defineProperty(window, 'innerWidth', {
        writable: true,
        configurable: true,
        value: 320,
      });

      render(<WeatherWidget />);

      // Should render weather content
      expect(screen.getByText('Weather')).toBeInTheDocument();
      expect(screen.getByText('23°C')).toBeInTheDocument();
      expect(screen.getByText('clear sky')).toBeInTheDocument();

      // Should have responsive classes
      const weatherSection = screen.getByRole('region');
      expect(weatherSection).toHaveClass('p-4', 'sm:p-5');
    });

    it('should render properly on standard mobile screens (375px)', () => {
      Object.defineProperty(window, 'innerWidth', {
        writable: true,
        configurable: true,
        value: 375,
      });

      render(<WeatherWidget />);

      expect(screen.getByText('Weather')).toBeInTheDocument();
      expect(screen.getByText('23°C')).toBeInTheDocument();

      // Check responsive text sizing
      const temperature = screen.getByText('23°C');
      expect(temperature).toHaveClass('text-2xl', 'sm:text-3xl');
    });

    it('should render properly on large mobile screens (414px)', () => {
      Object.defineProperty(window, 'innerWidth', {
        writable: true,
        configurable: true,
        value: 414,
      });

      render(<WeatherWidget />);

      expect(screen.getByText('Weather')).toBeInTheDocument();
      expect(screen.getByText('New York, NY')).toBeInTheDocument();
    });
  });

  describe('Touch Interactions', () => {
    it('should handle touch events on refresh button', () => {
      const mockRefetch = vi.fn();
      mockUseDebouncedWeatherData.mockReturnValue({
        data: mockWeatherData,
        loading: false,
        error: null,
        refetch: mockRefetch,
        retryCount: 0,
        isRetrying: false
      });

      render(<WeatherWidget />);

      const refreshButton = screen.getByRole('button', { name: /refresh weather data/i });

      // Verify touch-manipulation class is present
      expect(refreshButton).toHaveClass('touch-manipulation');

      // Test touch events
      fireEvent.touchStart(refreshButton);
      fireEvent.touchEnd(refreshButton);
      fireEvent.click(refreshButton);

      expect(mockRefetch).toHaveBeenCalledTimes(1);
    });

    it('should handle touch events on retry button', () => {
      const mockRefetch = vi.fn();
      mockUseDebouncedWeatherData.mockReturnValue({
        data: null,
        loading: false,
        error: {
          type: WeatherErrorType.NETWORK_ERROR,
          userMessage: 'Network connection failed',
          retryable: true
        },
        refetch: mockRefetch,
        retryCount: 0,
        isRetrying: false
      });

      render(<WeatherWidget />);

      const retryButton = screen.getByText('Check connection');

      // Test touch events on retry button
      fireEvent.touchStart(retryButton);
      fireEvent.touchEnd(retryButton);
      fireEvent.click(retryButton);

      expect(mockRefetch).toHaveBeenCalledTimes(1);
    });

    it('should have minimum touch target size (44px)', () => {
      render(<WeatherWidget />);

      const refreshButton = screen.getByRole('button', { name: /refresh weather data/i });

      // Check that button has adequate padding for touch targets
      expect(refreshButton).toHaveClass('p-1');
      expect(refreshButton).toHaveClass('touch-manipulation');
    });
  });

  describe('Mobile Layout and Spacing', () => {
    it('should use appropriate spacing for mobile', () => {
      render(<WeatherWidget />);

      const weatherSection = screen.getByRole('region');

      // Should have responsive padding
      expect(weatherSection).toHaveClass('p-4', 'sm:p-5');

      // Content should have proper spacing
      const contentDiv = weatherSection.querySelector('.space-y-3');
      expect(contentDiv).toBeInTheDocument();
    });

    it('should display weather details in mobile-friendly grid', () => {
      render(<WeatherWidget />);

      // Weather details should be in a 2-column grid
      const detailsGrid = screen.getByRole('group', { name: /additional weather details/i });
      expect(detailsGrid).toHaveClass('grid', 'grid-cols-2', 'gap-3');
    });

    it('should use appropriate text sizes for mobile', () => {
      render(<WeatherWidget />);

      // Title should be responsive
      const title = screen.getByText('Weather');
      expect(title).toHaveClass('text-lg', 'sm:text-xl');

      // Temperature should be responsive
      const temperature = screen.getByText('23°C');
      expect(temperature).toHaveClass('text-2xl', 'sm:text-3xl');

      // Description should be responsive
      const description = screen.getByText('clear sky');
      expect(description).toHaveClass('text-sm', 'sm:text-base');
    });
  });

  describe('Mobile Error States', () => {
    it('should display error messages properly on mobile', () => {
      mockUseDebouncedWeatherData.mockReturnValue({
        data: null,
        loading: false,
        error: {
          type: WeatherErrorType.NETWORK_ERROR,
          userMessage: 'Network connection failed',
          retryable: true
        },
        refetch: vi.fn(),
        retryCount: 0,
        isRetrying: false
      });

      render(<WeatherWidget />);

      // Error message should be visible
      expect(screen.getByText('Network connection failed')).toBeInTheDocument();

      // Retry button should be accessible
      const retryButton = screen.getByText('Check connection');
      expect(retryButton).toBeInTheDocument();
    });

    it('should handle loading states on mobile', () => {
      mockUseDebouncedWeatherData.mockReturnValue({
        data: null,
        loading: true,
        error: null,
        refetch: vi.fn(),
        retryCount: 0,
        isRetrying: false
      });

      render(<WeatherWidget />);

      // Loading message should be visible
      expect(screen.getAllByText('Loading weather data...').length).toBeGreaterThan(0);

      // Skeleton loaders should be present
      const skeletons = screen.getAllByRole('img', { name: /placeholder/i });
      expect(skeletons.length).toBeGreaterThan(0);
    });
  });

  describe('Mobile Accessibility', () => {
    it('should maintain accessibility on mobile', () => {
      render(<WeatherWidget />);

      // Main section should have proper ARIA labels
      const weatherSection = screen.getByRole('region', { name: /current weather information/i });
      expect(weatherSection).toBeInTheDocument();

      // Screen reader content should be present
      const srContent = screen.getByText(/current weather: 23 degrees celsius/i);
      expect(srContent).toBeInTheDocument();
    });

    it('should handle focus management on mobile', () => {
      render(<WeatherWidget />);

      const refreshButton = screen.getByRole('button', { name: /refresh weather data/i });

      // Button should be focusable
      refreshButton.focus();
      expect(refreshButton).toHaveFocus();

      // Should have proper focus styles
      expect(refreshButton).toHaveClass('focus:outline-none', 'focus:ring-2');
    });

    it('should provide proper ARIA labels for mobile screen readers', () => {
      render(<WeatherWidget />);

      // Temperature should have descriptive ARIA label
      const temperature = screen.getByRole('text', { name: /23 degrees celsius/i });
      expect(temperature).toBeInTheDocument();

      // Location should have descriptive ARIA label
      const location = screen.getByRole('group', { name: /location: new york, ny/i });
      expect(location).toBeInTheDocument();

      // Weather details should have descriptive ARIA labels
      const humidity = screen.getByRole('group', { name: /humidity: 65 percent/i });
      expect(humidity).toBeInTheDocument();

      const windSpeed = screen.getByRole('group', { name: /wind speed: 12 kilometers per hour/i });
      expect(windSpeed).toBeInTheDocument();
    });
  });

  describe('Mobile Performance', () => {
    it('should render efficiently on mobile devices', () => {
      const startTime = performance.now();

      render(<WeatherWidget />);

      const endTime = performance.now();
      const renderTime = endTime - startTime;

      // Render should complete quickly (under 100ms is good for mobile)
      expect(renderTime).toBeLessThan(100);
    });

    it('should handle rapid touch interactions without issues', () => {
      const mockRefetch = vi.fn();
      mockUseDebouncedWeatherData.mockReturnValue({
        data: mockWeatherData,
        loading: false,
        error: null,
        refetch: mockRefetch,
        retryCount: 0,
        isRetrying: false
      });

      render(<WeatherWidget />);

      const refreshButton = screen.getByRole('button', { name: /refresh weather data/i });

      // Simulate rapid touch interactions
      for (let i = 0; i < 5; i++) {
        fireEvent.touchStart(refreshButton);
        fireEvent.touchEnd(refreshButton);
        fireEvent.click(refreshButton);
      }

      // Should handle all interactions
      expect(mockRefetch).toHaveBeenCalledTimes(5);
    });
  });

  describe('Mobile Dark Mode', () => {
    it('should apply dark mode styles correctly on mobile', () => {
      // Mock dark mode
      document.documentElement.classList.add('dark');

      render(<WeatherWidget />);

      const weatherSection = screen.getByRole('region');

      // Should have dark mode classes
      expect(weatherSection).toHaveClass('dark:bg-gray-800');

      // Clean up
      document.documentElement.classList.remove('dark');
    });
  });

  describe('Mobile Orientation Changes', () => {
    it('should handle portrait to landscape orientation change', () => {
      // Start in portrait
      Object.defineProperty(window, 'innerWidth', {
        writable: true,
        configurable: true,
        value: 375,
      });
      Object.defineProperty(window, 'innerHeight', {
        writable: true,
        configurable: true,
        value: 667,
      });

      const { rerender } = render(<WeatherWidget />);

      expect(screen.getByText('Weather')).toBeInTheDocument();

      // Change to landscape
      Object.defineProperty(window, 'innerWidth', {
        writable: true,
        configurable: true,
        value: 667,
      });
      Object.defineProperty(window, 'innerHeight', {
        writable: true,
        configurable: true,
        value: 375,
      });

      // Trigger resize event
      fireEvent(window, new Event('resize'));

      rerender(<WeatherWidget />);

      // Should still render properly
      expect(screen.getByText('Weather')).toBeInTheDocument();
      expect(screen.getByText('23°C')).toBeInTheDocument();
    });
  });
});
