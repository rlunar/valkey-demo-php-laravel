import React from 'react';
import { useGeolocation } from '@/hooks/use-geolocation';
import { useWeatherData } from '@/hooks/use-weather-data';
import { Skeleton } from '@/components/ui/skeleton';
import { Alert, AlertDescription } from '@/components/ui/alert';

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

// Default location (can be configured via props)
const DEFAULT_LOCATION = {
  lat: 40.7128,
  lon: -74.0060,
  name: 'New York, NY'
};

export default function WeatherWidget({
  defaultLocation = DEFAULT_LOCATION,
  className = ''
}: WeatherWidgetProps) {
  const {
    location: userLocation,
    error: locationError,
    loading: locationLoading
  } = useGeolocation();

  // Use user location if available, otherwise fall back to default
  const coordinates = userLocation || {
    lat: defaultLocation.lat,
    lon: defaultLocation.lon
  };

  const {
    data: weatherData,
    loading: weatherLoading,
    error: weatherError,
    refetch
  } = useWeatherData(coordinates);

  const isLoading = locationLoading || weatherLoading;
  const hasError = weatherError || (locationError && !userLocation);

  // Loading state UI
  if (isLoading) {
    return (
      <section
        className={`bg-gray-50 p-4 sm:p-5 rounded-lg dark:bg-gray-800 ${className}`}
        aria-label="Weather information loading"
      >
        <div className="flex items-center justify-between mb-3">
          <Skeleton className="h-5 w-16" />
          <Skeleton className="h-4 w-4 rounded-full" />
        </div>
        <div className="space-y-3">
          <div className="flex items-center space-x-3">
            <Skeleton className="h-12 w-12 rounded-lg" />
            <div className="flex-1 space-y-2">
              <Skeleton className="h-6 w-20" />
              <Skeleton className="h-4 w-24" />
            </div>
          </div>
          <div className="space-y-2">
            <Skeleton className="h-4 w-full" />
            <div className="flex justify-between">
              <Skeleton className="h-3 w-16" />
              <Skeleton className="h-3 w-20" />
            </div>
          </div>
        </div>
      </section>
    );
  }

  // Error state UI
  if (hasError) {
    const errorMessage = weatherError || 'Unable to determine location';

    return (
      <section
        className={`bg-gray-50 p-4 sm:p-5 rounded-lg dark:bg-gray-800 ${className}`}
        aria-label="Weather information error"
      >
        <h2 className="text-lg sm:text-xl font-semibold text-gray-900 mb-3 dark:text-gray-100">
          Weather
        </h2>
        <Alert variant="destructive" className="border-red-200 dark:border-red-800">
          <svg
            className="h-4 w-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            aria-hidden="true"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
            />
          </svg>
          <AlertDescription>
            {errorMessage}
            {weatherError && (
              <button
                onClick={refetch}
                className="ml-2 text-sm underline hover:no-underline focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 rounded-sm dark:focus:ring-red-400 dark:focus:ring-offset-gray-800"
                aria-label="Retry loading weather data"
              >
                Try again
              </button>
            )}
          </AlertDescription>
        </Alert>
      </section>
    );
  }

  // Success state UI with weather data
  if (weatherData) {
    return (
      <section
        className={`bg-gray-50 p-4 sm:p-5 rounded-lg dark:bg-gray-800 ${className}`}
        aria-label="Current weather information"
      >
        <div className="flex items-center justify-between mb-3">
          <h2 className="text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100">
            Weather
          </h2>
          <button
            onClick={refetch}
            className="p-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-sm dark:focus:ring-blue-400 dark:focus:ring-offset-gray-800 touch-manipulation"
            aria-label="Refresh weather data"
            title="Refresh weather data"
          >
            <svg
              className="h-4 w-4"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              aria-hidden="true"
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

        <div className="space-y-3">
          {/* Main weather display */}
          <div className="flex items-center space-x-3">
            {/* Weather icon */}
            <div className="flex-shrink-0 w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
              <img
                src={`https://openweathermap.org/img/wn/${weatherData.icon}@2x.png`}
                alt={weatherData.description}
                className="w-10 h-10"
                loading="lazy"
                onError={(e) => {
                  // Fallback to generic weather icon if image fails to load
                  const target = e.target as HTMLImageElement;
                  target.style.display = 'none';
                  const parent = target.parentElement;
                  if (parent) {
                    parent.innerHTML = `
                      <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.002 4.002 0 003 15z" />
                      </svg>
                    `;
                  }
                }}
              />
            </div>

            {/* Temperature and condition */}
            <div className="flex-1 min-w-0">
              <div className="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-100">
                {Math.round(weatherData.temperature)}°C
              </div>
              <div className="text-sm sm:text-base text-gray-600 dark:text-gray-300 capitalize">
                {weatherData.description}
              </div>
            </div>
          </div>

          {/* Location */}
          <div className="text-sm text-gray-600 dark:text-gray-300 flex items-center">
            <svg
              className="w-4 h-4 mr-1 flex-shrink-0"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              aria-hidden="true"
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
            <span className="truncate">
              {weatherData.location || (userLocation ? 'Current location' : defaultLocation.name)}
            </span>
          </div>

          {/* Additional weather details */}
          <div className="grid grid-cols-2 gap-3 text-xs sm:text-sm text-gray-600 dark:text-gray-300">
            <div className="flex items-center">
              <svg
                className="w-3 h-3 sm:w-4 sm:h-4 mr-1 flex-shrink-0"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                aria-hidden="true"
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
            <div className="flex items-center">
              <svg
                className="w-3 h-3 sm:w-4 sm:h-4 mr-1 flex-shrink-0"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                aria-hidden="true"
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
          <div className="text-xs text-gray-500 dark:text-gray-400 pt-2 border-t border-gray-200 dark:border-gray-700">
            Updated: {new Date(weatherData.lastUpdated).toLocaleTimeString([], {
              hour: '2-digit',
              minute: '2-digit'
            })}
          </div>
        </div>
      </section>
    );
  }

  // Fallback state (should not normally be reached)
  return (
    <section
      className={`bg-gray-50 p-4 sm:p-5 rounded-lg dark:bg-gray-800 ${className}`}
      aria-label="Weather information unavailable"
    >
      <h2 className="text-lg sm:text-xl font-semibold text-gray-900 mb-3 dark:text-gray-100">
        Weather
      </h2>
      <p className="text-sm text-gray-600 dark:text-gray-300">
        Weather information is currently unavailable.
      </p>
    </section>
  );
}
