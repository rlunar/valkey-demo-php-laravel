import { weatherLogger } from './weather-logger';
// Memory usage utility
function getMemoryUsage() {
  if (typeof window !== 'undefined' && 'performance' in window && 'memory' in (window.performance as any)) {
    return (window.performance as any).memory;
  }
  return null;
}

// Resource timing observer utility
function observeResourceTiming(callback: (resources: PerformanceResourceTiming[]) => void) {
  if (typeof window === 'undefined' || !('PerformanceObserver' in window)) {
    callback([]);
    return;
  }

  try {
    const observer = new PerformanceObserver((list) => {
      const resources = list.getEntries() as PerformanceResourceTiming[];
      callback(resources);
    });
    observer.observe({ entryTypes: ['resource'] });
  } catch (error) {
    callback([]);
  }
}

interface WeatherPerformanceMetrics {
  componentRenderTime: number;
  apiRequestTime: number;
  iconLoadTime: number;
  memoryUsage: number;
  bundleSize: number;
  cacheHitRate: number;
}

interface PerformanceEntry {
  name: string;
  startTime: number;
  duration: number;
  transferSize?: number;
}

const COMPONENT_NAME = 'WeatherPerformance';

// Performance thresholds for weather widget
const PERFORMANCE_THRESHOLDS = {
  componentRenderTime: 16, // 16ms for 60fps
  apiRequestTime: 2000, // 2 seconds
  iconLoadTime: 500, // 500ms
  memoryUsage: 10 * 1024 * 1024, // 10MB
  bundleSize: 50 * 1024, // 50KB for weather-related code
};

// Cache for performance metrics
const performanceCache = new Map<string, number>();
let renderStartTime = 0;
let apiRequestCount = 0;
let cacheHitCount = 0;

// Start performance measurement for component render
export function startRenderMeasurement(): void {
  renderStartTime = performance.now();
}

// End performance measurement for component render
export function endRenderMeasurement(componentName: string = 'WeatherWidget'): number {
  const renderTime = performance.now() - renderStartTime;

  if (renderTime > PERFORMANCE_THRESHOLDS.componentRenderTime) {
    weatherLogger.warn(COMPONENT_NAME, `Slow render detected for ${componentName}`, {
      renderTime: `${renderTime.toFixed(2)}ms`,
      threshold: `${PERFORMANCE_THRESHOLDS.componentRenderTime}ms`,
    });
  } else {
    weatherLogger.debug(COMPONENT_NAME, `${componentName} rendered`, {
      renderTime: `${renderTime.toFixed(2)}ms`,
    });
  }

  performanceCache.set('lastRenderTime', renderTime);
  return renderTime;
}

// Measure API request performance
export function measureApiRequest<T>(
  requestPromise: Promise<T>,
  requestName: string = 'weather-api'
): Promise<T> {
  const startTime = performance.now();
  apiRequestCount++;

  return requestPromise
    .then((result) => {
      const duration = performance.now() - startTime;

      weatherLogger.info(COMPONENT_NAME, `API request completed: ${requestName}`, {
        duration: `${duration.toFixed(2)}ms`,
        success: true,
      });

      if (duration > PERFORMANCE_THRESHOLDS.apiRequestTime) {
        weatherLogger.warn(COMPONENT_NAME, `Slow API request: ${requestName}`, {
          duration: `${duration.toFixed(2)}ms`,
          threshold: `${PERFORMANCE_THRESHOLDS.apiRequestTime}ms`,
        });
      }

      performanceCache.set(`${requestName}-duration`, duration);
      return result;
    })
    .catch((error) => {
      const duration = performance.now() - startTime;

      weatherLogger.error(COMPONENT_NAME, `API request failed: ${requestName}`, {
        duration: `${duration.toFixed(2)}ms`,
        error: error.message,
      });

      performanceCache.set(`${requestName}-duration`, duration);
      throw error;
    });
}

// Measure icon loading performance
export function measureIconLoad(iconCode: string): Promise<void> {
  const startTime = performance.now();

  return new Promise((resolve, reject) => {
    const img = new Image();

    img.onload = () => {
      const loadTime = performance.now() - startTime;

      weatherLogger.debug(COMPONENT_NAME, `Icon loaded: ${iconCode}`, {
        loadTime: `${loadTime.toFixed(2)}ms`,
      });

      if (loadTime > PERFORMANCE_THRESHOLDS.iconLoadTime) {
        weatherLogger.warn(COMPONENT_NAME, `Slow icon load: ${iconCode}`, {
          loadTime: `${loadTime.toFixed(2)}ms`,
          threshold: `${PERFORMANCE_THRESHOLDS.iconLoadTime}ms`,
        });
      }

      performanceCache.set(`icon-${iconCode}-loadTime`, loadTime);
      resolve();
    };

    img.onerror = () => {
      const loadTime = performance.now() - startTime;
      weatherLogger.error(COMPONENT_NAME, `Icon load failed: ${iconCode}`, {
        loadTime: `${loadTime.toFixed(2)}ms`,
      });
      reject(new Error(`Failed to load icon: ${iconCode}`));
    };

    img.src = `https://openweathermap.org/img/wn/${iconCode}@2x.png`;
  });
}

// Track cache performance
export function trackCacheHit(): void {
  cacheHitCount++;
}

export function trackCacheMiss(): void {
  // Cache miss is implicit when we don't call trackCacheHit
}

// Calculate cache hit rate
export function getCacheHitRate(): number {
  const totalRequests = apiRequestCount;
  return totalRequests > 0 ? (cacheHitCount / totalRequests) * 100 : 0;
}

// Get current performance metrics
export function getWeatherPerformanceMetrics(): WeatherPerformanceMetrics {
  const memory = getMemoryUsage();

  return {
    componentRenderTime: performanceCache.get('lastRenderTime') || 0,
    apiRequestTime: performanceCache.get('weather-api-duration') || 0,
    iconLoadTime: Array.from(performanceCache.entries())
      .filter(([key]) => key.includes('icon-') && key.includes('loadTime'))
      .reduce((avg, [, time]) => avg + time, 0) / Math.max(1, Array.from(performanceCache.keys()).filter(key => key.includes('icon-')).length),
    memoryUsage: memory?.usedJSHeapSize || 0,
    bundleSize: performanceCache.get('bundleSize') || 0,
    cacheHitRate: getCacheHitRate(),
  };
}

// Analyze weather widget bundle size
export function analyzeWeatherBundleSize(): Promise<void> {
  return new Promise((resolve) => {
    if (typeof window === 'undefined' || !('PerformanceObserver' in window)) {
      resolve();
      return;
    }

    observeResourceTiming((resources) => {
      const weatherRelatedResources = resources.filter(resource =>
        resource.name.includes('weather') ||
        resource.name.includes('geolocation') ||
        resource.name.includes('openweathermap')
      );

      const totalWeatherSize = weatherRelatedResources.reduce(
        (total, resource) => total + (resource.transferSize || 0),
        0
      );

      performanceCache.set('bundleSize', totalWeatherSize);

      weatherLogger.info(COMPONENT_NAME, 'Weather bundle analysis', {
        totalSize: `${(totalWeatherSize / 1024).toFixed(2)} KB`,
        resourceCount: weatherRelatedResources.length,
        resources: weatherRelatedResources.map(r => ({
          name: r.name.split('/').pop(),
          size: `${((r.transferSize || 0) / 1024).toFixed(2)} KB`,
        })),
      });

      if (totalWeatherSize > PERFORMANCE_THRESHOLDS.bundleSize) {
        weatherLogger.warn(COMPONENT_NAME, 'Weather bundle size exceeds threshold', {
          actualSize: `${(totalWeatherSize / 1024).toFixed(2)} KB`,
          threshold: `${(PERFORMANCE_THRESHOLDS.bundleSize / 1024).toFixed(2)} KB`,
        });
      }

      resolve();
    });
  });
}

// Performance monitoring hook for React components
export function useWeatherPerformance(componentName: string) {
  if (typeof window === 'undefined') {
    return {
      startRender: () => {},
      endRender: () => 0,
      measureApi: <T>(promise: Promise<T>) => promise,
    };
  }

  return {
    startRender: () => startRenderMeasurement(),
    endRender: () => endRenderMeasurement(componentName),
    measureApi: <T>(promise: Promise<T>, name?: string) =>
      measureApiRequest(promise, name || 'api-request'),
  };
}

// Generate performance report
export function generateWeatherPerformanceReport(): string {
  const metrics = getWeatherPerformanceMetrics();

  const report = `
Weather Widget Performance Report
================================

Component Performance:
- Render Time: ${metrics.componentRenderTime.toFixed(2)}ms ${metrics.componentRenderTime > PERFORMANCE_THRESHOLDS.componentRenderTime ? '⚠️' : '✅'}
- API Request Time: ${metrics.apiRequestTime.toFixed(2)}ms ${metrics.apiRequestTime > PERFORMANCE_THRESHOLDS.apiRequestTime ? '⚠️' : '✅'}
- Icon Load Time: ${metrics.iconLoadTime.toFixed(2)}ms ${metrics.iconLoadTime > PERFORMANCE_THRESHOLDS.iconLoadTime ? '⚠️' : '✅'}

Resource Usage:
- Memory Usage: ${(metrics.memoryUsage / 1024 / 1024).toFixed(2)} MB ${metrics.memoryUsage > PERFORMANCE_THRESHOLDS.memoryUsage ? '⚠️' : '✅'}
- Bundle Size: ${(metrics.bundleSize / 1024).toFixed(2)} KB ${metrics.bundleSize > PERFORMANCE_THRESHOLDS.bundleSize ? '⚠️' : '✅'}

Caching:
- Cache Hit Rate: ${metrics.cacheHitRate.toFixed(1)}% ${metrics.cacheHitRate < 50 ? '⚠️' : '✅'}

Thresholds:
- Component Render: ${PERFORMANCE_THRESHOLDS.componentRenderTime}ms
- API Request: ${PERFORMANCE_THRESHOLDS.apiRequestTime}ms
- Icon Load: ${PERFORMANCE_THRESHOLDS.iconLoadTime}ms
- Memory Usage: ${(PERFORMANCE_THRESHOLDS.memoryUsage / 1024 / 1024).toFixed(2)} MB
- Bundle Size: ${(PERFORMANCE_THRESHOLDS.bundleSize / 1024).toFixed(2)} KB
`;

  return report;
}

// Initialize weather performance monitoring
export function initWeatherPerformanceMonitoring(): void {
  if (typeof window === 'undefined') return;

  // Only monitor in development or when explicitly enabled
  const shouldMonitor = process.env.NODE_ENV === 'development' ||
                       localStorage.getItem('monitor-weather-performance') === 'true';

  if (!shouldMonitor) return;

  weatherLogger.info(COMPONENT_NAME, 'Weather performance monitoring enabled');

  // Analyze bundle size on load
  window.addEventListener('load', () => {
    setTimeout(() => {
      analyzeWeatherBundleSize();
    }, 2000); // Wait 2 seconds for all resources to load
  });

  // Log performance report every 30 seconds in development
  if (process.env.NODE_ENV === 'development') {
    setInterval(() => {
      console.log(generateWeatherPerformanceReport());
    }, 30000);
  }
}

// Export performance thresholds for testing
export { PERFORMANCE_THRESHOLDS };
