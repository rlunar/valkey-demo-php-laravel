import { useCallback, useEffect, useRef, useState } from 'react';
// Weather API endpoint URL builder
const getCurrentWeather = {
  url: ({ query }: { query: { lat: string; lon: string } }) => {
    const params = new URLSearchParams(query);
    return `/api/weather?${params.toString()}`;
  }
};
import { WeatherError, WeatherErrorType, createWeatherError, parseHttpError, parseNetworkError } from '@/types/weather-errors';
import { weatherLogger, logApiRequest, logRetryAttempt } from '@/lib/weather-logger';

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
  error: WeatherError | null;
  refetch: () => void;
  retryCount: number;
  isRetrying: boolean;
}

interface UseWeatherDataConfig {
  autoRefreshInterval?: number; // milliseconds
  maxRetryAttempts?: number;
  retryDelay?: number; // base delay in milliseconds
  retryMultiplier?: number; // exponential backoff multiplier
  maxRetryDelay?: number; // maximum retry delay
}

const DEFAULT_CONFIG: UseWeatherDataConfig = {
  autoRefreshInterval: 30 * 60 * 1000, // 30 minutes
  maxRetryAttempts: 3,
  retryDelay: 1000, // 1 second base delay
  retryMultiplier: 2, // exponential backoff
  maxRetryDelay: 30000, // 30 seconds max delay
};

const COMPONENT_NAME = 'useWeatherData';

export function useWeatherData(
  coordinates: { lat: number; lon: number } | null,
  config: UseWeatherDataConfig = DEFAULT_CONFIG
): UseWeatherDataReturn {
  const [data, setData] = useState<WeatherData | null>(null);
  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<WeatherError | null>(null);
  const [retryCount, setRetryCount] = useState<number>(0);
  const [isRetrying, setIsRetrying] = useState<boolean>(false);

  // Use refs to track retry state and intervals
  const retryTimeoutRef = useRef<NodeJS.Timeout | null>(null);
  const refreshIntervalRef = useRef<NodeJS.Timeout | null>(null);
  const abortControllerRef = useRef<AbortController | null>(null);
  const requestStartTimeRef = useRef<number>(0);

  const calculateRetryDelay = useCallback((attempt: number): number => {
    const baseDelay = config.retryDelay || DEFAULT_CONFIG.retryDelay!;
    const multiplier = config.retryMultiplier || DEFAULT_CONFIG.retryMultiplier!;
    const maxDelay = config.maxRetryDelay || DEFAULT_CONFIG.maxRetryDelay!;

    // Exponential backoff with jitter
    const exponentialDelay = baseDelay * Math.pow(multiplier, attempt - 1);
    const jitter = Math.random() * 1000; // Add up to 1 second of jitter
    const totalDelay = exponentialDelay + jitter;

    return Math.min(totalDelay, maxDelay);
  }, [config.retryDelay, config.retryMultiplier, config.maxRetryDelay]);

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
      setRetryCount(0);
      setIsRetrying(false);
    } else {
      setIsRetrying(true);
    }

    const url = getCurrentWeather.url({
      query: {
        lat: coords.lat.toString(),
        lon: coords.lon.toString(),
      }
    });

    requestStartTimeRef.current = Date.now();

    weatherLogger.info(COMPONENT_NAME, `Starting weather data fetch${isRetry ? ' (retry)' : ''}`, {
      coordinates: coords,
      attempt: isRetry ? retryCount + 1 : 1,
      url,
    });

    try {
      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        signal: abortControllerRef.current.signal,
      });

      const duration = Date.now() - requestStartTimeRef.current;

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));

        logApiRequest(COMPONENT_NAME, url, 'GET', false, duration, { status: response.status, body: errorData });

        const weatherError = parseHttpError(response.status, errorData);
        throw weatherError;
      }

      const weatherData: WeatherData = await response.json();

      // Validate the response data
      if (!weatherData || typeof weatherData !== 'object') {
        const validationError = createWeatherError(WeatherErrorType.INVALID_RESPONSE, {
          receivedData: weatherData,
        });

        logApiRequest(COMPONENT_NAME, url, 'GET', false, duration, validationError);
        throw validationError;
      }

      // Validate required fields
      const requiredFields = ['location', 'temperature', 'condition', 'description', 'icon'];
      const missingFields = requiredFields.filter(field => !(field in weatherData));

      if (missingFields.length > 0) {
        const validationError = createWeatherError(WeatherErrorType.DATA_PARSING_ERROR, {
          missingFields,
          receivedData: weatherData,
        });

        logApiRequest(COMPONENT_NAME, url, 'GET', false, duration, validationError);
        throw validationError;
      }

      logApiRequest(COMPONENT_NAME, url, 'GET', true, duration);

      setData(weatherData);
      setError(null);
      setLoading(false);
      setRetryCount(0);
      setIsRetrying(false);

      weatherLogger.info(COMPONENT_NAME, 'Weather data fetched successfully', {
        coordinates: coords,
        location: weatherData.location,
        temperature: weatherData.temperature,
        duration,
      });

      // Set up auto-refresh interval
      if (refreshIntervalRef.current) {
        clearInterval(refreshIntervalRef.current);
      }

      if (config.autoRefreshInterval && config.autoRefreshInterval > 0) {
        weatherLogger.debug(COMPONENT_NAME, 'Setting up auto-refresh interval', {
          interval: config.autoRefreshInterval,
        });

        refreshIntervalRef.current = setInterval(() => {
          weatherLogger.info(COMPONENT_NAME, 'Auto-refreshing weather data');
          fetchWeatherData(coords, false);
        }, config.autoRefreshInterval);
      }

    } catch (err) {
      const duration = Date.now() - requestStartTimeRef.current;

      // Don't handle aborted requests as errors
      if (err instanceof Error && err.name === 'AbortError') {
        weatherLogger.debug(COMPONENT_NAME, 'Request aborted', { duration });
        return;
      }

      let weatherError: WeatherError;

      if (err instanceof Error && 'type' in err && 'userMessage' in err && 'retryable' in err) {
        // Already a WeatherError
        weatherError = err as WeatherError;
      } else if (err instanceof Error) {
        // Convert regular Error to WeatherError
        weatherError = parseNetworkError(err);
      } else {
        // Unknown error type
        weatherError = createWeatherError(WeatherErrorType.UNKNOWN_ERROR, {
          originalError: err,
        });
      }

      const currentRetryCount = isRetry ? retryCount + 1 : 1;
      const maxRetries = config.maxRetryAttempts || DEFAULT_CONFIG.maxRetryAttempts!;
      const shouldRetry = currentRetryCount < maxRetries && weatherError.retryable;

      if (shouldRetry) {
        const delay = calculateRetryDelay(currentRetryCount);

        setRetryCount(currentRetryCount);

        logRetryAttempt(COMPONENT_NAME, currentRetryCount, maxRetries, delay, weatherError.message);

        weatherLogger.warn(COMPONENT_NAME, `Retrying weather request in ${Math.round(delay)}ms`, {
          attempt: currentRetryCount,
          maxAttempts: maxRetries,
          delay,
          error: weatherError,
          coordinates: coords,
        });

        retryTimeoutRef.current = setTimeout(() => {
          fetchWeatherData(coords, true);
        }, delay);
      } else {
        // All retries exhausted or non-retryable error
        setError(weatherError);
        setLoading(false);
        setIsRetrying(false);

        weatherLogger.logWeatherError(COMPONENT_NAME, weatherError, {
          coordinates: coords,
          finalAttempt: currentRetryCount,
          maxAttempts: maxRetries,
          duration,
        });
      }
    }
  }, [config.autoRefreshInterval, config.maxRetryAttempts, config.retryDelay, config.retryMultiplier, config.maxRetryDelay, calculateRetryDelay, retryCount]);

  const refetch = useCallback(() => {
    if (coordinates) {
      // Clear any existing retry timeout
      if (retryTimeoutRef.current) {
        clearTimeout(retryTimeoutRef.current);
        retryTimeoutRef.current = null;
      }

      weatherLogger.info(COMPONENT_NAME, 'Manual refetch requested', { coordinates });
      fetchWeatherData(coordinates, false);
    } else {
      weatherLogger.warn(COMPONENT_NAME, 'Refetch requested but no coordinates available');
    }
  }, [coordinates, fetchWeatherData]);

  // Effect to fetch data when coordinates change
  useEffect(() => {
    if (coordinates) {
      weatherLogger.info(COMPONENT_NAME, 'Coordinates changed, fetching weather data', { coordinates });
      fetchWeatherData(coordinates, false);
    } else {
      // Clear data when coordinates are null
      weatherLogger.debug(COMPONENT_NAME, 'Coordinates cleared, resetting state');
      setData(null);
      setError(null);
      setLoading(false);
      setRetryCount(0);
      setIsRetrying(false);
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
      weatherLogger.debug(COMPONENT_NAME, 'Component unmounting, cleaning up');

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
    retryCount,
    isRetrying,
  };
}
