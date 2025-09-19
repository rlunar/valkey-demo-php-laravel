import { useCallback, useEffect, useState } from 'react';
import { WeatherError, WeatherErrorType, createWeatherError, parseGeolocationError } from '@/types/weather-errors';
import { weatherLogger, logGeolocation } from '@/lib/weather-logger';

interface UseGeolocationReturn {
  location: { lat: number; lon: number } | null;
  error: WeatherError | null;
  loading: boolean;
  requestLocation: () => void;
}

interface GeolocationConfig {
  enableHighAccuracy?: boolean;
  timeout?: number;
  maximumAge?: number;
}

const DEFAULT_CONFIG: GeolocationConfig = {
  enableHighAccuracy: true,
  timeout: 10000, // 10 seconds
  maximumAge: 300000, // 5 minutes
};

const COMPONENT_NAME = 'useGeolocation';

export function useGeolocation(config: GeolocationConfig = DEFAULT_CONFIG): UseGeolocationReturn {
  const [location, setLocation] = useState<{ lat: number; lon: number } | null>(null);
  const [error, setError] = useState<WeatherError | null>(null);
  const [loading, setLoading] = useState<boolean>(false);

  const handleSuccess = useCallback((position: GeolocationPosition) => {
    const coords = {
      lat: position.coords.latitude,
      lon: position.coords.longitude,
    };

    setLocation(coords);
    setError(null);
    setLoading(false);

    logGeolocation(COMPONENT_NAME, 'success', {
      coordinates: coords,
      accuracy: position.coords.accuracy,
      timestamp: position.timestamp,
    });

    weatherLogger.info(COMPONENT_NAME, 'Location obtained successfully', {
      coordinates: coords,
      accuracy: position.coords.accuracy,
    });
  }, []);

  const handleError = useCallback((geolocationError: GeolocationPositionError) => {
    setLoading(false);

    const weatherError = parseGeolocationError(geolocationError);
    setError(weatherError);

    logGeolocation(COMPONENT_NAME, 'error', {
      code: geolocationError.code,
      message: geolocationError.message,
      errorType: weatherError.type,
    });

    weatherLogger.logWeatherError(COMPONENT_NAME, weatherError, {
      geolocationCode: geolocationError.code,
      geolocationMessage: geolocationError.message,
    });
  }, []);

  const requestLocation = useCallback(() => {
    // Check if geolocation is supported
    if (!navigator.geolocation) {
      const unsupportedError = createWeatherError(WeatherErrorType.GEOLOCATION_UNSUPPORTED);
      setError(unsupportedError);

      weatherLogger.logWeatherError(COMPONENT_NAME, unsupportedError, {
        userAgent: navigator.userAgent,
      });

      return;
    }

    setLoading(true);
    setError(null);

    logGeolocation(COMPONENT_NAME, 'request', {
      config,
      userAgent: navigator.userAgent,
    });

    weatherLogger.info(COMPONENT_NAME, 'Requesting geolocation', { config });

    // Set up timeout handling
    const timeoutId = setTimeout(() => {
      const timeoutError = createWeatherError(WeatherErrorType.GEOLOCATION_TIMEOUT);
      setError(timeoutError);
      setLoading(false);

      logGeolocation(COMPONENT_NAME, 'timeout', { timeout: config.timeout });
      weatherLogger.logWeatherError(COMPONENT_NAME, timeoutError, { timeout: config.timeout });
    }, (config.timeout || DEFAULT_CONFIG.timeout!) + 1000); // Add 1 second buffer

    const clearTimeoutAndHandle = (handler: Function) => (...args: any[]) => {
      clearTimeout(timeoutId);
      handler(...args);
    };

    navigator.geolocation.getCurrentPosition(
      clearTimeoutAndHandle(handleSuccess),
      clearTimeoutAndHandle(handleError),
      {
        enableHighAccuracy: config.enableHighAccuracy,
        timeout: config.timeout,
        maximumAge: config.maximumAge,
      }
    );
  }, [handleSuccess, handleError, config]);

  // Auto-request location on mount if geolocation is available
  useEffect(() => {
    if (navigator.geolocation) {
      requestLocation();
    } else {
      const unsupportedError = createWeatherError(WeatherErrorType.GEOLOCATION_UNSUPPORTED);
      setError(unsupportedError);

      weatherLogger.logWeatherError(COMPONENT_NAME, unsupportedError, {
        userAgent: navigator.userAgent,
      });
    }
  }, [requestLocation]);

  return {
    location,
    error,
    loading,
    requestLocation,
  };
}
