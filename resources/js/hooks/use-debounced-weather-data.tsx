import { useCallback, useEffect, useRef, useState } from 'react';
import { useWeatherData } from './use-weather-data';
import { WeatherError } from '@/types/weather-errors';
import { weatherLogger } from '@/lib/weather-logger';

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

interface UseDebouncedWeatherDataReturn {
  data: WeatherData | null;
  loading: boolean;
  error: WeatherError | null;
  refetch: () => void;
  retryCount: number;
  isRetrying: boolean;
}

interface DebouncedWeatherConfig {
  debounceDelay?: number; // milliseconds to debounce coordinate changes
  minDistanceThreshold?: number; // minimum distance in meters to trigger new request
}

const DEFAULT_CONFIG: DebouncedWeatherConfig = {
  debounceDelay: 1000, // 1 second
  minDistanceThreshold: 1000, // 1km
};

const COMPONENT_NAME = 'useDebouncedWeatherData';

// Calculate distance between two coordinates using Haversine formula
function calculateDistance(
  lat1: number,
  lon1: number,
  lat2: number,
  lon2: number
): number {
  const R = 6371000; // Earth's radius in meters
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLon = (lon2 - lon1) * Math.PI / 180;
  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
    Math.sin(dLon / 2) * Math.sin(dLon / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return R * c;
}

export function useDebouncedWeatherData(
  coordinates: { lat: number; lon: number } | null,
  config: DebouncedWeatherConfig = DEFAULT_CONFIG
): UseDebouncedWeatherDataReturn {
  const [debouncedCoordinates, setDebouncedCoordinates] = useState<{ lat: number; lon: number } | null>(null);
  const debounceTimeoutRef = useRef<NodeJS.Timeout | null>(null);
  const lastCoordinatesRef = useRef<{ lat: number; lon: number } | null>(null);

  // Debounce coordinate changes
  useEffect(() => {
    if (debounceTimeoutRef.current) {
      clearTimeout(debounceTimeoutRef.current);
    }

    if (!coordinates) {
      setDebouncedCoordinates(null);
      lastCoordinatesRef.current = null;
      return;
    }

    // Check if coordinates have changed significantly
    const shouldUpdate = !lastCoordinatesRef.current ||
      calculateDistance(
        lastCoordinatesRef.current.lat,
        lastCoordinatesRef.current.lon,
        coordinates.lat,
        coordinates.lon
      ) >= (config.minDistanceThreshold || DEFAULT_CONFIG.minDistanceThreshold!);

    if (!shouldUpdate) {
      weatherLogger.debug(COMPONENT_NAME, 'Coordinates change below threshold, skipping update', {
        oldCoordinates: lastCoordinatesRef.current,
        newCoordinates: coordinates,
        threshold: config.minDistanceThreshold,
      });
      return;
    }

    weatherLogger.debug(COMPONENT_NAME, 'Debouncing coordinate change', {
      coordinates,
      delay: config.debounceDelay,
    });

    debounceTimeoutRef.current = setTimeout(() => {
      weatherLogger.info(COMPONENT_NAME, 'Applying debounced coordinates', { coordinates });
      setDebouncedCoordinates(coordinates);
      lastCoordinatesRef.current = coordinates;
    }, config.debounceDelay || DEFAULT_CONFIG.debounceDelay!);

    return () => {
      if (debounceTimeoutRef.current) {
        clearTimeout(debounceTimeoutRef.current);
      }
    };
  }, [coordinates, config.debounceDelay, config.minDistanceThreshold]);

  // Use the debounced coordinates with the original hook
  const weatherDataResult = useWeatherData(debouncedCoordinates);

  // Enhanced refetch that respects debouncing
  const debouncedRefetch = useCallback(() => {
    if (coordinates) {
      // Force immediate update for manual refetch
      weatherLogger.info(COMPONENT_NAME, 'Manual refetch requested, bypassing debounce');
      setDebouncedCoordinates(coordinates);
      lastCoordinatesRef.current = coordinates;

      // Call the original refetch after a short delay to ensure state is updated
      setTimeout(() => {
        weatherDataResult.refetch();
      }, 100);
    }
  }, [coordinates, weatherDataResult.refetch]);

  // Cleanup on unmount
  useEffect(() => {
    return () => {
      if (debounceTimeoutRef.current) {
        clearTimeout(debounceTimeoutRef.current);
      }
    };
  }, []);

  return {
    ...weatherDataResult,
    refetch: debouncedRefetch,
  };
}
