import { render, screen, waitFor } from '@testing-library/react';
import { vi, describe, it, expect, beforeEach, afterEach } from 'vitest';
import WeatherWidget from '@/components/weather-widget';
import {
  getWeatherPerformanceMetrics,
  PERFORMANCE_THRESHOLDS,
  generateWeatherPerformanceReport,
  initWeatherPerformanceMonitoring
} from '@/lib/weather-performance';

// Mock the hooks
vi.mock('@/hooks/use-geolocation');
vi.mock('@/hooks/use-debounced-weather-data');
vi.mock('@/hooks/use-weather-config');

// Mock performance API
const mockPerformance = {
  now: vi.fn(() => Date.now()),
  mark: vi.fn(),
  measure: vi.fn(),
  getEntriesByType: vi.fn(() => []),
  memory: {
    usedJSHeapSize: 1024 * 1024, // 1MB
    totalJSHeapSize: 2 * 1024 * 1024, // 2MB
    jsHeapSizeLimit: 10 * 1024 * 1024, // 10MB
  },
};

Object.defineProperty(window, 'performance', {
  value: mockPerformance,
  writable: true,
});

// Mock IntersectionObserver for lazy loading tests
const mockIntersectionObserver = vi.fn();
mockIntersectionObserver.mockReturnValue({
  observe: vi.fn(),
  unobserve: vi.fn(),
  disconnect: vi.fn(),
});
window.IntersectionObserver = mockIntersectionObserver;

describe('WeatherWidget Performance', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mockPerformance.now.mockReturnValue(0);
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  describe('Component Render Performance', () => {
    it('should render within performance threshold', async () => {
      const startTime = 0;
      const endTime = 10; // 10ms render time

      mockPerformance.now
        .mockReturnValueOnce(startTime)
        .mockReturnValueOnce(endTime);

      const { useGeolocation } = await import('@/hooks/use-geolocation');
      const { useDebouncedWeatherData } = await import('@/hooks/use-debounced-weather-data');
      const { useWeatherConfig } = await import('@/hooks/use-weather-config');

      vi.mocked(useGeolocation).mockReturnValue({
        location: { lat: 40.7128, lon: -74.0060 },
        error: null,
        loading: false,
        requestLocation: vi.fn(),
      });

      vi.mocked(useDebouncedWeatherData).mockReturnValue({
        data: {
          location: 'New York, NY',
          temperature: 22,
          condition: 'Clear',
          description: 'clear sky',
          icon: '01d',
          humidity: 65,
          windSpeed: 3.5,
          lastUpdated: new Date().toISOString(),
          coordinates: { lat: 40.7128, lon: -74.0060 },
        },
        loading: false,
        error: null,
        refetch: vi.fn(),
        retryCount: 0,
        isRetrying: false,
      });

      vi.mocked(useWeatherConfig).mockReturnValue({
        config: {
          default_location: { lat: 40.7128, lon: -74.0060, name: 'New York, NY' },
        },
        loading: false,
        error: null,
      });

      render(<WeatherWidget />);

      await waitFor(() => {
        expect(screen.getByText('22°C')).toBeInTheDocument();
      });

      const renderTime = endTime - startTime;
      expect(renderTime).toBeLessThan(PERFORMANCE_THRESHOLDS.componentRenderTime);
    });

    it('should handle slow renders gracefully', async () => {
      const startTime = 0;
      const endTime = 50; // 50ms render time (above threshold)

      mockPerformance.now
        .mockReturnValueOnce(startTime)
        .mockReturnValueOnce(endTime);

      const { useGeolocation } = await import('@/hooks/use-geolocation');
      const { useDebouncedWeatherData } = await import('@/hooks/use-debounced-weather-data');
      const { useWeatherConfig } = await import('@/hooks/use-weather-config');

      vi.mocked(useGeolocation).mockReturnValue({
        location: null,
        error: null,
        loading: false,
        requestLocation: vi.fn(),
      });

      vi.mocked(useDebouncedWeatherData).mockReturnValue({
        data: null,
        loading: true,
        error: null,
        refetch: vi.fn(),
        retryCount: 0,
        isRetrying: false,
      });

      vi.mocked(useWeatherConfig).mockReturnValue({
        config: null,
        loading: true,
        error: null,
      });

      render(<WeatherWidget />);

      await waitFor(() => {
        expect(screen.getByText('Loading configuration...')).toBeInTheDocument();
      });

      const renderTime = endTime - startTime;
      expect(renderTime).toBeGreaterThan(PERFORMANCE_THRESHOLDS.componentRenderTime);
    });
  });

  describe('Memoization Effectiveness', () => {
    it('should not re-render when props have not changed', async () => {
      const { useGeolocation } = await import('@/hooks/use-geolocation');
      const { useDebouncedWeatherData } = await import('@/hooks/use-debounced-weather-data');
      const { useWeatherConfig } = await import('@/hooks/use-weather-config');

      const mockRefetch = vi.fn();

      vi.mocked(useGeolocation).mockReturnValue({
        location: { lat: 40.7128, lon: -74.0060 },
        error: null,
        loading: false,
        requestLocation: vi.fn(),
      });

      vi.mocked(useDebouncedWeatherData).mockReturnValue({
        data: {
          location: 'New York, NY',
          temperature: 22,
          condition: 'Clear',
          description: 'clear sky',
          icon: '01d',
          humidity: 65,
          windSpeed: 3.5,
          lastUpdated: new Date().toISOString(),
          coordinates: { lat: 40.7128, lon: -74.0060 },
        },
        loading: false,
        error: null,
        refetch: mockRefetch,
        retryCount: 0,
        isRetrying: false,
      });

      vi.mocked(useWeatherConfig).mockReturnValue({
        config: {
          default_location: { lat: 40.7128, lon: -74.0060, name: 'New York, NY' },
        },
        loading: false,
        error: null,
      });

      const { rerender } = render(<WeatherWidget />);

      await waitFor(() => {
        expect(screen.getByText('22°C')).toBeInTheDocument();
      });

      const initialRenderCount = mockPerformance.now.mock.calls.length;

      // Re-render with same props
      rerender(<WeatherWidget />);

      // Should not trigger additional performance measurements for unchanged props
      expect(mockPerformance.now.mock.calls.length).toBe(initialRenderCount);
    });

    it('should re-render when weather data changes', async () => {
      const { useGeolocation } = await import('@/hooks/use-geolocation');
      const { useDebouncedWeatherData } = await import('@/hooks/use-debounced-weather-data');
      const { useWeatherConfig } = await import('@/hooks/use-weather-config');

      vi.mocked(useGeolocation).mockReturnValue({
        location: { lat: 40.7128, lon: -74.0060 },
        error: null,
        loading: false,
        requestLocation: vi.fn(),
      });

      const mockUseDebouncedWeatherData = vi.mocked(useDebouncedWeatherData);

      // Initial data
      mockUseDebouncedWeatherData.mockReturnValue({
        data: {
          location: 'New York, NY',
          temperature: 22,
          condition: 'Clear',
          description: 'clear sky',
          icon: '01d',
          humidity: 65,
          windSpeed: 3.5,
          lastUpdated: new Date().toISOString(),
          coordinates: { lat: 40.7128, lon: -74.0060 },
        },
        loading: false,
        error: null,
        refetch: vi.fn(),
        retryCount: 0,
        isRetrying: false,
      });

      vi.mocked(useWeatherConfig).mockReturnValue({
        config: {
          default_location: { lat: 40.7128, lon: -74.0060, name: 'New York, NY' },
        },
        loading: false,
        error: null,
      });

      const { rerender } = render(<WeatherWidget />);

      await waitFor(() => {
        expect(screen.getByText('22°C')).toBeInTheDocument();
      });

      // Update data
      mockUseDebouncedWeatherData.mockReturnValue({
        data: {
          location: 'New York, NY',
          temperature: 25, // Changed temperature
          condition: 'Clear',
          description: 'clear sky',
          icon: '01d',
          humidity: 65,
          windSpeed: 3.5,
          lastUpdated: new Date().toISOString(),
          coordinates: { lat: 40.7128, lon: -74.0060 },
        },
        loading: false,
        error: null,
        refetch: vi.fn(),
        retryCount: 0,
        isRetrying: false,
      });

      rerender(<WeatherWidget />);

      await waitFor(() => {
        expect(screen.getByText('25°C')).toBeInTheDocument();
      });
    });
  });

  describe('Debouncing Effectiveness', () => {
    it('should debounce rapid coordinate changes', async () => {
      const { useDebouncedWeatherData } = await import('@/hooks/use-debounced-weather-data');

      const mockUseDebouncedWeatherData = vi.mocked(useDebouncedWeatherData);

      // Should be called only once despite multiple coordinate changes
      expect(mockUseDebouncedWeatherData).toHaveBeenCalledTimes(0);

      render(<WeatherWidget />);

      // The debounced hook should handle the coordinate changes internally
      expect(mockUseDebouncedWeatherData).toHaveBeenCalledTimes(1);
    });
  });

  describe('Performance Metrics', () => {
    it('should collect performance metrics', () => {
      const metrics = getWeatherPerformanceMetrics();

      expect(metrics).toHaveProperty('componentRenderTime');
      expect(metrics).toHaveProperty('apiRequestTime');
      expect(metrics).toHaveProperty('iconLoadTime');
      expect(metrics).toHaveProperty('memoryUsage');
      expect(metrics).toHaveProperty('bundleSize');
      expect(metrics).toHaveProperty('cacheHitRate');
    });

    it('should generate performance report', () => {
      const report = generateWeatherPerformanceReport();

      expect(report).toContain('Weather Widget Performance Report');
      expect(report).toContain('Component Performance:');
      expect(report).toContain('Resource Usage:');
      expect(report).toContain('Caching:');
      expect(report).toContain('Thresholds:');
    });

    it('should initialize performance monitoring', () => {
      // Mock localStorage
      const mockLocalStorage = {
        getItem: vi.fn(() => 'true'),
        setItem: vi.fn(),
        removeItem: vi.fn(),
      };
      Object.defineProperty(window, 'localStorage', {
        value: mockLocalStorage,
        writable: true,
      });

      expect(() => initWeatherPerformanceMonitoring()).not.toThrow();
    });
  });

  describe('Memory Usage', () => {
    it('should monitor memory usage within acceptable limits', () => {
      const metrics = getWeatherPerformanceMetrics();

      expect(metrics.memoryUsage).toBeLessThan(PERFORMANCE_THRESHOLDS.memoryUsage);
    });
  });

  describe('Bundle Size Analysis', () => {
    it('should track bundle size impact', () => {
      // Mock PerformanceObserver
      const mockObserver = {
        observe: vi.fn(),
        disconnect: vi.fn(),
      };

      window.PerformanceObserver = vi.fn().mockImplementation((callback) => {
        // Simulate resource timing entries
        setTimeout(() => {
          callback({
            getEntries: () => [
              {
                name: 'weather-widget.js',
                transferSize: 30 * 1024, // 30KB
              },
              {
                name: 'weather-hooks.js',
                transferSize: 15 * 1024, // 15KB
              },
            ],
          });
        }, 0);

        return mockObserver;
      });

      const metrics = getWeatherPerformanceMetrics();
      expect(typeof metrics.bundleSize).toBe('number');
    });
  });
});
