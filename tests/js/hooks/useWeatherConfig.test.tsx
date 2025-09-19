import { renderHook, waitFor } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useWeatherConfig } from '../../../resources/js/hooks/use-weather-config';

// Mock the weather controller
vi.mock('../../../resources/js/actions/App/Http/Controllers/WeatherController', () => ({
  getConfig: {
    url: vi.fn(() => '/api/weather/config'),
  },
}));

// Mock fetch globally
const mockFetch = vi.fn();
global.fetch = mockFetch;

describe('useWeatherConfig', () => {
  const mockConfig = {
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

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('should initialize with loading state', () => {
    const { result } = renderHook(() => useWeatherConfig());

    expect(result.current.config).toBeNull();
    expect(result.current.loading).toBe(true);
    expect(result.current.error).toBeNull();
  });

  it('should fetch config successfully', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => mockConfig,
    });

    const { result } = renderHook(() => useWeatherConfig());

    expect(result.current.loading).toBe(true);

    await waitFor(() => {
      expect(result.current.loading).toBe(false);
      expect(result.current.config).toEqual(mockConfig);
      expect(result.current.error).toBeNull();
    });

    expect(mockFetch).toHaveBeenCalledWith('/api/weather/config', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    });
  });

  it('should handle API errors', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: false,
      status: 503,
      json: async () => ({ error: 'Weather widget is disabled' }),
    });

    const { result } = renderHook(() => useWeatherConfig());

    await waitFor(() => {
      expect(result.current.loading).toBe(false);
      expect(result.current.error).toBeTruthy();
      expect(result.current.error?.type).toBe('SERVICE_UNAVAILABLE');
      expect(result.current.config).toBeNull();
    });
  });

  it('should handle network errors', async () => {
    mockFetch.mockRejectedValueOnce(new Error('Network error'));

    const { result } = renderHook(() => useWeatherConfig());

    await waitFor(() => {
      expect(result.current.loading).toBe(false);
      expect(result.current.error).toBeTruthy();
      expect(result.current.error?.type).toBe('NETWORK_ERROR');
      expect(result.current.config).toBeNull();
    });
  });

  it('should handle invalid response data', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => null,
    });

    const { result } = renderHook(() => useWeatherConfig());

    await waitFor(() => {
      expect(result.current.loading).toBe(false);
      expect(result.current.error).toBeTruthy();
      expect(result.current.error?.type).toBe('INVALID_RESPONSE');
      expect(result.current.config).toBeNull();
    });
  });

  it('should validate required config fields', async () => {
    const incompleteConfig = {
      widget: {
        auto_refresh_interval: 1800
        // Missing default_location
      }
    };

    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => incompleteConfig,
    });

    const { result } = renderHook(() => useWeatherConfig());

    await waitFor(() => {
      expect(result.current.loading).toBe(false);
      expect(result.current.error).toBeTruthy();
      expect(result.current.error?.type).toBe('DATA_PARSING_ERROR');
      expect(result.current.config).toBeNull();
    });
  });

  it('should cache successful config responses', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => mockConfig,
    });

    const { result, rerender } = renderHook(() => useWeatherConfig());

    await waitFor(() => {
      expect(result.current.config).toEqual(mockConfig);
    });

    expect(mockFetch).toHaveBeenCalledTimes(1);

    // Rerender should not trigger another fetch
    rerender();

    expect(mockFetch).toHaveBeenCalledTimes(1);
    expect(result.current.config).toEqual(mockConfig);
  });
});
