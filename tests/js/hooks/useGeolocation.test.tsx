import { renderHook, act, waitFor } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { useGeolocation } from '../../../resources/js/hooks/use-geolocation';

// Mock geolocation
const mockGeolocation = {
  getCurrentPosition: vi.fn(),
  watchPosition: vi.fn(),
  clearWatch: vi.fn(),
};

// Mock navigator.geolocation
Object.defineProperty(global.navigator, 'geolocation', {
  value: mockGeolocation,
  writable: true,
});

describe('useGeolocation', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  it('should initialize with loading state and no location', () => {
    const { result } = renderHook(() => useGeolocation());

    expect(result.current.location).toBeNull();
    expect(result.current.error).toBeNull();
    expect(result.current.loading).toBe(true);
    expect(typeof result.current.requestLocation).toBe('function');
  });

  it('should request location on mount', () => {
    renderHook(() => useGeolocation());

    expect(mockGeolocation.getCurrentPosition).toHaveBeenCalledWith(
      expect.any(Function),
      expect.any(Function),
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 300000,
      }
    );
  });

  it('should handle successful location retrieval', async () => {
    const mockPosition = {
      coords: {
        latitude: 40.7128,
        longitude: -74.0060,
      },
    };

    mockGeolocation.getCurrentPosition.mockImplementation((success) => {
      success(mockPosition);
    });

    const { result } = renderHook(() => useGeolocation());

    await waitFor(() => {
      expect(result.current.loading).toBe(false);
    });

    expect(result.current.location).toEqual({
      lat: 40.7128,
      lon: -74.0060,
    });
    expect(result.current.error).toBeNull();
  });

  it('should handle permission denied error', async () => {
    const mockError = {
      code: 1, // PERMISSION_DENIED
      PERMISSION_DENIED: 1,
      POSITION_UNAVAILABLE: 2,
      TIMEOUT: 3,
    };

    mockGeolocation.getCurrentPosition.mockImplementation((success, error) => {
      error(mockError);
    });

    const { result } = renderHook(() => useGeolocation());

    await waitFor(() => {
      expect(result.current.loading).toBe(false);
    });

    expect(result.current.location).toBeNull();
    expect(result.current.error).toBeTruthy();
    expect(result.current.error?.type).toBe('GEOLOCATION_DENIED');
    expect(result.current.error?.userMessage).toBe('Location access denied. Showing weather for default location.');
  });

  it('should handle position unavailable error', async () => {
    const mockError = {
      code: 2, // POSITION_UNAVAILABLE
      PERMISSION_DENIED: 1,
      POSITION_UNAVAILABLE: 2,
      TIMEOUT: 3,
    };

    mockGeolocation.getCurrentPosition.mockImplementation((success, error) => {
      error(mockError);
    });

    const { result } = renderHook(() => useGeolocation());

    await waitFor(() => {
      expect(result.current.loading).toBe(false);
    });

    expect(result.current.location).toBeNull();
    expect(result.current.error).toBeTruthy();
    expect(result.current.error?.type).toBe('GEOLOCATION_UNAVAILABLE');
    expect(result.current.error?.userMessage).toBe('Unable to determine your location. Showing weather for default location.');
  });

  it('should handle timeout error', async () => {
    const mockError = {
      code: 3, // TIMEOUT
      PERMISSION_DENIED: 1,
      POSITION_UNAVAILABLE: 2,
      TIMEOUT: 3,
    };

    mockGeolocation.getCurrentPosition.mockImplementation((success, error) => {
      error(mockError);
    });

    const { result } = renderHook(() => useGeolocation());

    await waitFor(() => {
      expect(result.current.loading).toBe(false);
    });

    expect(result.current.location).toBeNull();
    expect(result.current.error).toBeTruthy();
    expect(result.current.error?.type).toBe('GEOLOCATION_TIMEOUT');
    expect(result.current.error?.userMessage).toBe('Location request timed out. Showing weather for default location.');
  });

  it('should handle unknown error', async () => {
    const mockError = {
      code: 999, // Unknown error code
      PERMISSION_DENIED: 1,
      POSITION_UNAVAILABLE: 2,
      TIMEOUT: 3,
    };

    mockGeolocation.getCurrentPosition.mockImplementation((success, error) => {
      error(mockError);
    });

    const { result } = renderHook(() => useGeolocation());

    await waitFor(() => {
      expect(result.current.loading).toBe(false);
    });

    expect(result.current.location).toBeNull();
    expect(result.current.error).toBeTruthy();
    expect(result.current.error?.type).toBe('UNKNOWN_ERROR');
    expect(result.current.error?.userMessage).toBe('An unexpected error occurred. Please try again.');
  });

  it('should handle unsupported geolocation', () => {
    // Temporarily remove geolocation support
    const originalGeolocation = global.navigator.geolocation;
    Object.defineProperty(global.navigator, 'geolocation', {
      value: undefined,
      writable: true,
    });

    const { result } = renderHook(() => useGeolocation());

    expect(result.current.location).toBeNull();
    expect(result.current.error).toBeTruthy();
    expect(result.current.error?.type).toBe('GEOLOCATION_UNSUPPORTED');
    expect(result.current.error?.userMessage).toBe('Location services are not supported by your browser. Showing weather for default location.');
    expect(result.current.loading).toBe(false);

    // Restore geolocation
    Object.defineProperty(global.navigator, 'geolocation', {
      value: originalGeolocation,
      writable: true,
    });
  });

  it('should allow manual location request', async () => {
    const mockPosition = {
      coords: {
        latitude: 51.5074,
        longitude: -0.1278,
      },
    };

    mockGeolocation.getCurrentPosition.mockImplementation((success) => {
      success(mockPosition);
    });

    const { result } = renderHook(() => useGeolocation());

    // Clear the initial call
    vi.clearAllMocks();

    act(() => {
      result.current.requestLocation();
    });

    expect(mockGeolocation.getCurrentPosition).toHaveBeenCalledTimes(1);

    await waitFor(() => {
      expect(result.current.loading).toBe(false);
    });

    expect(result.current.location).toEqual({
      lat: 51.5074,
      lon: -0.1278,
    });
  });

  it('should use custom configuration', () => {
    const customConfig = {
      enableHighAccuracy: false,
      timeout: 5000,
      maximumAge: 60000,
    };

    renderHook(() => useGeolocation(customConfig));

    expect(mockGeolocation.getCurrentPosition).toHaveBeenCalledWith(
      expect.any(Function),
      expect.any(Function),
      customConfig
    );
  });

  it('should clear error when requesting location again', async () => {
    // First, simulate an error
    const mockError = {
      code: 1, // PERMISSION_DENIED
      PERMISSION_DENIED: 1,
      POSITION_UNAVAILABLE: 2,
      TIMEOUT: 3,
    };

    mockGeolocation.getCurrentPosition.mockImplementationOnce((success, error) => {
      error(mockError);
    });

    const { result } = renderHook(() => useGeolocation());

    await waitFor(() => {
      expect(result.current.error?.type).toBe('GEOLOCATION_DENIED');
    });

    // Now simulate a successful request
    const mockPosition = {
      coords: {
        latitude: 40.7128,
        longitude: -74.0060,
      },
    };

    mockGeolocation.getCurrentPosition.mockImplementationOnce((success) => {
      success(mockPosition);
    });

    act(() => {
      result.current.requestLocation();
    });

    // Wait for the final state
    await waitFor(() => {
      expect(result.current.loading).toBe(false);
    });

    expect(result.current.location).toEqual({
      lat: 40.7128,
      lon: -74.0060,
    });
    expect(result.current.error).toBeNull();
  });
});
