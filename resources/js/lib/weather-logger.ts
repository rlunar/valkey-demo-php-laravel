/**
 * Weather widget logging utility for debugging and monitoring
 */

import { WeatherError, WeatherErrorType } from '@/types/weather-errors';

export enum LogLevel {
  DEBUG = 'debug',
  INFO = 'info',
  WARN = 'warn',
  ERROR = 'error',
}

interface LogEntry {
  level: LogLevel;
  message: string;
  data?: Record<string, any>;
  timestamp: string;
  component: string;
}

class WeatherLogger {
  private isDevelopment: boolean;
  private logHistory: LogEntry[] = [];
  private maxHistorySize = 100;

  constructor() {
    this.isDevelopment = import.meta.env.DEV || process.env.NODE_ENV === 'development';
  }

  private createLogEntry(
    level: LogLevel,
    message: string,
    component: string,
    data?: Record<string, any>
  ): LogEntry {
    return {
      level,
      message,
      data,
      timestamp: new Date().toISOString(),
      component,
    };
  }

  private addToHistory(entry: LogEntry): void {
    this.logHistory.push(entry);

    // Keep history size manageable
    if (this.logHistory.length > this.maxHistorySize) {
      this.logHistory = this.logHistory.slice(-this.maxHistorySize);
    }
  }

  private shouldLog(level: LogLevel): boolean {
    // Always log errors and warnings
    if (level === LogLevel.ERROR || level === LogLevel.WARN) {
      return true;
    }

    // Log info and debug only in development
    return this.isDevelopment;
  }

  private formatMessage(component: string, message: string): string {
    return `[WeatherWidget:${component}] ${message}`;
  }

  debug(component: string, message: string, data?: Record<string, any>): void {
    const entry = this.createLogEntry(LogLevel.DEBUG, message, component, data);
    this.addToHistory(entry);

    if (this.shouldLog(LogLevel.DEBUG)) {
      console.debug(this.formatMessage(component, message), data || '');
    }
  }

  info(component: string, message: string, data?: Record<string, any>): void {
    const entry = this.createLogEntry(LogLevel.INFO, message, component, data);
    this.addToHistory(entry);

    if (this.shouldLog(LogLevel.INFO)) {
      console.info(this.formatMessage(component, message), data || '');
    }
  }

  warn(component: string, message: string, data?: Record<string, any>): void {
    const entry = this.createLogEntry(LogLevel.WARN, message, component, data);
    this.addToHistory(entry);

    if (this.shouldLog(LogLevel.WARN)) {
      console.warn(this.formatMessage(component, message), data || '');
    }
  }

  error(component: string, message: string, data?: Record<string, any>): void {
    const entry = this.createLogEntry(LogLevel.ERROR, message, component, data);
    this.addToHistory(entry);

    if (this.shouldLog(LogLevel.ERROR)) {
      console.error(this.formatMessage(component, message), data || '');
    }
  }

  /**
   * Log weather-specific errors with structured data
   */
  logWeatherError(component: string, weatherError: WeatherError, context?: Record<string, any>): void {
    const logData = {
      errorType: weatherError.type,
      errorMessage: weatherError.message,
      userMessage: weatherError.userMessage,
      retryable: weatherError.retryable,
      details: weatherError.details,
      timestamp: weatherError.timestamp,
      ...context,
    };

    this.error(component, `Weather error: ${weatherError.message}`, logData);
  }

  /**
   * Log API request attempts and results
   */
  logApiRequest(
    component: string,
    url: string,
    method: string,
    success: boolean,
    duration?: number,
    error?: any
  ): void {
    const logData = {
      url,
      method,
      success,
      duration,
      error: error ? { message: error.message, name: error.name } : undefined,
    };

    if (success) {
      this.info(component, `API request successful: ${method} ${url}`, logData);
    } else {
      this.error(component, `API request failed: ${method} ${url}`, logData);
    }
  }

  /**
   * Log retry attempts
   */
  logRetryAttempt(
    component: string,
    attempt: number,
    maxAttempts: number,
    delay: number,
    reason: string
  ): void {
    const logData = {
      attempt,
      maxAttempts,
      delay,
      reason,
    };

    this.warn(component, `Retry attempt ${attempt}/${maxAttempts} in ${delay}ms`, logData);
  }

  /**
   * Log geolocation events
   */
  logGeolocation(
    component: string,
    event: 'request' | 'success' | 'error' | 'timeout',
    data?: Record<string, any>
  ): void {
    const message = `Geolocation ${event}`;

    switch (event) {
      case 'request':
        this.info(component, message, data);
        break;
      case 'success':
        this.info(component, message, data);
        break;
      case 'error':
      case 'timeout':
        this.warn(component, message, data);
        break;
    }
  }

  /**
   * Log cache operations
   */
  logCache(
    component: string,
    operation: 'hit' | 'miss' | 'set' | 'clear',
    key?: string,
    data?: Record<string, any>
  ): void {
    const logData = { operation, key, ...data };
    this.debug(component, `Cache ${operation}`, logData);
  }

  /**
   * Get recent log history for debugging
   */
  getLogHistory(level?: LogLevel): LogEntry[] {
    if (level) {
      return this.logHistory.filter(entry => entry.level === level);
    }
    return [...this.logHistory];
  }

  /**
   * Clear log history
   */
  clearHistory(): void {
    this.logHistory = [];
  }

  /**
   * Export logs for debugging (development only)
   */
  exportLogs(): string | null {
    if (!this.isDevelopment) {
      return null;
    }

    return JSON.stringify(this.logHistory, null, 2);
  }
}

// Create singleton instance
export const weatherLogger = new WeatherLogger();

// Export convenience functions
export const logWeatherError = (component: string, error: WeatherError, context?: Record<string, any>) =>
  weatherLogger.logWeatherError(component, error, context);

export const logApiRequest = (component: string, url: string, method: string, success: boolean, duration?: number, error?: any) =>
  weatherLogger.logApiRequest(component, url, method, success, duration, error);

export const logRetryAttempt = (component: string, attempt: number, maxAttempts: number, delay: number, reason: string) =>
  weatherLogger.logRetryAttempt(component, attempt, maxAttempts, delay, reason);

export const logGeolocation = (component: string, event: 'request' | 'success' | 'error' | 'timeout', data?: Record<string, any>) =>
  weatherLogger.logGeolocation(component, event, data);

export const logCache = (component: string, operation: 'hit' | 'miss' | 'set' | 'clear', key?: string, data?: Record<string, any>) =>
  weatherLogger.logCache(component, operation, key, data);
