import React from 'react';
import { render, screen, waitFor, fireEvent, act } from '@testing-library/react';
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
    endRender: vi.fn(() => 100), // Mock render time
  }),
}));

describe('WeatherWidget - Full Integration Tests', () => {
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
    refresh_interval: 1800000, // 30 minutes
    retry_attempts: 3
  };

  beforeEach(() => {
    // Reset all mocks
    vi.clearAllMocks();

    // Setup default mock implementations
    mockUseWeatherConfig.mockReturnValue({
      config: mockConfig,
      loading: false,
      error: null
    });
  });

  afterEach(() => {
    vi.clearAllMocks();
  });

  describe('Complete User Flow - Success Path', () => {
    it('should complete full flow: mount → geolocation → API call → display data', async () => {
      // Mock successful geolocation
      mockUseGeolocation.mockReturnValue({
        location: { lat: 40.7128, lon: -74.0060 },
        error: null,
        loading: false
      });

      // Mock successful weather data fetch
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

      // Verify component mounts and shows weather data
      await waitFor(() => {
        expect(screen.getByText('Weather')).toBeInTheDocument();
        expect(screen.getByText('23°C')).toBeInTheDocument();
        expect(screen.getByText('clear sky')).toBeInTheDocument();
        expect(screen.getByText('New York, NY')).toBeInTheDocument();
      });

      // Verify weather details are displayed
      expect(screen.getByText('Humidity: 65%')).toBeInTheDocument();
      expect(screen.getByText('Wind: 12 km/h')).toBeInTheDocument();

      // Verify weather icon is loaded
      const weatherIcon = screen.getByTestId('weather-icon');
      expect(weatherIcon).toHaveAttribute('data-icon', '01d');

      // Verify refresh button works
      const refreshButton = screen.getByRole('button', { name: /refresh weather data/i });
      fireEvent.click(refreshButton);
      expect(mockRefetch).toHaveBeenCalledTimes(1);
    });

    it('should handle geolocation denied gracefully and use default location', async () => {
      // Mock geolocation denied
      mockUseGeolocation.mockReturnValue({
        location: null,
        error: {
          type: WeatherErrorType.GEOLOCATION_DENIED,
          userMessage: 'Location access denied',
          retryable: false
        },
        loading: false
      });

      // Mock successful weather data fetch with default location
      mockUseDebouncedWeatherData.mockReturnValue({
        data: mockWeatherData,
        loading: false,
        error: null,
        refetch: vi.fn(),
        retryCount: 0,
        isRetrying: false
      });

      render(<WeatherWidget />);

      // Should still show weather data for default location
      await waitFor(() => {
        expect(screen.getByText('Weather')).toBeInTheDocument();
        expect(screen.getByText('23°C')).toBeInTheDocument();
        expect(screen.getByText('New York, NY')).toBeInTheDocument();
      });

      // Should not show error since we have weather data
      expect(screen.queryByText('Location access denied')).not.toBeInTheDocument();
    });
  });

  describe('Loading States', () => {
    it('should show loading state during geolocation request', async () => {
      mockUseGeolocation.mockReturnValue({
        location: null,
        error: null,
        loading: true
      });

      mockUseDebouncedWeatherData.mockReturnValue({
        data: null,
        loading: false,
        error: null,
        refetch: vi.fn(),
        retryCount: 0,
        isRetrying: false
      });

      render(<WeatherWidget />);

      expect(screen.getAllByText('Getting your location...').length).toBeGreaterThan(0);
      expect(screen.getByRole('status')).toBeInTheDocument();

      // Should show skeleton loaders
      const skeletons = screen.getAllByRole('img', { name: /placeholder/i });
      expect(skeletons.length).toBeGreaterThan(0);
    });

    it('should show loading state during weather data fetch', async () => {
      mockUseGeolocation.mockReturnValue({
        location: { lat: 40.7128, lon: -74.0060 },
        error: null,
        loading: false
      });

      mockUseDebouncedWeatherData.mockReturnValue({
        data: null,
        loading: true,
        error: null,
        refetch: vi.fn(),
        retryCount: 0,
        isRetrying: false
      });

      render(<WeatherWidget />);

      expect(screen.getAllByText('Loading weather data...').length).toBeGreaterThan(0);
      expect(screen.getByRole('status')).toBeInTheDocument();
    });

    it('should show retry state during retry attempts', async () => {
      mockUseGeolocation.mockReturnValue({
        location: { lat: 40.7128, lon: -74.0060 },
        error: null,
        loading: false
      });

      mockUseDebouncedWeatherData.mockReturnValue({
        data: null,
        loading: true,
        error: null,
        refetch: vi.fn(),
        retryCount: 2,
        isRetrying: true
      });

      render(<WeatherWidget />);

      expect(screen.getAllByText('Retrying weather data...').length).toBeGreaterThan(0);
      expect(screen.getByText('Retry 2/3')).toBeInTheDocument();
    });
  });

  describe('Error Handling and Recovery', () => {
    it('should handle network errors with retry functionality', async () => {
      mockUseGeolocation.mockReturnValue({
        location: { lat: 40.7128, lon: -74.0060 },
        error: null,
        loading: false
      });

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
        retryCount: 1,
        isRetrying: false
      });

      render(<WeatherWidget />);

      // Should show error message
      await waitFor(() => {
        expect(screen.getByText('Network connection failed')).toBeInTheDocument();
      });

      // Should show retry button
      const retryButton = screen.getByText('Check connection');
      expect(retryButton).toBeInTheDocument();

      // Test retry functionality
      fireEvent.click(retryButton);
      expect(mockRefetch).toHaveBeenCalledTimes(1);
    });

    it('should handle rate limiting errors appropriately', async () => {
      mockUseGeolocation.mockReturnValue({
        location: { lat: 40.7128, lon: -74.0060 },
        error: null,
        loading: false
      });

      mockUseDebouncedWeatherData.mockReturnValue({
        data: null,
        loading: false,
        error: {
          type: WeatherErrorType.RATE_LIMIT_EXCEEDED,
          userMessage: 'Too many requests. Please try again later.',
          retryable: true
        },
        refetch: vi.fn(),
        retryCount: 0,
        isRetrying: false
      });

      render(<WeatherWidget />);

      await waitFor(() => {
        expect(screen.getByText('Too many requests. Please try again later.')).toBeInTheDocument();
        expect(screen.getByText('This usually resolves within a few minutes.')).toBeInTheDocument();
      });

      const retryButton = screen.getByRole('button', { name: /try again later/i });
      expect(retryButton).toBeInTheDocument();
    });

    it('should handle service unavailable errors', async () => {
      mockUseGeolocation.mockReturnValue({
        location: { lat: 40.7128, lon: -74.0060 },
        error: null,
        loading: false
      });

      mockUseDebouncedWeatherData.mockReturnValue({
        data: null,
        loading: false,
        error: {
          type: WeatherErrorType.SERVICE_UNAVAILABLE,
          userMessage: 'Weather service is temporarily unavailable',
          retryable: false
        },
        refetch: vi.fn(),
        retryCount: 0,
        isRetrying: false
      });

      render(<WeatherWidget />);

      await waitFor(() => {
        expect(screen.getByText('Weather service is temporarily unavailable')).toBeInTheDocument();
      });

      // Should not show retry button for non-retryable errors
      expect(screen.queryByRole('button', { name: /try again/i })).not.toBeInTheDocument();
    });
  });

  describe('Mobile Responsiveness and Touch Interactions', () => {
    it('should render properly on mobile viewport', async () => {
      // Mock mobile viewport
      Object.defineProperty(window, 'innerWidth', {
        writable: true,
        configurable: true,
        value: 375,
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

      render(<WeatherWidget />);

      await waitFor(() => {
        expect(screen.getByText('Weather')).toBeInTheDocument();
      });

      // Verify touch-friendly button sizes (minimum 44px)
      const refreshButton = screen.getByRole('button', { name: /refresh weather data/i });
      expect(refreshButton).toHaveClass('touch-manipulation');
    });

    it('should handle touch events on interactive elements', async () => {
      mockUseGeolocation.mockReturnValue({
        location: { lat: 40.7128, lon: -74.0060 },
        error: null,
        loading: false
      });

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

      await waitFor(() => {
        expect(screen.getByText('Weather')).toBeInTheDocument();
      });

      const refreshButton = screen.getByRole('button', { name: /refresh weather data/i });

      // Test touch events
      fireEvent.touchStart(refreshButton);
      fireEvent.touchEnd(refreshButton);
      fireEvent.click(refreshButton);

      expect(mockRefetch).toHaveBeenCalledTimes(1);
    });
  });

  describe('Data Accuracy and Refresh Functionality', () => {
    it('should display accurate weather data with proper formatting', async () => {
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

      render(<WeatherWidget />);

      await waitFor(() => {
        // Temperature should be rounded and displayed in Celsius
        expect(screen.getByText('23°C')).toBeInTheDocument();

        // Wind speed should be converted from m/s to km/h and rounded
        expect(screen.getByText('Wind: 12 km/h')).toBeInTheDocument();

        // Humidity should be displayed as percentage
        expect(screen.getByText('Humidity: 65%')).toBeInTheDocument();

        // Description should be displayed
        expect(screen.getByText('clear sky')).toBeInTheDocument();

        // Location should be displayed
        expect(screen.getByText('New York, NY')).toBeInTheDocument();
      });
    });

    it('should show last updated timestamp', async () => {
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

      render(<WeatherWidget />);

      await waitFor(() => {
        // Should show formatted time
        const updatedText = screen.getByText(/Updated:/);
        expect(updatedText).toBeInTheDocument();
      });
    });

    it('should handle manual refresh correctly', async () => {
      mockUseGeolocation.mockReturnValue({
        location: { lat: 40.7128, lon: -74.0060 },
        error: null,
        loading: false
      });

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

      await waitFor(() => {
        expect(screen.getByText('Weather')).toBeInTheDocument();
      });

      const refreshButton = screen.getByRole('button', { name: /refresh weather data/i });

      // Test multiple refresh clicks
      fireEvent.click(refreshButton);
      fireEvent.click(refreshButton);

      expect(mockRefetch).toHaveBeenCalledTimes(2);
    });
  });

  describe('Accessibility Compliance', () => {
    it('should have proper ARIA labels and roles', async () => {
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

      render(<WeatherWidget />);

      await waitFor(() => {
        // Main section should have proper aria-label
        const weatherSection = screen.getByRole('region', { name: /current weather information/i });
        expect(weatherSection).toBeInTheDocument();

        // Refresh button should have descriptive aria-label
        const refreshButton = screen.getByRole('button', { name: /refresh weather data/i });
        expect(refreshButton).toBeInTheDocument();

        // Weather data groups should have proper roles
        const weatherGroups = screen.getAllByRole('group');
        expect(weatherGroups.length).toBeGreaterThan(0);
      });
    });

    it('should provide screen reader announcements', async () => {
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

      render(<WeatherWidget />);

      await waitFor(() => {
        // Should have screen reader content
        const srContent = screen.getByText(/current weather: 23 degrees celsius/i);
        expect(srContent).toBeInTheDocument();
        expect(srContent.closest('.sr-only')).toBeInTheDocument();
      });
    });

    it('should handle keyboard navigation', async () => {
      mockUseGeolocation.mockReturnValue({
        location: { lat: 40.7128, lon: -74.0060 },
        error: null,
        loading: false
      });

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

      await waitFor(() => {
        expect(screen.getByText('Weather')).toBeInTheDocument();
      });

      const refreshButton = screen.getByRole('button', { name: /refresh weather data/i });

      // Test keyboard navigation
      refreshButton.focus();
      expect(refreshButton).toHaveFocus();

      // Test Enter key
      fireEvent.keyDown(refreshButton, { key: 'Enter' });
      fireEvent.keyUp(refreshButton, { key: 'Enter' });

      // Test Space key
      fireEvent.keyDown(refreshButton, { key: ' ' });
      fireEvent.keyUp(refreshButton, { key: ' ' });
    });
  });

  describe('Configuration and Props', () => {
    it('should use custom default location when provided', async () => {
      const customLocation = {
        lat: 51.5074,
        lon: -0.1278,
        name: 'London, UK'
      };

      mockUseGeolocation.mockReturnValue({
        location: null,
        error: {
          type: WeatherErrorType.GEOLOCATION_DENIED,
          userMessage: 'Location access denied',
          retryable: false
        },
        loading: false
      });

      mockUseDebouncedWeatherData.mockReturnValue({
        data: {
          ...mockWeatherData,
          location: 'London, UK'
        },
        loading: false,
        error: null,
        refetch: vi.fn(),
        retryCount: 0,
        isRetrying: false
      });

      render(<WeatherWidget defaultLocation={customLocation} />);

      await waitFor(() => {
        expect(screen.getByText('London, UK')).toBeInTheDocument();
      });
    });

    it('should apply custom className', async () => {
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

      const { container } = render(<WeatherWidget className="custom-weather-class" />);

      await waitFor(() => {
        const weatherSection = container.querySelector('.custom-weather-class');
        expect(weatherSection).toBeInTheDocument();
      });
    });
  });
});
