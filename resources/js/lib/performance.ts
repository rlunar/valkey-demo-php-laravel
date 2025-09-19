// Core Web Vitals monitoring utility
export interface WebVitalsMetric {
    name: string;
    value: number;
    rating: 'good' | 'needs-improvement' | 'poor';
    delta: number;
    id: string;
}

// Thresholds based on Google's Core Web Vitals
const THRESHOLDS = {
    CLS: { good: 0.1, poor: 0.25 },
    FID: { good: 100, poor: 300 },
    FCP: { good: 1800, poor: 3000 },
    LCP: { good: 2500, poor: 4000 },
    TTFB: { good: 800, poor: 1800 },
    INP: { good: 200, poor: 500 }
};

function getRating(name: string, value: number): 'good' | 'needs-improvement' | 'poor' {
    const threshold = THRESHOLDS[name as keyof typeof THRESHOLDS];
    if (!threshold) return 'good';

    if (value <= threshold.good) return 'good';
    if (value <= threshold.poor) return 'needs-improvement';
    return 'poor';
}

// Performance observer for Core Web Vitals
export function observeWebVitals(callback: (metric: WebVitalsMetric) => void) {
    // Only run in browser environment
    if (typeof window === 'undefined') return;

    // Largest Contentful Paint (LCP)
    if ('PerformanceObserver' in window) {
        try {
            const lcpObserver = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                const lastEntry = entries[entries.length - 1] as PerformanceEntry & { startTime: number };

                callback({
                    name: 'LCP',
                    value: lastEntry.startTime,
                    rating: getRating('LCP', lastEntry.startTime),
                    delta: lastEntry.startTime,
                    id: 'lcp-' + Date.now()
                });
            });

            lcpObserver.observe({ type: 'largest-contentful-paint', buffered: true });
        } catch (e) {
            console.warn('LCP observer not supported');
        }

        // First Input Delay (FID)
        try {
            const fidObserver = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                entries.forEach((entry: any) => {
                    callback({
                        name: 'FID',
                        value: entry.processingStart - entry.startTime,
                        rating: getRating('FID', entry.processingStart - entry.startTime),
                        delta: entry.processingStart - entry.startTime,
                        id: 'fid-' + Date.now()
                    });
                });
            });

            fidObserver.observe({ type: 'first-input', buffered: true });
        } catch (e) {
            console.warn('FID observer not supported');
        }

        // Cumulative Layout Shift (CLS)
        try {
            let clsValue = 0;
            const clsObserver = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                entries.forEach((entry: any) => {
                    if (!entry.hadRecentInput) {
                        clsValue += entry.value;
                    }
                });

                callback({
                    name: 'CLS',
                    value: clsValue,
                    rating: getRating('CLS', clsValue),
                    delta: clsValue,
                    id: 'cls-' + Date.now()
                });
            });

            clsObserver.observe({ type: 'layout-shift', buffered: true });
        } catch (e) {
            console.warn('CLS observer not supported');
        }

        // First Contentful Paint (FCP)
        try {
            const fcpObserver = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                entries.forEach((entry) => {
                    if (entry.name === 'first-contentful-paint') {
                        callback({
                            name: 'FCP',
                            value: entry.startTime,
                            rating: getRating('FCP', entry.startTime),
                            delta: entry.startTime,
                            id: 'fcp-' + Date.now()
                        });
                    }
                });
            });

            fcpObserver.observe({ type: 'paint', buffered: true });
        } catch (e) {
            console.warn('FCP observer not supported');
        }
    }

    // Time to First Byte (TTFB) using Navigation Timing
    if ('performance' in window && 'getEntriesByType' in performance) {
        window.addEventListener('load', () => {
            const navigationEntries = performance.getEntriesByType('navigation') as PerformanceNavigationTiming[];
            if (navigationEntries.length > 0) {
                const entry = navigationEntries[0];
                const ttfb = entry.responseStart - entry.requestStart;

                callback({
                    name: 'TTFB',
                    value: ttfb,
                    rating: getRating('TTFB', ttfb),
                    delta: ttfb,
                    id: 'ttfb-' + Date.now()
                });
            }
        });
    }
}

// Resource loading performance
export function observeResourceTiming(callback: (resources: PerformanceResourceTiming[]) => void) {
    if (typeof window === 'undefined' || !('PerformanceObserver' in window)) return;

    try {
        const resourceObserver = new PerformanceObserver((list) => {
            const entries = list.getEntries() as PerformanceResourceTiming[];
            callback(entries);
        });

        resourceObserver.observe({ type: 'resource', buffered: true });
    } catch (e) {
        console.warn('Resource timing observer not supported');
    }
}

// Memory usage monitoring
export function getMemoryUsage() {
    if (typeof window === 'undefined' || !('performance' in window)) return null;

    const memory = (performance as any).memory;
    if (!memory) return null;

    return {
        usedJSHeapSize: memory.usedJSHeapSize,
        totalJSHeapSize: memory.totalJSHeapSize,
        jsHeapSizeLimit: memory.jsHeapSizeLimit,
        usagePercentage: (memory.usedJSHeapSize / memory.jsHeapSizeLimit) * 100
    };
}

// Bundle size analysis
export function analyzeBundleSize() {
    if (typeof window === 'undefined' || !('PerformanceObserver' in window)) return;

    const resourceObserver = new PerformanceObserver((list) => {
        const entries = list.getEntries() as PerformanceResourceTiming[];
        const jsResources = entries.filter(entry =>
            entry.name.includes('.js') || entry.name.includes('.tsx') || entry.name.includes('.ts')
        );
        const cssResources = entries.filter(entry => entry.name.includes('.css'));

        const totalJSSize = jsResources.reduce((total, entry) => total + (entry.transferSize || 0), 0);
        const totalCSSSize = cssResources.reduce((total, entry) => total + (entry.transferSize || 0), 0);

        console.group('Bundle Size Analysis');
        console.log(`Total JS Size: ${(totalJSSize / 1024).toFixed(2)} KB`);
        console.log(`Total CSS Size: ${(totalCSSSize / 1024).toFixed(2)} KB`);
        console.log(`JS Resources:`, jsResources.map(r => ({ name: r.name, size: `${((r.transferSize || 0) / 1024).toFixed(2)} KB` })));
        console.log(`CSS Resources:`, cssResources.map(r => ({ name: r.name, size: `${((r.transferSize || 0) / 1024).toFixed(2)} KB` })));
        console.groupEnd();
    });

    resourceObserver.observe({ type: 'resource', buffered: true });
}

// Performance budget checker
export function checkPerformanceBudget() {
    const budget = {
        maxJSSize: 250 * 1024, // 250KB
        maxCSSSize: 50 * 1024, // 50KB
        maxLCP: 2500, // 2.5s
        maxFID: 100, // 100ms
        maxCLS: 0.1 // 0.1
    };

    return budget;
}

// Initialize performance monitoring for blog page
export function initBlogPerformanceMonitoring() {
    if (typeof window === 'undefined') return;

    // Only monitor in development or when explicitly enabled
    const shouldMonitor = process.env.NODE_ENV === 'development' ||
                         localStorage.getItem('monitor-performance') === 'true';

    if (!shouldMonitor) return;

    console.log('🚀 Blog Performance Monitoring Enabled');

    observeWebVitals((metric) => {
        console.log(`📊 ${metric.name}: ${metric.value.toFixed(2)}ms (${metric.rating})`);

        // Log warnings for poor performance
        if (metric.rating === 'poor') {
            console.warn(`⚠️ Poor ${metric.name} performance detected: ${metric.value.toFixed(2)}ms`);
        }
    });

    // Monitor memory usage
    setInterval(() => {
        const memory = getMemoryUsage();
        if (memory && memory.usagePercentage > 80) {
            console.warn(`🧠 High memory usage: ${memory.usagePercentage.toFixed(2)}%`);
        }
    }, 10000); // Check every 10 seconds

    // Analyze bundle size on load
    window.addEventListener('load', () => {
        setTimeout(analyzeBundleSize, 1000);
    });
}
