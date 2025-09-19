/**
 * Weather-related error types and constants for comprehensive error handling
 */

export enum WeatherErrorType {
  // Network and connectivity errors
  NETWORK_ERROR = 'NETWORK_ERROR',
  CONNECTION_TIMEOUT = 'CONNECTION_TIMEOUT',

  // API-specific errors
  API_KEY_INVALID = 'API_KEY_INVALID',
  RATE_LIMIT_EXCEEDED = 'RATE_LIMIT_EXCEEDED',
  SERVICE_UNAVAILABLE = 'SERVICE_UNAVAILABLE',

  // Location-related errors
  LOCATION_NOT_FOUND = 'LOCATION_NOT_FOUND',
  INVALID_COORDINATES = 'INVALID_COORDINATES',
  GEOLOCATION_DENIED = 'GEOLOCATION_DENIED',
  GEOLOCATION_UNAVAILABLE = 'GEOLOCATION_UNAVAILABLE',
  GEOLOCATION_TIMEOUT = 'GEOLOCATION_TIMEOUT',
  GEOLOCATION_UNSUPPORTED = 'GEOLOCATION_UNSUPPORTED',

  // Data and parsing errors
  INVALID_RESPONSE = 'INVALID_RESPONSE',
  DATA_PARSING_ERROR = 'DATA_PARSING_ERROR',

  // Generic errors
  UNKNOWN_ERROR = 'UNKNOWN_ERROR',
}

export interface WeatherError {
  type: WeatherErrorType;
  message: string;
  userMessage: string;
  retryable: boolean;
  details?: Record<string, any>;
  timestamp: string;
}

export const ERROR_MESSAGES: Record<WeatherErrorType, { message: string; userMessage: string; retryable: boolean }> = {
  [WeatherErrorType.NETWORK_ERROR]: {
    message: 'Network connection failed',
    userMessage: 'Unable to connect to weather service. Please check your internet connection.',
    retryable: true,
  },
  [WeatherErrorType.CONNECTION_TIMEOUT]: {
    message: 'Request timed out',
    userMessage: 'Weather service is taking too long to respond. Please try again.',
    retryable: true,
  },
  [WeatherErrorType.API_KEY_INVALID]: {
    message: 'Invalid API key',
    userMessage: 'Weather service is not properly configured. Please contact support.',
    retryable: false,
  },
  [WeatherErrorType.RATE_LIMIT_EXCEEDED]: {
    message: 'API rate limit exceeded',
    userMessage: 'Weather service is temporarily unavailable due to high demand. Please try again in a few minutes.',
    retryable: true,
  },
  [WeatherErrorType.SERVICE_UNAVAILABLE]: {
    message: 'Weather service unavailable',
    userMessage: 'Weather service is currently unavailable. Please try again later.',
    retryable: true,
  },
  [WeatherErrorType.LOCATION_NOT_FOUND]: {
    message: 'Location not found',
    userMessage: 'Unable to find weather data for this location.',
    retryable: false,
  },
  [WeatherErrorType.INVALID_COORDINATES]: {
    message: 'Invalid coordinates provided',
    userMessage: 'Invalid location coordinates. Using default location instead.',
    retryable: false,
  },
  [WeatherErrorType.GEOLOCATION_DENIED]: {
    message: 'Geolocation permission denied',
    userMessage: 'Location access denied. Showing weather for default location.',
    retryable: false,
  },
  [WeatherErrorType.GEOLOCATION_UNAVAILABLE]: {
    message: 'Geolocation unavailable',
    userMessage: 'Unable to determine your location. Showing weather for default location.',
    retryable: true,
  },
  [WeatherErrorType.GEOLOCATION_TIMEOUT]: {
    message: 'Geolocation request timed out',
    userMessage: 'Location request timed out. Showing weather for default location.',
    retryable: true,
  },
  [WeatherErrorType.GEOLOCATION_UNSUPPORTED]: {
    message: 'Geolocation not supported',
    userMessage: 'Location services are not supported by your browser. Showing weather for default location.',
    retryable: false,
  },
  [WeatherErrorType.INVALID_RESPONSE]: {
    message: 'Invalid response from weather service',
    userMessage: 'Received invalid data from weather service. Please try again.',
    retryable: true,
  },
  [WeatherErrorType.DATA_PARSING_ERROR]: {
    message: 'Failed to parse weather data',
    userMessage: 'Unable to process weather data. Please try again.',
    retryable: true,
  },
  [WeatherErrorType.UNKNOWN_ERROR]: {
    message: 'Unknown error occurred',
    userMessage: 'An unexpected error occurred. Please try again.',
    retryable: true,
  },
};

/**
 * Create a standardized weather error object
 */
export function createWeatherError(
  type: WeatherErrorType,
  details?: Record<string, any>,
  customMessage?: string
): WeatherError {
  const errorConfig = ERROR_MESSAGES[type];

  return {
    type,
    message: customMessage || errorConfig.message,
    userMessage: errorConfig.userMessage,
    retryable: errorConfig.retryable,
    details,
    timestamp: new Date().toISOString(),
  };
}

/**
 * Parse HTTP error response and return appropriate WeatherError
 */
export function parseHttpError(status: number, responseBody?: any): WeatherError {
  switch (status) {
    case 400:
      return createWeatherError(WeatherErrorType.INVALID_COORDINATES, { status, responseBody });
    case 401:
      return createWeatherError(WeatherErrorType.API_KEY_INVALID, { status, responseBody });
    case 404:
      return createWeatherError(WeatherErrorType.LOCATION_NOT_FOUND, { status, responseBody });
    case 429:
      return createWeatherError(WeatherErrorType.RATE_LIMIT_EXCEEDED, { status, responseBody });
    case 503:
      return createWeatherError(WeatherErrorType.SERVICE_UNAVAILABLE, { status, responseBody });
    default:
      if (status >= 500) {
        return createWeatherError(WeatherErrorType.SERVICE_UNAVAILABLE, { status, responseBody });
      }
      return createWeatherError(WeatherErrorType.UNKNOWN_ERROR, { status, responseBody });
  }
}

/**
 * Parse geolocation error and return appropriate WeatherError
 */
export function parseGeolocationError(error: GeolocationPositionError): WeatherError {
  switch (error.code) {
    case error.PERMISSION_DENIED:
      return createWeatherError(WeatherErrorType.GEOLOCATION_DENIED, { code: error.code, message: error.message });
    case error.POSITION_UNAVAILABLE:
      return createWeatherError(WeatherErrorType.GEOLOCATION_UNAVAILABLE, { code: error.code, message: error.message });
    case error.TIMEOUT:
      return createWeatherError(WeatherErrorType.GEOLOCATION_TIMEOUT, { code: error.code, message: error.message });
    default:
      return createWeatherError(WeatherErrorType.UNKNOWN_ERROR, { code: error.code, message: error.message });
  }
}

/**
 * Parse network/fetch error and return appropriate WeatherError
 */
export function parseNetworkError(error: Error): WeatherError {
  if (error.name === 'AbortError') {
    return createWeatherError(WeatherErrorType.UNKNOWN_ERROR, { name: error.name, message: error.message });
  }

  if (error.message.toLowerCase().includes('timeout')) {
    return createWeatherError(WeatherErrorType.CONNECTION_TIMEOUT, { name: error.name, message: error.message });
  }

  if (error.message.toLowerCase().includes('network') ||
      error.message.toLowerCase().includes('fetch')) {
    return createWeatherError(WeatherErrorType.NETWORK_ERROR, { name: error.name, message: error.message });
  }

  return createWeatherError(WeatherErrorType.UNKNOWN_ERROR, { name: error.name, message: error.message });
}
