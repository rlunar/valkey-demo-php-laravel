import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import WeatherWidget from '@/components/weather-widget';

// Mock fetch globally
const mockFetch = vi.fn();
global.fetch = mockFetch;

// Mock geolocation
const mockGeolocation = {
  getCurrentPosition: vi.fn(),
  watchPosition: vi.fn(),
  clearWatch: vi.fn(),
};

Object.defineProperty(global.navigator, 'geolocation', {
  value: mockGeolocation,
  writable: true,
});

describe('WeatherWidget Integration Tests', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  afterEach(() => {
    vi.clearAllMocks();
  });

  it('should complete full user flow from mount to weather display', async () => {
    const mockPosition = {
      coords: {
        latitude: 40.7128,
        longitude: -74.0060,
        accuracy: 100,
      },
      timestamp: Date.now(),
    };

    const mockConfigResponse = {
      default_location: {
        lat: 51.5074,
        lon: -0.1278,
        name: 'London, UK'
      },
      widget: {
        auto_refresh_interval: 1800,
        show_detailed_info: true,
        temperature_unit: 'celsius'
      }
    };

    const mockWeatherResponse = {
      location: 'New York, NY',
      temperature: 22,
      condition: 'Clear',
      description: 'clear sky',
      icon: '01d',
      humidity: 65,
      windSpeed: 3.2,
      lastUpdated: '2024-01-15T12:00:00Z',
      coordinates: { lat: 40.7128, lon: -74.0060 }
    };

    // Mock successful geolocation
    mockGeolocation.getCurrentPosition.mockImplementation((success) => {
      setTimeout(() => success(mockPosition), 100);
    });

    // Mock API responses
    mockFetch
      .mockResolvedValueOnce({
        ok: true,
        json: async () => mockConfigResponse,
      })
      .mockResolvedValueOnce({
        ok: true,
        json: async () => mockWeatherResponse,
      });

    render(<WeatherWidget />);

    // Initially should show loading state
    expect(screen.getByLabelText('Weather information loading')).toBeInTheDocument();

    // Wait for geolocation and weather data to load
    await waitFor(() => {
      expect(screen.getByText('New York, NY')).toBeInTheDocument();
    }, { timeout: 5000 });

    // Verify weather data is displayed
    expect(screen.getByText('22°C')).toBeInTheDocument();
    expect(screen.getByText('clear sky')).toBeInTheDocument();
    expect(screen.getByText('Humidity: 65%')).toBeInTheDocument();
    expect(screen.getByText('Wind: 12 km/h')).toBeInTheDocument();

    // Verify API calls were made
    expect(mockFetch).toHaveBeenCalledWith('/api/weather/config', expect.any(Object));
    expect(mockFetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/weather?lat=40.7128&lon=-74.006'),
      expect.any(Object)
    );
  });

  it('should handle geolocation denial and use default location', async () => {
    const mockError = {
      code: 1, // PERMISSION_DENIED
      PERMISSION_DENIED: 1,
      POSITION_UNAVAILABLE: 2,
      TIMEOUT: 3,
    };

    const mockConfigResponse = {
      default_location: {
        lat: 51.5074,
        lon: -0.1278,
        name: 'London, UK'
      },
      widget: {
        auto_refresh_interval: 1800,
        show_detailed_info: true,
        temperature_unit: 'celsius'
      }
    };

    const mockWeatherResponse = {
      location: 'London, UK',
      temperature: 15,
      condition: 'Clouds',
      description: 'overcast clouds',
      icon: '04d',
      humidity: 80,
      windSpeed: 2.1,
      lastUpdated: '2024-01-15T12:00:00Z',
      coordinates: { lat: 51.5074, lon: -0.1278 }
    };

    // Mock geolocation denial
    mockGeolocation.getCurrentPosition.mockImplementation((success, error) => {
      setTimeout(() => error(mockError), 100);
    });

    // Mock API responses
    mockFetch
      .mockResolvedValueOnce({
        ok: true,
        json: async () => mockConfigResponse,
      })
      .mockResolvedValueOnce({
        ok: true,
        json: async () => mockWeatherResponse,
      });

    render(<WeatherWidget />);

    // Wait for config and weather data to load
    await waitFor(() => {
      expect(screen.getByText('London, UK')).toBeInTheDocument();
    }, { timeout: 5000 });

    // Should show weather for default location
    expect(screen.getByText('15°C')).toBeInTheDocument();
    expect(screen.getByText('overcast clouds')).toBeInTheDocument();

    // Should use default location coordinates in API call
    expect(mockFetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/weather?lat=51.5074&lon=-0.1278'),
      expect.any(Object)
    );
  });

  it('should handle API errors and show retry functionality', async () => {
    const mockPosition = {
      coords: {
        latitude: 40.7128,
        longitude: -74.0060,
        accuracy: 100,
      },
      timestamp: Date.now(),
    };

    const mockConfigResponse = {
      default_location: {
        lat: 40.7128,
        lon: -74.0060,
        name: 'New York, NY'
      },
      widget: {
        auto_refresh_interval: 1800,
        show_detailed_info: true,
        temperature_unit: 'celsius'
      }
    };

    const mockWeatherResponse = {
      location: 'New York, NY',
      temperature: 22,
      condition: 'Clear',
      description: 'clear sky',
      icon: '01d',
      humidity: 65,
      windSpeed: 3.2,
      lastUpdated: '2024-01-15T12:00:00Z',
      coordinates: { lat: 40.7128, lon: -74.0060 }
    };

    // Mock successful geolocation
    mockGeolocation.getCurrentPosition.mockImplementation((success) => {
      setTimeout(() => success(mockPosition), 100);
    });

    // Mock API responses - config succeeds, weather fails then succeeds
    mockFetch
      .mockResolvedValueOnce({
        ok: true,
        json: async () => mockConfigResponse,
      })
      .mockResolvedValueOnce({
        ok: false,
        status: 503,
        json: async () => ({ error: 'Service unavailable' }),
      })
      .mockResolvedValueOnce({
        ok: true,
        json: async () => mockWeatherResponse,
      });

    render(<WeatherWidget />);

    // Wait for error state
    await waitFor(() => {
      expect(screen.getByLabelText('Weather information error')).toBeInTheDocument();
    }, { timeout: 5000 });

    // Should show error message
    expect(screen.getByText(/Weather data is currently unavailable/)).toBeInTheDocument();

    // Find and click retry button
    const retryButton = screen.getByText('Try again');
    fireEvent.click(retryButton);

    // Wait for successful retry
    await waitFor(() => {
      expect(screen.getByText('New York, NY')).toBeInTheDocument();
    }, { timeout: 5000 });

    // Should show weather data after retry
    expect(screen.getByText('22°C')).toBeInTheDocument();
    expect(screen.getByText('clear sky')).toBeInTheDocument();
  });

  it('should handle refresh functionality', async () => {
    const mockPosition = {
      coords: {
        latitude: 40.7128,
        longitude: -74.0060,
        accuracy: 100,
      },
      timestamp: Date.now(),
    };

    const mockConfigResponse = {
      default_location: {
        lat: 40.7128,
        lon: -74.0060,
        name: 'New York, NY'
      },
      widget: {
        auto_refresh_interval: 1800,
        show_detailed_info: true,
        temperature_unit: 'celsius'
      }
    };

    const mockWeatherResponse1 = {
      location: 'New York, NY',
      temperature: 22,
      condition: 'Clear',
      description: 'clear sky',
      icon: '01d',
      humidity: 65,
      windSpeed: 3.2,
      lastUpdated: '2024-01-15T12:00:00Z',
      coordinates: { lat: 40.7128, lon: -74.0060 }
    };

    const mockWeatherResponse2 = {
      location: 'New York, NY',
      temperature: 25,
      condition: 'Clear',
      description: 'clear sky',
      icon: '01d',
      humidity: 60,
      windSpeed: 2.8,
      lastUpdated: '2024-01-15T13:00:00Z',
      coordinates: { lat: 40.7128, lon: -74.0060 }
    };

    // Mock successful geolocation
    mockGeolocation.getCurrentPosition.mockImplementation((success) => {
      setTimeout(() => success(mockPosition), 100);
    });

    // Mock API responses
    mockFetch
      .mockResolvedValueOnce({
        ok: true,
        json: async () => mockConfigResponse,
      })
      .mockResolvedValueOnce({
        ok: true,
        json: async () => mockWeatherResponse1,
      })
      .mockResolvedValueOnce({
        ok: true,
        json: async () => mockWeatherResponse2,
      });

    render(<WeatherWidget />);

    // Wait for initial weather data
    await waitFor(() => {
      expect(screen.getByText('22°C')).toBeInTheDocument();
    }, { timeout: 5000 });

    // Find and click refresh button
    const refreshButton = screen.getByLabelText(/Refresh weather data/);
    fireEvent.click(refreshButton);

    // Wait for updated weather data
    await waitFor(() => {
      expect(screen.getByText('25°C')).toBeInTheDocument();
    }, { timeout: 5000 });

    // Should show updated humidity
    expect(screen.getByText('Humidity: 60%')).toBeInTheDocument();

    // Should have made 3 API calls (config + 2 weather)
    expect(mockFetch).toHaveBeenCalledTimes(3);
  });

  it('should handle config loading failure gracefully', async () => {
    const mockPosition = {
      coords: {
        latitude: 40.7128,
        longitude: -74.0060,
        accuracy: 100,
      },
      timestamp: Date.now(),
    };

    const mockWeatherResponse = {
      location: 'New York, NY',
      temperature: 22,
      condition: 'Clear',
      description: 'clear sky',
      icon: '01d',
      humidity: 65,
      windSpeed: 3.2,
      lastUpdated: '2024-01-15T12:00:00Z',
      coordinates: { lat: 40.7128, lon: -74.0060 }
    };

    // Mock successful geolocation
    mockGeolocation.getCurrentPosition.mockImplementation((success) => {
      setTimeout(() => success(mockPosition), 100);
    });

    // Mock API responses - config fails, weather succeeds
    mockFetch
      .mockResolvedValueOnce({
        ok: false,
        status: 503,
        json: async () => ({ error: 'Config unavailable' }),
      })
      .mockResolvedValueOnce({
        ok: true,
        json: async () => mockWeatherResponse,
      });

    render(<WeatherWidget />);

    // Should still show weather data using fallback location
    await waitFor(() => {
      expect(screen.getByText('New York, NY')).toBeInTheDocument();
    }, { timeout: 5000 });

    expect(screen.getByText('22°C')).toBeInTheDocument();
    expect(screen.getByText('clear sky')).toBeInTheDocument();
  });
});
