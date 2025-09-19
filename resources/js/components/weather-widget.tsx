import React, { useMemo, useCallback } from 'react';
import { useGeolocation } from '@/hooks/use-geolocation';
import { useDebouncedWeatherData } from '@/hooks/use-debounced-weather-data';
import { useWeatherConfig } from '@/hooks/use-weather-config';
import { Skeleton } from '@/components/ui/skeleton';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { LazyWeatherIcon } from '@/components/lazy-weather-icon';
import { WeatherError, WeatherErrorType } from '@/types/weather-errors';
import { weatherLogger } from '@/lib/weather-logger';
import { useWeatherPerformance } from '@/lib/weather-performance';

interface WeatherWidgetProps {
  defaultLocation?: {
    lat: number;
    lon: number;
    name: string;
  };
  className?: string;
}

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

// Default fallback location (used if config fetch fails)
const FALLBACK_LOCATION = {
  lat: 40.7128,
  lon: -74.0060,
  name: 'New York, NY'
};

const COMPONENT_NAME = 'WeatherWidget';

const WeatherWidget = React.memo<WeatherWidgetProps>(({
  defaultLocation,
  className = ''
}) => {
  // Performance monitoring
  const { startRender, endRender } = useWeatherPerformance('WeatherWidget');

  const {
    location: userLocation,
    error: locationError,
    loading: locationLoading
  } = useGeolocation();

  const {
    config: weatherConfig,
    loading: configLoading,
    error: configError
  } = useWeatherConfig();

  // Memoize the effective default location to prevent unnecessary recalculations
  const effectiveDefaultLocation = useMemo(() => {
    return defaultLocation ||
      weatherConfig?.default_location ||
      FALLBACK_LOCATION;
  }, [defaultLocation, weatherConfig?.default_location]);

  // Memoize coordinates to prevent unnecessary API calls
  const coordinates = useMemo(() => {
    return userLocation || {
      lat: effectiveDefaultLocation.lat,
      lon: effectiveDefaultLocation.lon
    };
  }, [userLocation, effectiveDefaultLocation.lat, effectiveDefaultLocation.lon]);

  const {
    data: weatherData,
    loading: weatherLoading,
    error: weatherError,
    refetch,
    retryCount,
    isRetrying
  } = useDebouncedWeatherData(coordinates);

  // Memoize loading state calculation
  const isLoading = useMemo(() => {
    return locationLoading || weatherLoading || configLoading;
  }, [locationLoading, weatherLoading, configLoading]);

  // Memoize error state calculations
  const errorStates = useMemo(() => {
    // Only show location errors if they prevent us from getting any coordinates
    const shouldShowLocationError = locationError &&
      locationError.type !== WeatherErrorType.GEOLOCATION_DENIED &&
      locationError.type !== WeatherErrorType.GEOLOCATION_UNAVAILABLE &&
      locationError.type !== WeatherErrorType.GEOLOCATION_TIMEOUT &&
      !userLocation;

    const hasError = weatherError || shouldShowLocationError;
    const displayError = weatherError || locationError;

    return { shouldShowLocationError, hasError, displayError };
  }, [locationError, weatherError, userLocation]);

  // Memoized helper function to get error icon based on error type
  const getErrorIcon = useCallback((error: WeatherError) => {
    switch (error.type) {
      case WeatherErrorType.NETWORK_ERROR:
      case WeatherErrorType.CONNECTION_TIMEOUT:
        return (
          <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
          </svg>
        );
      case WeatherErrorType.RATE_LIMIT_EXCEEDED:
      case WeatherErrorType.SERVICE_UNAVAILABLE:
        return (
          <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        );
      case WeatherErrorType.LOCATION_NOT_FOUND:
      case WeatherErrorType.GEOLOCATION_DENIED:
        return (
          <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        );
      default:
        return (
          <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
        );
    }
  }, []);

  // Memoized helper function to determine if retry should be shown
  const shouldShowRetry = useCallback((error: WeatherError) => {
    return error.retryable && !isRetrying;
  }, [isRetrying]);

  // Memoized helper function to get retry button text
  const getRetryButtonText = useCallback((error: WeatherError) => {
    if (isRetrying) {
      return retryCount > 0 ? `Retrying... (${retryCount}/3)` : 'Retrying...';
    }

    switch (error.type) {
      case WeatherErrorType.NETWORK_ERROR:
      case WeatherErrorType.CONNECTION_TIMEOUT:
        return 'Check connection';
      case WeatherErrorType.RATE_LIMIT_EXCEEDED:
        return 'Try again later';
      default:
        return 'Try again';
    }
  }, [isRetrying, retryCount]);



  // Performance and debug logging
  React.useEffect(() => {
    startRender();

    weatherLogger.debug(COMPONENT_NAME, 'Component rendered', {
      hasUserLocation: !!userLocation,
      hasWeatherData: !!weatherData,
      hasWeatherConfig: !!weatherConfig,
      hasDefaultLocationOverride: !!defaultLocation,
      effectiveDefaultLocation: effectiveDefaultLocation.name,
      isLoading,
      hasError: errorStates.hasError,
      errorType: errorStates.displayError?.type,
      retryCount,
      isRetrying,
    });

    // Measure render performance
    const renderTime = endRender();

    return () => {
      // Cleanup if needed
    };
  }, [userLocation, weatherData, weatherConfig, defaultLocation, effectiveDefaultLocation.name, isLoading, errorStates.hasError, errorStates.displayError?.type, retryCount, isRetrying, startRender, endRender]);

  // Loading state UI
  if (isLoading) {
    const loadingMessage = configLoading ? 'Loading configuration...' :
                          locationLoading ? 'Getting your location...' :
                          (isRetrying ? 'Retrying weather data...' : 'Loading weather data...');

    return (
      <section
        className={`bg-gray-50 p-4 sm:p-5 rounded-lg dark:bg-gray-800 ${className}`}
        aria-label="Weather information loading"
        aria-live="polite"
        aria-busy="true"
      >
        <div className="flex items-center justify-between mb-3">
          <h2
            className="text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100"
            id="weather-widget-title"
          >
            Weather
          </h2>
          {isRetrying && (
            <div
              className="flex items-center space-x-1 text-xs text-gray-600 dark:text-gray-400"
              aria-label={`Retry attempt ${retryCount} of 3`}
            >
              <svg
                className="animate-spin h-3 w-3"
                fill="none"
                viewBox="0 0 24 24"
                aria-hidden="true"
                role="img"
                aria-label="Loading spinner"
              >
                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{retryCount > 0 ? `Retry ${retryCount}/3` : 'Loading'}</span>
            </div>
          )}
        </div>

        {/* Screen reader announcement for loading state */}
        <div className="sr-only" aria-live="assertive">
          {loadingMessage}
        </div>

        <div className="space-y-3" role="status" aria-labelledby="weather-widget-title">
          <div className="flex items-center space-x-3">
            <div role="img" aria-label="Weather icon placeholder">
              <Skeleton className="h-12 w-12 rounded-lg" />
            </div>
            <div className="flex-1 space-y-2">
              <div role="text" aria-label="Temperature placeholder">
                <Skeleton className="h-6 w-20" />
              </div>
              <div role="text" aria-label="Weather condition placeholder">
                <Skeleton className="h-4 w-24" />
              </div>
            </div>
          </div>
          <div className="space-y-2">
            <div role="text" aria-label="Location placeholder">
              <Skeleton className="h-4 w-full" />
            </div>
            <div className="flex justify-between">
              <div role="text" aria-label="Humidity placeholder">
                <Skeleton className="h-3 w-16" />
              </div>
              <div role="text" aria-label="Wind speed placeholder">
                <Skeleton className="h-3 w-20" />
              </div>
            </div>
          </div>
        </div>

        {/* Show loading message based on state */}
        <div className="mt-3 text-xs text-gray-500 dark:text-gray-400" aria-live="polite">
          {loadingMessage}
        </div>
      </section>
    );
  }

  // Error state UI
  if (errorStates.hasError && errorStates.displayError) {
    const displayError = errorStates.displayError; // Type assertion for non-null
    const showRetry = shouldShowRetry(displayError);
    const retryButtonText = getRetryButtonText(displayError);

    return (
      <section
        className={`bg-gray-50 p-4 sm:p-5 rounded-lg dark:bg-gray-800 ${className}`}
        aria-label="Weather information error"
        aria-live="assertive"
        role="alert"
      >
        <h2
          className="text-lg sm:text-xl font-semibold text-gray-900 mb-3 dark:text-gray-100"
          id="weather-widget-error-title"
        >
          Weather
        </h2>

        {/* Screen reader announcement for error */}
        <div className="sr-only" aria-live="assertive">
          Weather information error: {displayError.userMessage}
        </div>

        <Alert
          variant="destructive"
          className="border-red-200 dark:border-red-800"
          role="alert"
          aria-labelledby="weather-widget-error-title"
        >
          {getErrorIcon(displayError)}
          <AlertDescription>
            <div className="space-y-2">
              <p id="error-message">{displayError.userMessage}</p>

              {/* Show additional context for certain errors */}
              {displayError.type === WeatherErrorType.GEOLOCATION_DENIED && (
                <p className="text-xs text-gray-600 dark:text-gray-400" role="note">
                  You can enable location access in your browser settings to see local weather.
                </p>
              )}

              {displayError.type === WeatherErrorType.RATE_LIMIT_EXCEEDED && (
                <p className="text-xs text-gray-600 dark:text-gray-400" role="note">
                  This usually resolves within a few minutes.
                </p>
              )}

              {/* Retry button or status */}
              <div className="flex items-center space-x-2">
                {showRetry && (
                  <button
                    onClick={() => {
                      weatherLogger.info(COMPONENT_NAME, 'User initiated retry', { errorType: displayError.type });
                      refetch();
                    }}
                    disabled={isRetrying}
                    className="text-sm underline hover:no-underline focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 rounded-sm dark:focus:ring-red-400 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                    aria-label={`Retry loading weather data. Current error: ${displayError.userMessage}`}
                    aria-describedby="error-message"
                  >
                    {retryButtonText}
                  </button>
                )}

                {isRetrying && (
                  <div
                    className="flex items-center space-x-1 text-xs text-gray-600 dark:text-gray-400"
                    aria-live="polite"
                    aria-label="Retrying weather data request"
                  >
                    <svg
                      className="animate-spin h-3 w-3"
                      fill="none"
                      viewBox="0 0 24 24"
                      aria-hidden="true"
                      role="img"
                      aria-label="Loading spinner"
                    >
                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Retrying...</span>
                  </div>
                )}
              </div>

              {/* Show fallback info for location errors */}
              {(displayError.type === WeatherErrorType.GEOLOCATION_DENIED ||
                displayError.type === WeatherErrorType.GEOLOCATION_UNAVAILABLE ||
                displayError.type === WeatherErrorType.GEOLOCATION_TIMEOUT) && (
                <p className="text-xs text-gray-600 dark:text-gray-400" role="note">
                  Showing weather for {effectiveDefaultLocation.name}
                </p>
              )}
            </div>
          </AlertDescription>
        </Alert>
      </section>
    );
  }

  // Memoize weather display data to prevent unnecessary recalculations
  const weatherDisplayData = useMemo(() => {
    if (!weatherData) return null;

    const locationText = weatherData.location || (userLocation ? 'Current location' : effectiveDefaultLocation.name);
    const temperatureText = `${Math.round(weatherData.temperature)} degrees Celsius`;
    const lastUpdatedText = new Date(weatherData.lastUpdated).toLocaleTimeString([], {
      hour: '2-digit',
      minute: '2-digit'
    });

    return { locationText, temperatureText, lastUpdatedText };
  }, [weatherData, userLocation, effectiveDefaultLocation.name]);

  // Success state UI with weather data
  if (weatherData && weatherDisplayData) {

    return (
      <section
        className={`bg-gray-50 p-4 sm:p-5 rounded-lg dark:bg-gray-800 ${className}`}
        aria-label={`Current weather information for ${weatherDisplayData.locationText}`}
        aria-live="polite"
      >
        {/* Screen reader summary */}
        <div className="sr-only" aria-live="polite">
          <span>
            Current weather: {weatherDisplayData.temperatureText}, {weatherData.description} in {weatherDisplayData.locationText}.
            Humidity {weatherData.humidity} percent, wind speed {Math.round(weatherData.windSpeed * 3.6)} kilometers per hour.
            Last updated at {weatherDisplayData.lastUpdatedText}.
          </span>
        </div>

        <div className="flex items-center justify-between mb-3">
          <h2
            className="text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100"
            id="weather-widget-title"
          >
            Weather
          </h2>
          <button
            onClick={refetch}
            className="p-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-sm dark:focus:ring-blue-400 dark:focus:ring-offset-gray-800 touch-manipulation"
            aria-label={`Refresh weather data for ${weatherDisplayData.locationText}. Last updated at ${weatherDisplayData.lastUpdatedText}`}
            title="Refresh weather data"
          >
            <svg
              className="h-4 w-4"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              aria-hidden="true"
              role="img"
              aria-label="Refresh icon"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
              />
            </svg>
          </button>
        </div>

        <div className="space-y-3" role="group" aria-labelledby="weather-widget-title">
          {/* Main weather display */}
          <div className="flex items-center space-x-3" role="group" aria-label="Current temperature and conditions">
            {/* Weather icon with lazy loading */}
            <div className="flex-shrink-0 w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
              <LazyWeatherIcon
                icon={weatherData.icon}
                description={weatherData.description}
                className="w-10 h-10"
                fallbackClassName="w-8 h-8 text-blue-600 dark:text-blue-400"
              />
            </div>

            {/* Temperature and condition */}
            <div className="flex-1 min-w-0">
              <div
                className="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-100"
                aria-label={weatherDisplayData.temperatureText}
                role="text"
              >
                {Math.round(weatherData.temperature)}°C
              </div>
              <div
                className="text-sm sm:text-base text-gray-600 dark:text-gray-300 capitalize"
                aria-label={`Weather condition: ${weatherData.description}`}
                role="text"
              >
                {weatherData.description}
              </div>
            </div>
          </div>

          {/* Location */}
          <div
            className="text-sm text-gray-600 dark:text-gray-300 flex items-center"
            role="group"
            aria-label={`Location: ${weatherDisplayData.locationText}`}
          >
            <svg
              className="w-4 h-4 mr-1 flex-shrink-0"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              aria-hidden="true"
              role="img"
              aria-label="Location icon"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
              />
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
              />
            </svg>
            <span className="truncate" aria-label={`Weather location: ${weatherDisplayData.locationText}`}>
              {weatherDisplayData.locationText}
            </span>
          </div>

          {/* Additional weather details */}
          <div
            className="grid grid-cols-2 gap-3 text-xs sm:text-sm text-gray-600 dark:text-gray-300"
            role="group"
            aria-label="Additional weather details"
          >
            <div
              className="flex items-center"
              role="group"
              aria-label={`Humidity: ${weatherData.humidity} percent`}
            >
              <svg
                className="w-3 h-3 sm:w-4 sm:h-4 mr-1 flex-shrink-0"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                aria-hidden="true"
                role="img"
                aria-label="Humidity icon"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.002 4.002 0 003 15z"
                />
              </svg>
              <span>Humidity: {weatherData.humidity}%</span>
            </div>
            <div
              className="flex items-center"
              role="group"
              aria-label={`Wind speed: ${Math.round(weatherData.windSpeed * 3.6)} kilometers per hour`}
            >
              <svg
                className="w-3 h-3 sm:w-4 sm:h-4 mr-1 flex-shrink-0"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                aria-hidden="true"
                role="img"
                aria-label="Wind icon"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 0h10m-10 0a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2"
                />
              </svg>
              <span>Wind: {Math.round(weatherData.windSpeed * 3.6)} km/h</span>
            </div>
          </div>

          {/* Last updated timestamp */}
          <div
            className="text-xs text-gray-500 dark:text-gray-400 pt-2 border-t border-gray-200 dark:border-gray-700"
            role="status"
            aria-label={`Weather data last updated at ${weatherDisplayData.lastUpdatedText}`}
          >
            Updated: {weatherDisplayData.lastUpdatedText}
          </div>
        </div>
      </section>
    );
  }

  // Memoize fallback location text
  const fallbackLocationText = useMemo(() => {
    return userLocation ? 'Current location' : effectiveDefaultLocation.name;
  }, [userLocation, effectiveDefaultLocation.name]);

  return (
    <section
      className={`bg-gray-50 p-4 sm:p-5 rounded-lg dark:bg-gray-800 ${className}`}
      aria-label="Weather information unavailable"
      role="alert"
      aria-live="polite"
    >
      {/* Screen reader announcement for fallback state */}
      <div className="sr-only" aria-live="assertive">
        Weather information is currently unavailable. Please check your connection and try again.
      </div>

      <div className="flex items-center justify-between mb-3">
        <h2
          className="text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100"
          id="weather-widget-fallback-title"
        >
          Weather
        </h2>
        <button
          onClick={() => {
            weatherLogger.info(COMPONENT_NAME, 'User attempted manual refresh from fallback state');
            refetch();
          }}
          className="p-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-sm dark:focus:ring-blue-400 dark:focus:ring-offset-gray-800 touch-manipulation"
          aria-label={`Try to refresh weather data for ${fallbackLocationText}`}
          title="Try to refresh weather data"
        >
          <svg
            className="h-4 w-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            aria-hidden="true"
            role="img"
            aria-label="Refresh icon"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
            />
          </svg>
        </button>
      </div>

      <div className="space-y-3" role="group" aria-labelledby="weather-widget-fallback-title">
        {/* Placeholder weather display */}
        <div
          className="flex items-center space-x-3"
          role="status"
          aria-label="Weather information unavailable"
        >
          <div
            className="flex-shrink-0 w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center"
            role="img"
            aria-label="Weather unavailable icon"
          >
            <svg
              className="w-8 h-8 text-gray-400 dark:text-gray-500"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              aria-hidden="true"
              role="img"
              aria-label="Generic weather icon"
            >
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.002 4.002 0 003 15z" />
            </svg>
          </div>
          <div className="flex-1 min-w-0">
            <div className="text-sm text-gray-600 dark:text-gray-300" role="status">
              Weather information unavailable
            </div>
            <div className="text-xs text-gray-500 dark:text-gray-400" role="note">
              Please check your connection and try again
            </div>
          </div>
        </div>

        {/* Location fallback */}
        <div
          className="text-sm text-gray-600 dark:text-gray-300 flex items-center"
          role="group"
          aria-label={`Location: ${fallbackLocationText}`}
        >
          <svg
            className="w-4 h-4 mr-1 flex-shrink-0"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            aria-hidden="true"
            role="img"
            aria-label="Location icon"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
            />
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
            />
          </svg>
          <span className="truncate" aria-label={`Weather location: ${fallbackLocationText}`}>
            {fallbackLocationText}
          </span>
        </div>
      </div>
    </section>
  );
});

WeatherWidget.displayName = 'WeatherWidget';

export default WeatherWidget;
