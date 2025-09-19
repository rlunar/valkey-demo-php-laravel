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

vi.mock('@/hooks/use-weather-config', () => ({
  useWeatherConfig: vi.fn()
}));

// Import the mocked hooks
import { useGeolocation } from '@/hooks/use-geolocation';
import { useWeatherData } from '@/hooks/use-weather-data';
import { useWeatherConfig } from '@/hooks/use-weather-config';

const mockUseGeolocation = vi.mocked(useGeolocation);
const mockUseWeatherData = vi.mocked(useWeatherData);
const mockUseWeatherConfig = vi.mocked(useWeatherConfig);

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
      refetch: vi.fn(),
      retryCount: 0,
      isRetrying: false
    });

    mockUseWeatherConfig.mockReturnValue({
      config: null,
      loading: false,
      error: null
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
    const mockError = {
      type: 'NETWORK_ERROR',
      message: 'Network connection failed',
      userMessage: 'Unable to connect to weather service. Please check your internet connection.',
      retryable: true,
      details: {},
      timestamp: '2024-01-15T12:00:00Z'
    };

    mockUseGeolocation.mockReturnValue({
      location: null,
      error: null,
      loading: false,
      requestLocation: vi.fn()
    });

    mockUseWeatherData.mockReturnValue({
      data: null,
      loading: false,
      error: mockError,
      refetch: mockRefetch,
      retryCount: 0,
      isRetrying: false
    });

    mockUseWeatherConfig.mockReturnValue({
      config: {
        default_location: { lat: 40.7128, lon: -74.0060, name: 'New York, NY' }
      },
      loading: false,
      error: null
    });

    render(<WeatherWidget />);

    // Check for error state
    expect(screen.getByLabelText('Weather information error')).toBeInTheDocument();
    expect(screen.getByText('Unable to connect to weather service. Please check your internet connection.')).toBeInTheDocument();

    // Check for retry button
    const retryButton = screen.getByText('Check connection');
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
      refetch: mockRefetch,
      retryCount: 0,
      isRetrying: false
    });

    mockUseWeatherConfig.mockReturnValue({
      config: {
        default_location: { lat: 40.7128, lon: -74.0060, name: 'New York, NY' }
      },
      loading: false,
      error: null
    });

    render(<WeatherWidget />);

    // Check for weather data display
    expect(screen.getByLabelText(/Current weather information/)).toBeInTheDocument();
    expect(screen.getByText('Weather')).toBeInTheDocument();
    expect(screen.getByText('23°C')).toBeInTheDocument(); // Rounded temperature
    expect(screen.getByText('clear sky')).toBeInTheDocument();
    expect(screen.getByText('Humidity: 65%')).toBeInTheDocument();
    expect(screen.getByText('Wind: 12 km/h')).toBeInTheDocument(); // Converted from m/s to km/h

    // Check for refresh button
    const refreshButton = screen.getByLabelText(/Refresh weather data/);
    expect(refreshButton).toBeInTheDocument();

    // Test refresh functionality
    fireEvent.click(refreshButton);
    expect(mockRefetch).toHaveBeenCalledOnce();
  });

  it('handles geolocation error gracefully', () => {
    const mockLocationError = {
      type: 'GEOLOCATION_DENIED',
      message: 'Geolocation permission denied',
      userMessage: 'Location access denied. Showing weather for default location.',
      retryable: false,
      details: {},
      timestamp: '2024-01-15T12:00:00Z'
    };

    mockUseGeolocation.mockReturnValue({
      location: null,
      error: mockLocationError,
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
        default_location: { lat: 40.7128, lon: -74.0060, name: 'New York, NY' }
      },
      loading: false,
      error: null
    });

    render(<WeatherWidget />);

    // Should show weather data for default location, not an error
    // Since geolocation denial is handled gracefully by falling back to default location
    expect(screen.queryByLabelText('Weather information error')).not.toBeInTheDocument();
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
      refetch: vi.fn(),
      retryCount: 0,
      isRetrying: false
    });

    mockUseWeatherConfig.mockReturnValue({
      config: {
        default_location: { lat: 40.7128, lon: -74.0060, name: 'New York, NY' }
      },
      loading: false,
      error: null
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
      refetch: vi.fn(),
      retryCount: 0,
      isRetrying: false
    });

    mockUseWeatherConfig.mockReturnValue({
      config: null,
      loading: false,
      error: null
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
      refetch: vi.fn(),
      retryCount: 0,
      isRetrying: false
    });

    mockUseWeatherConfig.mockReturnValue({
      config: {
        default_location: { lat: 40.7128, lon: -74.0060, name: 'New York, NY' }
      },
      loading: false,
      error: null
    });

    render(<WeatherWidget />);

    const weatherIcon = screen.getByAltText(/Weather icon: clear sky/);
    expect(weatherIcon).toBeInTheDocument();

    // Simulate image load error
    fireEvent.error(weatherIcon);

    // The error handler should replace the image with an SVG
    await waitFor(() => {
      expect(weatherIcon.style.display).toBe('none');
    });
  });
});

  // Add comprehensive new tests for all requirements

  it('displays weather config loading state', () => {
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
      config: null,
      loading: true,
      error: null
    });

    render(<WeatherWidget />);

    expect(screen.getAllByText('Loading configuration...')).toHaveLength(2); // One for screen reader, one visible
    expect(screen.getByLabelText('Weather information loading')).toBeInTheDocument();
  });

  it('handles retry state correctly', () => {
    const mockRefetch = vi.fn();

    mockUseGeolocation.mockReturnValue({
      location: { lat: 40.7128, lon: -74.0060 },
      error: null,
      loading: false,
      requestLocation: vi.fn()
    });

    mockUseWeatherData.mockReturnValue({
      data: null,
      loading: true,
      error: null,
      refetch: mockRefetch,
      retryCount: 2,
      isRetrying: true
    });

    mockUseWeatherConfig.mockReturnValue({
      config: {
        default_location: { lat: 40.7128, lon: -74.0060, name: 'New York, NY' }
      },
      loading: false,
      error: null
    });

    render(<WeatherWidget />);

    expect(screen.getByText('Retry 2/3')).toBeInTheDocument();
    expect(screen.getAllByText('Retrying weather data...')).toHaveLength(2); // One for screen reader, one visible
  });

  it('handles non-retryable errors correctly', () => {
    const mockError = {
      type: 'GEOLOCATION_DENIED',
      message: 'Geolocation permission denied',
      userMessage: 'Location access denied. Showing weather for default location.',
      retryable: false,
      details: {},
      timestamp: '2024-01-15T12:00:00Z'
    };

    mockUseGeolocation.mockReturnValue({
      location: null,
      error: mockError,
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
        default_location: { lat: 40.7128, lon: -74.0060, name: 'New York, NY' }
      },
      loading: false,
      error: null
    });

    render(<WeatherWidget />);

    // Should not show error state for geolocation denial (handled gracefully)
    expect(screen.queryByLabelText('Weather information error')).not.toBeInTheDocument();
  });

  it('displays fallback state when no data and no errors', () => {
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
        default_location: { lat: 40.7128, lon: -74.0060, name: 'New York, NY' }
      },
      loading: false,
      error: null
    });

    render(<WeatherWidget />);

    expect(screen.getByText('Weather information unavailable')).toBeInTheDocument();
    expect(screen.getByText('Please check your connection and try again')).toBeInTheDocument();
  });

  it('handles rate limit error with appropriate message', () => {
    const mockError = {
      type: 'RATE_LIMIT_EXCEEDED',
      message: 'Rate limit exceeded',
      userMessage: 'Weather service temporarily unavailable due to high demand. Please try again later.',
      retryable: true,
      details: {},
      timestamp: '2024-01-15T12:00:00Z'
    };

    mockUseGeolocation.mockReturnValue({
      location: { lat: 40.7128, lon: -74.0060 },
      error: null,
      loading: false,
      requestLocation: vi.fn()
    });

    mockUseWeatherData.mockReturnValue({
      data: null,
      loading: false,
      error: mockError,
      refetch: vi.fn(),
      retryCount: 0,
      isRetrying: false
    });

    mockUseWeatherConfig.mockReturnValue({
      config: {
        default_location: { lat: 40.7128, lon: -74.0060, name: 'New York, NY' }
      },
      loading: false,
      error: null
    });

    render(<WeatherWidget />);

    expect(screen.getByText('Weather service temporarily unavailable due to high demand. Please try again later.')).toBeInTheDocument();
    expect(screen.getByText('Try again later')).toBeInTheDocument();
    expect(screen.getByText('This usually resolves within a few minutes.')).toBeInTheDocument();
  });

  it('displays weather data with all details correctly', () => {
    const mockWeatherData = {
      location: 'London, UK',
      temperature: 15.7,
      condition: 'Clouds',
      description: 'overcast clouds',
      icon: '04d',
      humidity: 78,
      windSpeed: 4.2,
      lastUpdated: '2024-01-15T14:30:00Z',
      coordinates: { lat: 51.5074, lon: -0.1278 }
    };

    mockUseGeolocation.mockReturnValue({
      location: { lat: 51.5074, lon: -0.1278 },
      error: null,
      loading: false,
      requestLocation: vi.fn()
    });

    mockUseWeatherData.mockReturnValue({
      data: mockWeatherData,
      loading: false,
      error: null,
      refetch: vi.fn(),
      retryCount: 0,
      isRetrying: false
    });

    mockUseWeatherConfig.mockReturnValue({
      config: {
        default_location: { lat: 40.7128, lon: -74.0060, name: 'New York, NY' }
      },
      loading: false,
      error: null
    });

    render(<WeatherWidget />);

    // Check all weather data is displayed
    expect(screen.getByText('16°C')).toBeInTheDocument(); // Rounded temperature
    expect(screen.getByText('overcast clouds')).toBeInTheDocument();
    expect(screen.getByText('London, UK')).toBeInTheDocument();
    expect(screen.getByText('Humidity: 78%')).toBeInTheDocument();
    expect(screen.getByText('Wind: 15 km/h')).toBeInTheDocument(); // Converted from m/s
    expect(screen.getByText(/Updated:/)).toBeInTheDocument();
  });

  it('handles accessibility requirements correctly', () => {
    const mockWeatherData = {
      location: 'Test City',
      temperature: 20,
      condition: 'Clear',
      description: 'clear sky',
      icon: '01d',
      humidity: 60,
      windSpeed: 2.5,
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
      refetch: vi.fn(),
      retryCount: 0,
      isRetrying: false
    });

    mockUseWeatherConfig.mockReturnValue({
      config: {
        default_location: { lat: 40.7128, lon: -74.0060, name: 'New York, NY' }
      },
      loading: false,
      error: null
    });

    render(<WeatherWidget />);

    // Check ARIA labels and roles
    expect(screen.getByLabelText(/Current weather information/)).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /Refresh weather data/ })).toBeInTheDocument();

    // Check screen reader content
    const srContent = document.querySelector('.sr-only');
    expect(srContent).toBeInTheDocument();
  });

  it('handles weather config error gracefully', () => {
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
      config: null,
      loading: false,
      error: {
        type: 'CONFIG_ERROR',
        message: 'Configuration error',
        userMessage: 'Weather configuration is not available',
        retryable: false,
        details: {},
        timestamp: '2024-01-15T12:00:00Z'
      }
    });

    render(<WeatherWidget />);

    // Should fall back to hardcoded default location
    expect(screen.getByText('Weather information unavailable')).toBeInTheDocument();
  });

  it('prioritizes prop default location over config', () => {
    const propDefaultLocation = {
      lat: 48.8566,
      lon: 2.3522,
      name: 'Paris, France'
    };

    const mockWeatherData = {
      location: 'Paris, France',
      temperature: 18,
      condition: 'Clear',
      description: 'clear sky',
      icon: '01d',
      humidity: 55,
      windSpeed: 1.8,
      lastUpdated: '2024-01-15T12:00:00Z',
      coordinates: { lat: 48.8566, lon: 2.3522 }
    };

    mockUseGeolocation.mockReturnValue({
      location: null, // No user location
      error: null,
      loading: false,
      requestLocation: vi.fn()
    });

    mockUseWeatherData.mockReturnValue({
      data: mockWeatherData,
      loading: false,
      error: null,
      refetch: vi.fn(),
      retryCount: 0,
      isRetrying: false
    });

    mockUseWeatherConfig.mockReturnValue({
      config: {
        default_location: { lat: 40.7128, lon: -74.0060, name: 'New York, NY' }
      },
      loading: false,
      error: null
    });

    render(<WeatherWidget defaultLocation={propDefaultLocation} />);

    // Should use prop location, not config location
    expect(screen.getByText('Paris, France')).toBeInTheDocument();
  });
