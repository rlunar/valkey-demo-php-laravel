import { useCallback, useEffect, useState } from 'react';

interface UseGeolocationReturn {
  location: { lat: number; lon: number } | null;
  error: string | null;
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

export function useGeolocation(config: GeolocationConfig = DEFAULT_CONFIG): UseGeolocationReturn {
  const [location, setLocation] = useState<{ lat: number; lon: number } | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState<boolean>(false);

  const handleSuccess = useCallback((position: GeolocationPosition) => {
    setLocation({
      lat: position.coords.latitude,
      lon: position.coords.longitude,
    });
    setError(null);
    setLoading(false);
  }, []);

  const handleError = useCallback((error: GeolocationPositionError) => {
    setLoading(false);

    switch (error.code) {
      case error.PERMISSION_DENIED:
        setError('Location access denied by user');
        break;
      case error.POSITION_UNAVAILABLE:
        setError('Location information unavailable');
        break;
      case error.TIMEOUT:
        setError('Location request timed out');
        break;
      default:
        setError('An unknown error occurred while retrieving location');
        break;
    }
  }, []);

  const requestLocation = useCallback(() => {
    // Check if geolocation is supported
    if (!navigator.geolocation) {
      setError('Geolocation is not supported by this browser');
      return;
    }

    setLoading(true);
    setError(null);

    navigator.geolocation.getCurrentPosition(
      handleSuccess,
      handleError,
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
      setError('Geolocation is not supported by this browser');
    }
  }, [requestLocation]);

  return {
    location,
    error,
    loading,
    requestLocation,
  };
}
