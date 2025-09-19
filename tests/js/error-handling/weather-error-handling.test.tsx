import { describe, it, expect } from 'vitest';
import {
  WeatherErrorType,
  createWeatherError,
  parseHttpError,
  parseGeolocationError,
  parseNetworkError,
  ERROR_MESSAGES
} from '../../../resources/js/types/weather-errors';

describe('Weather Error Handling', () => {
  describe('createWeatherError', () => {
    it('should create a weather error with correct properties', () => {
      const error = createWeatherError(WeatherErrorType.NETWORK_ERROR, { test: 'data' });

      expect(error.type).toBe(WeatherErrorType.NETWORK_ERROR);
      expect(error.message).toBe('Network connection failed');
      expect(error.userMessage).toBe('Unable to connect to weather service. Please check your internet connection.');
      expect(error.retryable).toBe(true);
      expect(error.details).toEqual({ test: 'data' });
      expect(error.timestamp).toBeTruthy();
    });

    it('should use custom message when provided', () => {
      const customMessage = 'Custom error message';
      const error = createWeatherError(WeatherErrorType.NETWORK_ERROR, {}, customMessage);

      expect(error.message).toBe(customMessage);
      expect(error.userMessage).toBe('Unable to connect to weather service. Please check your internet connection.');
    });
  });

  describe('parseHttpError', () => {
    it('should parse 400 status as invalid coordinates', () => {
      const error = parseHttpError(400, { error: 'Bad request' });

      expect(error.type).toBe(WeatherErrorType.INVALID_COORDINATES);
      expect(error.retryable).toBe(false);
    });

    it('should parse 401 status as API key invalid', () => {
      const error = parseHttpError(401, { error: 'Unauthorized' });

      expect(error.type).toBe(WeatherErrorType.API_KEY_INVALID);
      expect(error.retryable).toBe(false);
    });

    it('should parse 404 status as location not found', () => {
      const error = parseHttpError(404, { error: 'Not found' });

      expect(error.type).toBe(WeatherErrorType.LOCATION_NOT_FOUND);
      expect(error.retryable).toBe(false);
    });

    it('should parse 429 status as rate limit exceeded', () => {
      const error = parseHttpError(429, { error: 'Too many requests' });

      expect(error.type).toBe(WeatherErrorType.RATE_LIMIT_EXCEEDED);
      expect(error.retryable).toBe(true);
    });

    it('should parse 503 status as service unavailable', () => {
      const error = parseHttpError(503, { error: 'Service unavailable' });

      expect(error.type).toBe(WeatherErrorType.SERVICE_UNAVAILABLE);
      expect(error.retryable).toBe(true);
    });

    it('should parse 500+ status as service unavailable', () => {
      const error = parseHttpError(500, { error: 'Internal server error' });

      expect(error.type).toBe(WeatherErrorType.SERVICE_UNAVAILABLE);
      expect(error.retryable).toBe(true);
    });

    it('should parse unknown status as unknown error', () => {
      const error = parseHttpError(418, { error: "I'm a teapot" });

      expect(error.type).toBe(WeatherErrorType.UNKNOWN_ERROR);
      expect(error.retryable).toBe(true);
    });
  });

  describe('parseGeolocationError', () => {
    it('should parse PERMISSION_DENIED error', () => {
      const geolocationError = {
        code: 1,
        message: 'User denied geolocation',
        PERMISSION_DENIED: 1,
        POSITION_UNAVAILABLE: 2,
        TIMEOUT: 3
      } as GeolocationPositionError;

      const error = parseGeolocationError(geolocationError);

      expect(error.type).toBe(WeatherErrorType.GEOLOCATION_DENIED);
      expect(error.retryable).toBe(false);
    });

    it('should parse POSITION_UNAVAILABLE error', () => {
      const geolocationError = {
        code: 2,
        message: 'Position unavailable',
        PERMISSION_DENIED: 1,
        POSITION_UNAVAILABLE: 2,
        TIMEOUT: 3
      } as GeolocationPositionError;

      const error = parseGeolocationError(geolocationError);

      expect(error.type).toBe(WeatherErrorType.GEOLOCATION_UNAVAILABLE);
      expect(error.retryable).toBe(true);
    });

    it('should parse TIMEOUT error', () => {
      const geolocationError = {
        code: 3,
        message: 'Timeout',
        PERMISSION_DENIED: 1,
        POSITION_UNAVAILABLE: 2,
        TIMEOUT: 3
      } as GeolocationPositionError;

      const error = parseGeolocationError(geolocationError);

      expect(error.type).toBe(WeatherErrorType.GEOLOCATION_TIMEOUT);
      expect(error.retryable).toBe(true);
    });

    it('should parse unknown geolocation error', () => {
      const geolocationError = {
        code: 999,
        message: 'Unknown error',
        PERMISSION_DENIED: 1,
        POSITION_UNAVAILABLE: 2,
        TIMEOUT: 3
      } as GeolocationPositionError;

      const error = parseGeolocationError(geolocationError);

      expect(error.type).toBe(WeatherErrorType.UNKNOWN_ERROR);
      expect(error.retryable).toBe(true);
    });
  });

  describe('parseNetworkError', () => {
    it('should parse AbortError', () => {
      const networkError = new Error('Request aborted');
      networkError.name = 'AbortError';

      const error = parseNetworkError(networkError);

      expect(error.type).toBe(WeatherErrorType.UNKNOWN_ERROR);
    });

    it('should parse timeout error', () => {
      const networkError = new Error('Request timeout');

      const error = parseNetworkError(networkError);

      expect(error.type).toBe(WeatherErrorType.CONNECTION_TIMEOUT);
      expect(error.retryable).toBe(true);
    });

    it('should parse network error', () => {
      const networkError = new Error('Network connection failed');

      const error = parseNetworkError(networkError);

      expect(error.type).toBe(WeatherErrorType.NETWORK_ERROR);
      expect(error.retryable).toBe(true);
    });

    it('should parse fetch error', () => {
      const networkError = new Error('Fetch failed');

      const error = parseNetworkError(networkError);

      expect(error.type).toBe(WeatherErrorType.NETWORK_ERROR);
      expect(error.retryable).toBe(true);
    });

    it('should parse unknown error', () => {
      const networkError = new Error('Something went wrong');

      const error = parseNetworkError(networkError);

      expect(error.type).toBe(WeatherErrorType.UNKNOWN_ERROR);
      expect(error.retryable).toBe(true);
    });
  });

  describe('ERROR_MESSAGES', () => {
    it('should have user-friendly messages for all error types', () => {
      Object.values(WeatherErrorType).forEach(errorType => {
        const errorConfig = ERROR_MESSAGES[errorType];

        expect(errorConfig).toBeDefined();
        expect(errorConfig.message).toBeTruthy();
        expect(errorConfig.userMessage).toBeTruthy();
        expect(typeof errorConfig.retryable).toBe('boolean');
      });
    });

    it('should have appropriate retry settings', () => {
      // Non-retryable errors
      const nonRetryableErrors = [
        WeatherErrorType.API_KEY_INVALID,
        WeatherErrorType.LOCATION_NOT_FOUND,
        WeatherErrorType.INVALID_COORDINATES,
        WeatherErrorType.GEOLOCATION_DENIED,
        WeatherErrorType.GEOLOCATION_UNSUPPORTED
      ];

      nonRetryableErrors.forEach(errorType => {
        expect(ERROR_MESSAGES[errorType].retryable).toBe(false);
      });

      // Retryable errors
      const retryableErrors = [
        WeatherErrorType.NETWORK_ERROR,
        WeatherErrorType.CONNECTION_TIMEOUT,
        WeatherErrorType.RATE_LIMIT_EXCEEDED,
        WeatherErrorType.SERVICE_UNAVAILABLE,
        WeatherErrorType.GEOLOCATION_UNAVAILABLE,
        WeatherErrorType.GEOLOCATION_TIMEOUT,
        WeatherErrorType.INVALID_RESPONSE,
        WeatherErrorType.DATA_PARSING_ERROR,
        WeatherErrorType.UNKNOWN_ERROR
      ];

      retryableErrors.forEach(errorType => {
        expect(ERROR_MESSAGES[errorType].retryable).toBe(true);
      });
    });
  });
});
