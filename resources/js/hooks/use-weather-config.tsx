import { useState, useEffect } from 'react';
import { weatherLogger } from '@/lib/weather-logger';

interface WeatherConfig {
  default_location: {
    lat: number;
    lon: number;
    name: string;
  };
  widget: {
    auto_refresh_interval: number;
    show_detailed_info: boolean;
    temperature_unit: 'celsius' | 'fahrenheit';
  };
}

interface UseWeatherConfigReturn {
  config: WeatherConfig | null;
  loading: boolean;
  error: string | null;
}

const COMPONENT_NAME = 'useWeatherConfig';

export function useWeatherConfig(): UseWeatherConfigReturn {
  const [config, setConfig] = useState<WeatherConfig | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let isMounted = true;

    const fetchConfig = async () => {
      try {
        weatherLogger.debug(COMPONENT_NAME, 'Fetching weather configuration');

        const response = await fetch('/api/weather/config');

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const configData = await response.json();

        if (isMounted) {
          setConfig(configData);
          setError(null);
          weatherLogger.info(COMPONENT_NAME, 'Weather configuration loaded successfully', {
            default_location: configData.default_location?.name,
            auto_refresh_interval: configData.widget?.auto_refresh_interval,
          });
        }
      } catch (err) {
        if (isMounted) {
          const errorMessage = err instanceof Error ? err.message : 'Failed to load weather configuration';
          setError(errorMessage);
          weatherLogger.error(COMPONENT_NAME, 'Failed to fetch weather configuration', {
            error: errorMessage,
          });
        }
      } finally {
        if (isMounted) {
          setLoading(false);
        }
      }
    };

    fetchConfig();

    return () => {
      isMounted = false;
    };
  }, []);

  return { config, loading, error };
}
