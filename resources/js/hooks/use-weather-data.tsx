import { useCallback, useEffect, useRef, useState } from 'react';
import { getCurrentWeather } from '../actions/App/Http/Controllers/WeatherController';

interface WeatherData {
  location: string;
  temperature: number;
  condition: string;
  description: string;
  icon: string;
  humidity: number;
  windSpeed: number;
  lastUpdated: string;
  coordinates: {
    lat: number;
    lon: number;
  };
}

interface UseWeatherDataReturn {
  data: WeatherData | null;
  loading: boolean;
  error: string | null;
  refetch: () => void;
}

interface UseWeatherDataConfig {
  autoRefreshInterval?: number; // milliseconds
  maxRetryAttempts?: number;
  retryDelay?: number; // base delay in milliseconds
}

const DEFAULT_CONFIG: UseWeatherDataConfig = {
  autoRefreshInterval: 30 * 60 * 1000, // 30 minutes
  maxRetryAttempts: 3,
  retryDelay: 1000, // 1 second base delay
};

export function useWeatherData(
  coordinates: { lat: number; lon: number } | null,
  config: UseWeatherDataConfig = DEFAULT_CONFIG
): UseWeatherDataReturn {
  const [data, setData] = useState<WeatherData | null>(null);
  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<string | null>(null);

  // Use refs to track retry state and intervals
  const retryTimeoutRef = useRef<NodeJS.Timeout | null>(null);
  const refreshIntervalRef = useRef<NodeJS.Timeout | null>(null);
  const retryCountRef = useRef<number>(0);
  const abortControllerRef = useRef<AbortController | null>(null);

  const fetchWeatherData = useCallback(async (
    coords: { lat: number; lon: number },
    isRetry: boolean = false
  ): Promise<void> => {
    // Cancel any existing request
    if (abortControllerRef.current) {
      abortControllerRef.current.abort();
    }

    // Create new abort controller for this request
    abortControllerRef.current = new AbortController();

    if (!isRetry) {
      setLoading(true);
      setError(null);
      retryCountRef.current = 0;
    }

    try {
      const response = await fetch(getCurrentWeather.url({
        query: {
          lat: coords.lat.toString(),
          lon: coords.lon.toString(),
        }
      }), {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        signal: abortControllerRef.current.signal,
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));

        // Handle specific error cases
        switch (response.status) {
          case 400:
            throw new Error('Invalid coordinates provided');
          case 404:
            throw new Error('Location not found');
          case 429:
            throw new Error('Weather service temporarily unavailable due to high demand');
          case 503:
            throw new Error('Weather service is currently unavailable');
          default:
            throw new Error(errorData.error || 'Failed to fetch weather data');
        }
      }

      const weatherData: WeatherData = await response.json();

      // Validate the response data
      if (!weatherData || typeof weatherData !== 'object') {
        throw new Error('Invalid weather data received');
      }

      setData(weatherData);
      setError(null);
      setLoading(false);
      retryCountRef.current = 0;

      // Set up auto-refresh interval
      if (refreshIntervalRef.current) {
        clearInterval(refreshIntervalRef.current);
      }

      if (config.autoRefreshInterval && config.autoRefreshInterval > 0) {
        refreshIntervalRef.current = setInterval(() => {
          fetchWeatherData(coords, false);
        }, config.autoRefreshInterval);
      }

    } catch (err) {
      // Don't handle aborted requests as errors
      if (err instanceof Error && err.name === 'AbortError') {
        return;
      }

      const errorMessage = err instanceof Error ? err.message : 'An unknown error occurred';

      // Check if we should retry
      const shouldRetry = retryCountRef.current < (config.maxRetryAttempts || DEFAULT_CONFIG.maxRetryAttempts!);
      const isRetryableError = !errorMessage.includes('Invalid coordinates') &&
                              !errorMessage.includes('Location not found');

      if (shouldRetry && isRetryableError) {
        retryCountRef.current += 1;

        // Calculate exponential backoff delay with jitter
        const baseDelay = config.retryDelay || DEFAULT_CONFIG.retryDelay!;
        const exponentialDelay = baseDelay * Math.pow(2, retryCountRef.current - 1);
        const jitter = Math.random() * 1000; // Add up to 1 second of jitter
        const totalDelay = exponentialDelay + jitter;

        console.warn(`Weather API request failed (attempt ${retryCountRef.current}/${config.maxRetryAttempts}). Retrying in ${Math.round(totalDelay)}ms...`, errorMessage);

        retryTimeoutRef.current = setTimeout(() => {
          fetchWeatherData(coords, true);
        }, totalDelay);
      } else {
        // All retries exhausted or non-retryable error
        setError(errorMessage);
        setLoading(false);
        console.error('Weather API request failed after all retries:', errorMessage);
      }
    }
  }, [config.autoRefreshInterval, config.maxRetryAttempts, config.retryDelay]);

  const refetch = useCallback(() => {
    if (coordinates) {
      // Clear any existing retry timeout
      if (retryTimeoutRef.current) {
        clearTimeout(retryTimeoutRef.current);
        retryTimeoutRef.current = null;
      }

      fetchWeatherData(coordinates, false);
    }
  }, [coordinates, fetchWeatherData]);

  // Effect to fetch data when coordinates change
  useEffect(() => {
    if (coordinates) {
      fetchWeatherData(coordinates, false);
    } else {
      // Clear data when coordinates are null
      setData(null);
      setError(null);
      setLoading(false);
    }

    // Cleanup function
    return () => {
      if (retryTimeoutRef.current) {
        clearTimeout(retryTimeoutRef.current);
        retryTimeoutRef.current = null;
      }
      if (refreshIntervalRef.current) {
        clearInterval(refreshIntervalRef.current);
        refreshIntervalRef.current = null;
      }
      if (abortControllerRef.current) {
        abortControllerRef.current.abort();
        abortControllerRef.current = null;
      }
    };
  }, [coordinates, fetchWeatherData]);

  // Cleanup on unmount
  useEffect(() => {
    return () => {
      if (retryTimeoutRef.current) {
        clearTimeout(retryTimeoutRef.current);
      }
      if (refreshIntervalRef.current) {
        clearInterval(refreshIntervalRef.current);
      }
      if (abortControllerRef.current) {
        abortControllerRef.current.abort();
      }
    };
  }, []);

  return {
    data,
    loading,
    error,
    refetch,
  };
}
