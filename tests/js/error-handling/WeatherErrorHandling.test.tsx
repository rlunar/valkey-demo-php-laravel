import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import WeatherWidget from '@/components/weather-widget';
import { WeatherErrorType } from '@/types/weather-errors';

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

describe('Weather Error Handling', () => {
  beforeEach(() => {
    vi.clearAllMocks();

    // Default mocks
    mockUseGeolocation.mockReturnValue({
      location: null,
      error: null,
      loading: false,
      requestLocation: vi.fn()
    });

    mockUseWeatherConfig.mockReturnValue({
      config: {
        default_location: { lat: 40.7128, lon: -74.0060, name: 'New York, NY' }
      },
      loading: false,
      error: null
    });
  });

  it('handles network errors with appropriate messaging', () => {
    const networkError = {
      type: WeatherErrorType.NETWORK_ERROR,
      message: 'Network connection failed',
      userMessage: 'Unable to connect to weather service. Please check your internet connection.',
      retryable: true,
      details: {},
      timestamp: '2024-01-15T12:00:00Z'
    };

    mockUseWeatherData.mockReturnValue({
      data: null,
      loading: false,
      error: networkError,
      refetch: vi.fn(),
      retryCount: 0,
      isRetrying: false
    });

    render(<WeatherWidget />);

    expect(screen.getByText('Unable to connect to weather service. Please check your internet connection.')).toBeInTheDocument();
    expect(screen.getByText('Check connection')).toBeInTheDocument();
  });

  it('handles connection timeout errors', () => {
    const timeoutError = {
      type: WeatherErrorType.CONNECTION_TIMEOUT,
      message: 'Connection timeout',
      userMessage: 'Request timed out. Please check your connection and try again.',
      retryable: true,
      details: {},
      timestamp: '2024-01-15T12:00:00Z'
    };

    mockUseWeatherData.mockReturnValue({
      data: null,
      loading: false,
      error: timeoutError,
      refetch: vi.fn(),
      retryCount: 0,
      isRetrying: false
    });

    render(<WeatherWidget />);

    expect(screen.getByText('Request timed out. Please check your connection and try again.')).toBeInTheDocument();
    expect(screen.getByText('Check connection')).toBeInTheDocument();
  });

  it('handles service unavailable errors', () => {
    const serviceError = {
      type: WeatherErrorType.SERVICE_UNAVAILABLE,
      message: 'Service unavailable',
      userMessage: 'Weather service is temporarily unavailable. Please try again later.',
      retryable: true,
      details: {},
      timestamp: '2024-01-15T12:00:00Z'
    };

    mockUseWeatherData.mockReturnValue({
      data: null,
      loading: false,
      error: serviceError,
      refetch: vi.fn(),
      retryCount: 0,
      isRetrying: false
    });

    render(<WeatherWidget />);

    expect(screen.getByText('Weather service is temporarily unavailable. Please try again later.')).toBeInTheDocument();
    expect(screen.getByText('Try again')).toBeInTheDocument();
  });

  it('handles location not found errors', () => {
    const locationError = {
      type: WeatherErrorType.LOCATION_NOT_FOUND,
      message: 'Location not found',
      userMessage: 'Unable to find weather data for this location.',
      retryable: false,
      details: {},
      timestamp: '2024-01-15T12:00:00Z'
    };

    mockUseWeatherData.mockReturnValue({
      data: null,
      loading: false,
      error: locationError,
      refetch: vi.fn(),
      retryCount: 0,
      isRetrying: false
    });

    render(<WeatherWidget />);

    expect(screen.getByText('Unable to find weather data for this location.')).toBeInTheDocument();
    // Should not show retry button for non-retryable errors
    expect(screen.queryByText('Try again')).not.toBeInTheDocument();
  });

  it('handles geolocation denied gracefully', () => {
    const geolocationError = {
      type: WeatherErrorType.GEOLOCATION_DENIED,
      message: 'Geolocation denied',
      userMessage: 'Location access denied. Showing weather for default location.',
      retryable: false,
      details: {},
      timestamp: '2024-01-15T12:00:00Z'
    };

    mockUseGeolocation.mockReturnValue({
      location: null,
      error: geolocationError,
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

    render(<WeatherWidget />);

    // Should not show error state for geolocation denial
    expect(screen.queryByLabelText('Weather information error')).not.toBeInTheDocument();
    // Should show fallback state instead
    expect(screen.getByText('Weather information unavailable')).toBeInTheDocument();
  });

  it('handles geolocation timeout gracefully', () => {
    const geolocationError = {
      type: WeatherErrorType.GEOLOCATION_TIMEOUT,
      message: 'Geolocation timeout',
      userMessage: 'Location request timed out. Showing weather for default location.',
      retryable: false,
      details: {},
      timestamp: '2024-01-15T12:00:00Z'
    };

    mockUseGeolocation.mockReturnValue({
      location: null,
      error: geolocationError,
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

    render(<WeatherWidget />);

    // Should not show error state for geolocation timeout
    expect(screen.queryByLabelText('Weather information error')).not.toBeInTheDocument();
  });

  it('handles geolocation unavailable gracefully', () => {
    const geolocationError = {
      type: WeatherErrorType.GEOLOCATION_UNAVAILABLE,
      message: 'Geolocation unavailable',
      userMessage: 'Unable to determine your location. Showing weather for default location.',
      retryable: false,
      details: {},
      timestamp: '2024-01-15T12:00:00Z'
    };

    mockUseGeolocation.mockReturnValue({
      location: null,
      error: geolocationError,
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

    render(<WeatherWidget />);

    // Should not show error state for geolocation unavailable
    expect(screen.queryByLabelText('Weather information error')).not.toBeInTheDocument();
  });

  it('handles invalid response errors', () => {
    const invalidResponseError = {
      type: WeatherErrorType.INVALID_RESPONSE,
      message: 'Invalid response',
      userMessage: 'Received invalid data from weather service. Please try again.',
      retryable: true,
      details: {},
      timestamp: '2024-01-15T12:00:00Z'
    };

    mockUseWeatherData.mockReturnValue({
      data: null,
      loading: false,
      error: invalidResponseError,
      refetch: vi.fn(),
      retryCount: 0,
      isRetrying: false
    });

    render(<WeatherWidget />);

    expect(screen.getByText('Received invalid data from weather service. Please try again.')).toBeInTheDocument();
    expect(screen.getByText('Try again')).toBeInTheDocument();
  });

  it('handles data parsing errors', () => {
    const parsingError = {
      type: WeatherErrorType.DATA_PARSING_ERROR,
      message: 'Data parsing error',
      userMessage: 'Unable to process weather data. Please try again.',
      retryable: true,
      details: {},
      timestamp: '2024-01-15T12:00:00Z'
    };

    mockUseWeatherData.mockReturnValue({
      data: null,
      loading: false,
      error: parsingError,
      refetch: vi.fn(),
      retryCount: 0,
      isRetrying: false
    });

    render(<WeatherWidget />);

    expect(screen.getByText('Unable to process weather data. Please try again.')).toBeInTheDocument();
    expect(screen.getByText('Try again')).toBeInTheDocument();
  });

  it('handles unknown errors', () => {
    const unknownError = {
      type: WeatherErrorType.UNKNOWN_ERROR,
      message: 'Unknown error',
      userMessage: 'An unexpected error occurred. Please try again.',
      retryable: true,
      details: {},
      timestamp: '2024-01-15T12:00:00Z'
    };

    mockUseWeatherData.mockReturnValue({
      data: null,
      loading: false,
      error: unknownError,
      refetch: vi.fn(),
      retryCount: 0,
      isRetrying: false
    });

    render(<WeatherWidget />);

    expect(screen.getByText('An unexpected error occurred. Please try again.')).toBeInTheDocument();
    expect(screen.getByText('Try again')).toBeInTheDocument();
  });

  it('shows retry count during retry attempts', () => {
    const networkError = {
      type: WeatherErrorType.NETWORK_ERROR,
      message: 'Network error',
      userMessage: 'Unable to connect to weather service. Please check your internet connection.',
      retryable: true,
      details: {},
      timestamp: '2024-01-15T12:00:00Z'
    };

    mockUseWeatherData.mockReturnValue({
      data: null,
      loading: true,
      error: networkError,
      refetch: vi.fn(),
      retryCount: 2,
      isRetrying: true
    });

    render(<WeatherWidget />);

    expect(screen.getByText('Retry 2/3')).toBeInTheDocument();
    expect(screen.getAllByText('Retrying weather data...')).toHaveLength(2); // One for screen reader, one visible
  });

  it('disables retry button during retry attempts', () => {
    const networkError = {
      type: WeatherErrorType.NETWORK_ERROR,
      message: 'Network error',
      userMessage: 'Unable to connect to weather service. Please check your internet connection.',
      retryable: true,
      details: {},
      timestamp: '2024-01-15T12:00:00Z'
    };

    mockUseWeatherData.mockReturnValue({
      data: null,
      loading: false,
      error: networkError,
      refetch: vi.fn(),
      retryCount: 1,
      isRetrying: true
    });

    render(<WeatherWidget />);

    const retryButton = screen.getByRole('button', { name: /Retry loading weather data/ });
    expect(retryButton).toBeDisabled();
  });

  it('shows appropriate error icons for different error types', () => {
    const networkError = {
      type: WeatherErrorType.NETWORK_ERROR,
      message: 'Network error',
      userMessage: 'Unable to connect to weather service. Please check your internet connection.',
      retryable: true,
      details: {},
      timestamp: '2024-01-15T12:00:00Z'
    };

    mockUseWeatherData.mockReturnValue({
      data: null,
      loading: false,
      error: networkError,
      refetch: vi.fn(),
      retryCount: 0,
      isRetrying: false
    });

    render(<WeatherWidget />);

    // Check that an error icon is present
    const errorIcon = document.querySelector('svg[aria-hidden="true"]');
    expect(errorIcon).toBeInTheDocument();
  });

  it('provides helpful context for rate limit errors', () => {
    const rateLimitError = {
      type: WeatherErrorType.RATE_LIMIT_EXCEEDED,
      message: 'Rate limit exceeded',
      userMessage: 'Weather service temporarily unavailable due to high demand. Please try again later.',
      retryable: true,
      details: {},
      timestamp: '2024-01-15T12:00:00Z'
    };

    mockUseWeatherData.mockReturnValue({
      data: null,
      loading: false,
      error: rateLimitError,
      refetch: vi.fn(),
      retryCount: 0,
      isRetrying: false
    });

    render(<WeatherWidget />);

    expect(screen.getByText('Weather service temporarily unavailable due to high demand. Please try again later.')).toBeInTheDocument();
    expect(screen.getByText('This usually resolves within a few minutes.')).toBeInTheDocument();
    expect(screen.getByText('Try again later')).toBeInTheDocument();
  });

  it('provides helpful context for geolocation denied errors', () => {
    const geolocationError = {
      type: WeatherErrorType.GEOLOCATION_DENIED,
      message: 'Geolocation denied',
      userMessage: 'Location access denied. Showing weather for default location.',
      retryable: false,
      details: {},
      timestamp: '2024-01-15T12:00:00Z'
    };

    mockUseGeolocation.mockReturnValue({
      location: null,
      error: geolocationError,
      loading: false,
      requestLocation: vi.fn()
    });

    mockUseWeatherData.mockReturnValue({
      data: null,
      loading: false,
      error: geolocationError,
      refetch: vi.fn(),
      retryCount: 0,
      isRetrying: false
    });

    render(<WeatherWidget />);

    expect(screen.getByText('Location access denied. Showing weather for default location.')).toBeInTheDocument();
    expect(screen.getByText('You can enable location access in your browser settings to see local weather.')).toBeInTheDocument();
    expect(screen.getByText('Showing weather for New York, NY')).toBeInTheDocument();
  });
});
