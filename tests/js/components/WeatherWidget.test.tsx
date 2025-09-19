import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import WeatherWidget from '@/components/weather-widget';

// Mock the hooks
vi.mock('@/hooks/use-geolocation', () => ({
  useGeolocation: vi.fn()
}));

vi.mock('@/hooks/use-weather-data', () => ({
  useWeatherData: vi.fn()
}));

// Import the mocked hooks
import { useGeolocation } from '@/hooks/use-geolocation';
import { useWeatherData } from '@/hooks/use-weather-data';

const mockUseGeolocation = vi.mocked(useGeolocation);
const mockUseWeatherData = vi.mocked(useWeatherData);

describe('WeatherWidget', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  afterEach(() => {
    vi.clearAllMocks();
  });

  it('renders loading state correctly', () => {
    // Mock loading state
    mockUseGeolocation.mockReturnValue({
      location: null,
      error: null,
      loading: true,
      requestLocation: vi.fn()
    });

    mockUseWeatherData.mockReturnValue({
      data: null,
      loading: false,
      error: null,
      refetch: vi.fn()
    });

    render(<WeatherWidget />);

    // Check for loading skeletons
    expect(screen.getByLabelText('Weather information loading')).toBeInTheDocument();

    // Should have skeleton elements
    const skeletons = document.querySelectorAll('[data-slot="skeleton"]');
    expect(skeletons.length).toBeGreaterThan(0);
  });

  it('renders error state with retry button', () => {
    const mockRefetch = vi.fn();

    mockUseGeolocation.mockReturnValue({
      location: null,
      error: null,
      loading: false,
      requestLocation: vi.fn()
    });

    mockUseWeatherData.mockReturnValue({
      data: null,
      loading: false,
      error: 'Failed to fetch weather data',
      refetch: mockRefetch
    });

    render(<WeatherWidget />);

    // Check for error state
    expect(screen.getByLabelText('Weather information error')).toBeInTheDocument();
    expect(screen.getByText('Failed to fetch weather data')).toBeInTheDocument();

    // Check for retry button
    const retryButton = screen.getByText('Try again');
    expect(retryButton).toBeInTheDocument();

    // Test retry functionality
    fireEvent.click(retryButton);
    expect(mockRefetch).toHaveBeenCalledOnce();
  });

  it('renders weather data correctly', () => {
    const mockWeatherData = {
      location: 'New York, NY',
      temperature: 22.5,
      condition: 'Clear',
      description: 'clear sky',
      icon: '01d',
      humidity: 65,
      windSpeed: 3.2,
      lastUpdated: '2024-01-15T12:00:00Z',
      coordinates: { lat: 40.7128, lon: -74.0060 }
    };

    const mockRefetch = vi.fn();

    mockUseGeolocation.mockReturnValue({
      location: { lat: 40.7128, lon: -74.0060 },
      error: null,
      loading: false,
      requestLocation: vi.fn()
    });

    mockUseWeatherData.mockReturnValue({
      data: mockWeatherData,
      loading: false,
      error: null,
      refetch: mockRefetch
    });

    render(<WeatherWidget />);

    // Check for weather data display
    expect(screen.getByLabelText('Current weather information')).toBeInTheDocument();
    expect(screen.getByText('Weather')).toBeInTheDocument();
    expect(screen.getByText('23°C')).toBeInTheDocument(); // Rounded temperature
    expect(screen.getByText('clear sky')).toBeInTheDocument();
    expect(screen.getByText('Humidity: 65%')).toBeInTheDocument();
    expect(screen.getByText('Wind: 12 km/h')).toBeInTheDocument(); // Converted from m/s to km/h

    // Check for refresh button
    const refreshButton = screen.getByLabelText('Refresh weather data');
    expect(refreshButton).toBeInTheDocument();

    // Test refresh functionality
    fireEvent.click(refreshButton);
    expect(mockRefetch).toHaveBeenCalledOnce();
  });

  it('handles geolocation error gracefully', () => {
    mockUseGeolocation.mockReturnValue({
      location: null,
      error: 'Location access denied by user',
      loading: false,
      requestLocation: vi.fn()
    });

    mockUseWeatherData.mockReturnValue({
      data: null,
      loading: false,
      error: null,
      refetch: vi.fn()
    });

    render(<WeatherWidget />);

    // Should show error state when location is denied
    expect(screen.getByLabelText('Weather information error')).toBeInTheDocument();
    expect(screen.getByText('Unable to determine location')).toBeInTheDocument();
  });

  it('uses default location when provided', () => {
    const defaultLocation = {
      lat: 51.5074,
      lon: -0.1278,
      name: 'London, UK'
    };

    const mockWeatherData = {
      location: 'London, UK',
      temperature: 15.0,
      condition: 'Cloudy',
      description: 'overcast clouds',
      icon: '04d',
      humidity: 80,
      windSpeed: 2.1,
      lastUpdated: '2024-01-15T12:00:00Z',
      coordinates: { lat: 51.5074, lon: -0.1278 }
    };

    // No geolocation error, just no location available
    mockUseGeolocation.mockReturnValue({
      location: null,
      error: null,
      loading: false,
      requestLocation: vi.fn()
    });

    mockUseWeatherData.mockReturnValue({
      data: mockWeatherData,
      loading: false,
      error: null,
      refetch: vi.fn()
    });

    render(<WeatherWidget defaultLocation={defaultLocation} />);

    // Should display weather for default location
    expect(screen.getByText('London, UK')).toBeInTheDocument();
    expect(screen.getByText('15°C')).toBeInTheDocument();
  });

  it('applies custom className', () => {
    mockUseGeolocation.mockReturnValue({
      location: null,
      error: null,
      loading: true,
      requestLocation: vi.fn()
    });

    mockUseWeatherData.mockReturnValue({
      data: null,
      loading: false,
      error: null,
      refetch: vi.fn()
    });

    const { container } = render(<WeatherWidget className="custom-class" />);

    const section = container.querySelector('section');
    expect(section).toHaveClass('custom-class');
  });

  it('handles weather icon loading error', async () => {
    const mockWeatherData = {
      location: 'Test Location',
      temperature: 20,
      condition: 'Clear',
      description: 'clear sky',
      icon: 'invalid-icon',
      humidity: 50,
      windSpeed: 1.0,
      lastUpdated: '2024-01-15T12:00:00Z',
      coordinates: { lat: 0, lon: 0 }
    };

    mockUseGeolocation.mockReturnValue({
      location: { lat: 0, lon: 0 },
      error: null,
      loading: false,
      requestLocation: vi.fn()
    });

    mockUseWeatherData.mockReturnValue({
      data: mockWeatherData,
      loading: false,
      error: null,
      refetch: vi.fn()
    });

    render(<WeatherWidget />);

    const weatherIcon = screen.getByAltText('clear sky');
    expect(weatherIcon).toBeInTheDocument();

    // Simulate image load error
    fireEvent.error(weatherIcon);

    // The error handler should replace the image with an SVG
    await waitFor(() => {
      expect(weatherIcon.style.display).toBe('none');
    });
  });
});
