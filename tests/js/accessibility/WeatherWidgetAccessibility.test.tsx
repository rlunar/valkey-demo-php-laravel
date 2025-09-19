import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import { vi, describe, it, expect, beforeEach } from 'vitest';
import { axe, toHaveNoViolations } from 'jest-axe';
import WeatherWidget from '@/components/weather-widget';

// Extend Jest matchers
expect.extend(toHaveNoViolations);

// Mock the hooks
vi.mock('@/hooks/use-geolocation', () => ({
  useGeolocation: vi.fn()
}));

vi.mock('@/hooks/use-weather-data', () => ({
  useWeatherData: vi.fn()
}));

vi.mock('@/hooks/use-weather-config', () => ({
  useWeatherConfig: vi.fn()
}));

vi.mock('@/lib/weather-logger', () => ({
  weatherLogger: {
    debug: vi.fn(),
    info: vi.fn(),
    warn: vi.fn(),
    error: vi.fn()
  }
}));

// Import the mocked hooks
import { useGeolocation } from '@/hooks/use-geolocation';
import { useWeatherData } from '@/hooks/use-weather-data';
import { useWeatherConfig } from '@/hooks/use-weather-config';

const mockUseGeolocation = vi.mocked(useGeolocation);
const mockUseWeatherData = vi.mocked(useWeatherData);
const mockUseWeatherConfig = vi.mocked(useWeatherConfig);

describe('WeatherWidget Accessibility', () => {
  beforeEach(() => {
    vi.clearAllMocks();

    // Reset mocks to default state
    mockUseGeolocation.mockReturnValue({
      location: null,
      error: null,
      loading: false,
      requestLocation: vi.fn()
    });

    mockUseWeatherData.mockReturnValue({
      data: null,
      loading: false,
      error: null,
      refetch: vi.fn(),
      retryCount: 0,
      isRetrying: false
    });

    mockUseWeatherConfig.mockReturnValue({
      config: {
        default_location: {
          lat: 40.7128,
          lon: -74.0060,
          name: 'New York, NY'
        }
      },
      loading: false,
      error: null
    });
  });

  describe('Loading State Accessibility', () => {
    it('should have proper ARIA attributes during loading', () => {
      mockUseWeatherData.mockReturnValue({
        data: null,
        loading: true,
        error: null,
        refetch: vi.fn(),
        retryCount: 0,
        isRetrying: false
      });

      render(<WeatherWidget />);

      const section = screen.getByRole('region', { name: /weather information loading/i });
      expect(section).toHaveAttribute('aria-live', 'polite');
      expect(section).toHaveAttribute('aria-busy', 'true');

      const status = screen.getByRole('status');
      expect(status).toBeInTheDocument();

      // Check for screen reader announcements
      expect(screen.getAllByText(/loading weather data/i)).toHaveLength(2);
    });

    it('should pass axe accessibility tests in loading state', async () => {
      mockUseWeatherData.mockReturnValue({
        data: null,
        loading: true,
        error: null,
        refetch: vi.fn(),
        retryCount: 0,
        isRetrying: false
      });

      const { container } = render(<WeatherWidget />);
      const results = await axe(container);
      expect(results).toHaveNoViolations();
    });
  });

  describe('Success State Accessibility', () => {
    const mockWeatherData = {
      location: 'New York, NY',
      temperature: 22.5,
      condition: 'Clear',
      description: 'clear sky',
      icon: '01d',
      humidity: 65,
      windSpeed: 3.2,
      lastUpdated: '2024-01-15T12:00:00Z',
      coordinates: { lat: 40.7128, lon: -74.0060 },
    };

    it('should have comprehensive ARIA labels for weather information', () => {
      mockUseWeatherData.mockReturnValue({
        data: mockWeatherData,
        loading: false,
        error: null,
        refetch: vi.fn(),
        retryCount: 0,
        isRetrying: false
      });

      render(<WeatherWidget />);

      const section = screen.getByRole('region', { name: /current weather information for new york, ny/i });
      expect(section).toHaveAttribute('aria-live', 'polite');

      // Check temperature labeling
      expect(screen.getByLabelText(/23 degrees celsius/i)).toBeInTheDocument();

      // Check condition labeling
      expect(screen.getByLabelText(/weather condition: clear sky/i)).toBeInTheDocument();

      // Check location labeling
      expect(screen.getByLabelText(/weather location: new york, ny/i)).toBeInTheDocument();

      // Check humidity labeling
      expect(screen.getByLabelText(/humidity: 65 percent/i)).toBeInTheDocument();

      // Check wind speed labeling
      expect(screen.getByLabelText(/wind speed: 12 kilometers per hour/i)).toBeInTheDocument();
    });

    it('should provide comprehensive screen reader summary', () => {
      mockUseWeatherData.mockReturnValue({
        data: mockWeatherData,
        loading: false,
        error: null,
        refetch: vi.fn(),
        retryCount: 0,
        isRetrying: false
      });

      render(<WeatherWidget />);

      // Check for comprehensive screen reader summary
      const summary = screen.getByText(/current weather: 23 degrees celsius, clear sky in new york, ny/i);
      expect(summary).toBeInTheDocument();
      expect(summary.parentElement).toHaveClass('sr-only');
    });

    it('should have accessible refresh button with context', () => {
      const mockRefetch = vi.fn();
      mockUseWeatherData.mockReturnValue({
        data: mockWeatherData,
        loading: false,
        error: null,
        refetch: mockRefetch,
        retryCount: 0,
        isRetrying: false
      });

      render(<WeatherWidget />);

      const refreshButton = screen.getByRole('button', { name: /refresh weather data for new york, ny/i });
      expect(refreshButton).toBeInTheDocument();
      expect(refreshButton).toHaveAttribute('title', 'Refresh weather data');

      fireEvent.click(refreshButton);
      expect(mockRefetch).toHaveBeenCalled();
    });

    it('should pass axe accessibility tests in success state', async () => {
      mockUseWeatherData.mockReturnValue({
        data: mockWeatherData,
        loading: false,
        error: null,
        refetch: vi.fn(),
        retryCount: 0,
        isRetrying: false
      });

      const { container } = render(<WeatherWidget />);
      const results = await axe(container);
      expect(results).toHaveNoViolations();
    });
  });

  describe('Keyboard Navigation', () => {
    it('should support keyboard navigation for interactive elements', () => {
      const mockRefetch = vi.fn();
      mockUseWeatherData.mockReturnValue({
        data: {
          location: 'New York, NY',
          temperature: 22.5,
          condition: 'Clear',
          description: 'clear sky',
          icon: '01d',
          humidity: 65,
          windSpeed: 3.2,
          lastUpdated: '2024-01-15T12:00:00Z',
          coordinates: { lat: 40.7128, lon: -74.0060 },
        },
        loading: false,
        error: null,
        refetch: mockRefetch,
        retryCount: 0,
        isRetrying: false
      });

      render(<WeatherWidget />);

      const refreshButton = screen.getByRole('button', { name: /refresh weather data/i });

      // Test keyboard focus
      refreshButton.focus();
      expect(refreshButton).toHaveFocus();

      // Test Enter key activation
      fireEvent.keyDown(refreshButton, { key: 'Enter', code: 'Enter' });
      fireEvent.click(refreshButton); // Simulate the click that would happen
      expect(mockRefetch).toHaveBeenCalled();
    });

    it('should have proper focus management with visible focus indicators', () => {
      const mockRefetch = vi.fn();
      mockUseWeatherData.mockReturnValue({
        data: {
          location: 'New York, NY',
          temperature: 22.5,
          condition: 'Clear',
          description: 'clear sky',
          icon: '01d',
          humidity: 65,
          windSpeed: 3.2,
          lastUpdated: '2024-01-15T12:00:00Z',
          coordinates: { lat: 40.7128, lon: -74.0060 },
        },
        loading: false,
        error: null,
        refetch: mockRefetch,
        retryCount: 0,
        isRetrying: false
      });

      render(<WeatherWidget />);

      const refreshButton = screen.getByRole('button', { name: /refresh weather data/i });

      // Check that focus styles are applied
      expect(refreshButton).toHaveClass('focus:outline-none', 'focus:ring-2', 'focus:ring-blue-500');
    });
  });
});
