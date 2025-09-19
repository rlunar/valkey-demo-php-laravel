import { renderHook, waitFor } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useWeatherData } from '../../../resources/js/hooks/use-weather-data';

// Mock the weather controller
vi.mock('../../../resources/js/actions/App/Http/Controllers/WeatherController', () => ({
  getCurrentWeather: {
    url: vi.fn((options) => {
      const params = new URLSearchParams(options?.query || {});
      return `/api/weather?${params.toString()}`;
    }),
  },
}));

// Mock fetch globally
const mockFetch = vi.fn();
global.fetch = mockFetch;

describe('useWeatherData', () => {
  const mockCoordinates = { lat: 40.7128, lon: -74.0060 };
  const mockWeatherData = {
    location: 'New York',
    temperature: 22,
    condition: 'Clear',
    description: 'clear sky',
    icon: '01d',
    humidity: 65,
    windSpeed: 3.2,
    lastUpdated: '2023-10-01T12:00:00.000Z',
    coordinates: { lat: 40.7128, lon: -74.0060 },
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('should initialize with default state', () => {
    const { result } = renderHook(() => useWeatherData(null));

    expect(result.current.data).toBeNull();
    expect(result.current.loading).toBe(false);
    expect(result.current.error).toBeNull();
    expect(typeof result.current.refetch).toBe('function');
  });

  it('should fetch weather data successfully', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => mockWeatherData,
    });

    const { result } = renderHook(() => useWeatherData(mockCoordinates));

    expect(result.current.loading).toBe(true);

    await waitFor(() => {
      expect(result.current.loading).toBe(false);
      expect(result.current.data).toEqual(mockWeatherData);
      expect(result.current.error).toBeNull();
    });

    expect(mockFetch).toHaveBeenCalledWith(
      '/api/weather?lat=40.7128&lon=-74.006',
      expect.objectContaining({
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      })
    );
  });

  it('should handle API errors', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: false,
      status: 404,
      json: async () => ({ error: 'Location not found' }),
    });

    const { result } = renderHook(() => useWeatherData(mockCoordinates));

    await waitFor(() => {
      expect(result.current.loading).toBe(false);
      expect(result.current.error).toBeTruthy();
      expect(result.current.error?.type).toBe('LOCATION_NOT_FOUND');
      expect(result.current.error?.userMessage).toBe('Unable to find weather data for this location.');
      expect(result.current.data).toBeNull();
    });
  });

  it('should handle network errors with retry', async () => {
    // First call fails, second succeeds
    mockFetch
      .mockRejectedValueOnce(new Error('Network error'))
      .mockResolvedValueOnce({
        ok: true,
        json: async () => mockWeatherData,
      });

    const { result } = renderHook(() =>
      useWeatherData(mockCoordinates, { maxRetryAttempts: 2, retryDelay: 10 })
    );

    expect(result.current.loading).toBe(true);

    await waitFor(() => {
      expect(result.current.loading).toBe(false);
      expect(result.current.data).toEqual(mockWeatherData);
      expect(result.current.error).toBeNull();
    }, { timeout: 15000 });

    expect(mockFetch).toHaveBeenCalledTimes(2);
  }, 15000);

  it('should handle refetch functionality', async () => {
    mockFetch.mockResolvedValue({
      ok: true,
      json: async () => mockWeatherData,
    });

    const { result } = renderHook(() => useWeatherData(mockCoordinates));

    await waitFor(() => {
      expect(result.current.loading).toBe(false);
    });

    expect(mockFetch).toHaveBeenCalledTimes(1);

    // Manually trigger refetch
    result.current.refetch();

    await waitFor(() => {
      expect(mockFetch).toHaveBeenCalledTimes(2);
    });
  });

  it('should clear data when coordinates become null', async () => {
    mockFetch.mockResolvedValue({
      ok: true,
      json: async () => mockWeatherData,
    });

    const { result, rerender } = renderHook(
      ({ coords }) => useWeatherData(coords),
      { initialProps: { coords: mockCoordinates } }
    );

    await waitFor(() => {
      expect(result.current.data).toEqual(mockWeatherData);
    });

    // Change coordinates to null
    rerender({ coords: null });

    expect(result.current.data).toBeNull();
    expect(result.current.loading).toBe(false);
    expect(result.current.error).toBeNull();
  });
});
