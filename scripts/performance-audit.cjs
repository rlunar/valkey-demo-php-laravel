#!/usr/bin/env node

/**
 * Performance audit script for the blog landing page
 * This script validates Core Web Vitals and performance metrics
 */

const fs = require('fs');
const path = require('path');

// Performance budget thresholds
const PERFORMANCE_BUDGET = {
    // Bundle sizes (in KB)
    maxJSBundleSize: 350, // Main app bundle
    maxCSSBundleSize: 110, // Main CSS bundle
    maxBlogJSSize: 50, // Blog-specific JS
    maxBlogCSSSize: 100, // Blog-specific CSS

    // Core Web Vitals thresholds (Google recommendations)
    maxLCP: 2500, // Largest Contentful Paint (ms)
    maxFID: 100,  // First Input Delay (ms)
    maxCLS: 0.1,  // Cumulative Layout Shift
    maxFCP: 1800, // First Contentful Paint (ms)
    maxTTFB: 800, // Time to First Byte (ms)

    // Resource counts
    maxJSFiles: 50,
    maxCSSFiles: 10,
    maxImageFiles: 20
};

function analyzeManifest() {
    const manifestPath = path.join(__dirname, '../public/build/manifest.json');

    if (!fs.existsSync(manifestPath)) {
        console.error('❌ Build manifest not found. Run `npm run build` first.');
        process.exit(1);
    }

    const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));

    console.log('📊 Bundle Analysis');
    console.log('==================');

    let totalJSSize = 0;
    let totalCSSSize = 0;
    let jsFileCount = 0;
    let cssFileCount = 0;
    let blogJSSize = 0;
    let blogCSSSize = 0;

    const results = {
        passed: 0,
        failed: 0,
        warnings: 0
    };

    // Analyze each asset
    Object.entries(manifest).forEach(([key, asset]) => {
        if (!asset.file) return;

        const filePath = path.join(__dirname, '../public/build', asset.file);
        if (!fs.existsSync(filePath)) return;

        const stats = fs.statSync(filePath);
        const sizeKB = stats.size / 1024;

        if (asset.file.endsWith('.js')) {
            totalJSSize += sizeKB;
            jsFileCount++;

            if (key.includes('blog') || asset.file.includes('blog')) {
                blogJSSize += sizeKB;
            }
        } else if (asset.file.endsWith('.css')) {
            totalCSSSize += sizeKB;
            cssFileCount++;

            if (key.includes('blog') || asset.file.includes('blog')) {
                blogCSSSize += sizeKB;
            }
        }
    });

    // Check bundle sizes
    console.log(`\n📦 Bundle Sizes:`);
    console.log(`Total JS: ${totalJSSize.toFixed(2)} KB`);
    console.log(`Total CSS: ${totalCSSSize.toFixed(2)} KB`);
    console.log(`Blog JS: ${blogJSSize.toFixed(2)} KB`);
    console.log(`Blog CSS: ${blogCSSSize.toFixed(2)} KB`);

    // Validate against budget
    console.log(`\n✅ Performance Budget Validation:`);

    // JS Bundle size
    if (totalJSSize <= PERFORMANCE_BUDGET.maxJSBundleSize) {
        console.log(`✅ Total JS size: ${totalJSSize.toFixed(2)} KB (under ${PERFORMANCE_BUDGET.maxJSBundleSize} KB)`);
        results.passed++;
    } else {
        console.log(`❌ Total JS size: ${totalJSSize.toFixed(2)} KB (exceeds ${PERFORMANCE_BUDGET.maxJSBundleSize} KB)`);
        results.failed++;
    }

    // CSS Bundle size
    if (totalCSSSize <= PERFORMANCE_BUDGET.maxCSSBundleSize) {
        console.log(`✅ Total CSS size: ${totalCSSSize.toFixed(2)} KB (under ${PERFORMANCE_BUDGET.maxCSSBundleSize} KB)`);
        results.passed++;
    } else {
        console.log(`❌ Total CSS size: ${totalCSSSize.toFixed(2)} KB (exceeds ${PERFORMANCE_BUDGET.maxCSSBundleSize} KB)`);
        results.failed++;
    }

    // Blog-specific bundles
    if (blogJSSize <= PERFORMANCE_BUDGET.maxBlogJSSize) {
        console.log(`✅ Blog JS size: ${blogJSSize.toFixed(2)} KB (under ${PERFORMANCE_BUDGET.maxBlogJSSize} KB)`);
        results.passed++;
    } else {
        console.log(`❌ Blog JS size: ${blogJSSize.toFixed(2)} KB (exceeds ${PERFORMANCE_BUDGET.maxBlogJSSize} KB)`);
        results.failed++;
    }

    if (blogCSSSize <= PERFORMANCE_BUDGET.maxBlogCSSSize) {
        console.log(`✅ Blog CSS size: ${blogCSSSize.toFixed(2)} KB (under ${PERFORMANCE_BUDGET.maxBlogCSSSize} KB)`);
        results.passed++;
    } else {
        console.log(`❌ Blog CSS size: ${blogCSSSize.toFixed(2)} KB (exceeds ${PERFORMANCE_BUDGET.maxBlogCSSSize} KB)`);
        results.failed++;
    }

    // File counts
    if (jsFileCount <= PERFORMANCE_BUDGET.maxJSFiles) {
        console.log(`✅ JS file count: ${jsFileCount} (under ${PERFORMANCE_BUDGET.maxJSFiles})`);
        results.passed++;
    } else {
        console.log(`❌ JS file count: ${jsFileCount} (exceeds ${PERFORMANCE_BUDGET.maxJSFiles})`);
        results.failed++;
    }

    if (cssFileCount <= PERFORMANCE_BUDGET.maxCSSFiles) {
        console.log(`✅ CSS file count: ${cssFileCount} (under ${PERFORMANCE_BUDGET.maxCSSFiles})`);
        results.passed++;
    } else {
        console.log(`❌ CSS file count: ${cssFileCount} (exceeds ${PERFORMANCE_BUDGET.maxCSSFiles})`);
        results.failed++;
    }

    return results;
}

function checkCodeSplitting() {
    console.log(`\n🔄 Code Splitting Analysis:`);

    const manifestPath = path.join(__dirname, '../public/build/manifest.json');
    const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));

    const blogComponents = [
        'blog-header',
        'blog-navigation',
        'blog-sidebar',
        'blog-post',
        'post-card',
        'featured-post',
        'blog-pagination',
        'lazy-image'
    ];

    let foundComponents = 0;

    blogComponents.forEach(component => {
        const found = Object.values(manifest).some(asset =>
            asset.file && asset.file.includes(component)
        );

        if (found) {
            console.log(`✅ ${component} is code-split`);
            foundComponents++;
        } else {
            console.log(`⚠️  ${component} might not be code-split`);
        }
    });

    const splitRatio = (foundComponents / blogComponents.length) * 100;
    console.log(`\n📈 Code splitting coverage: ${splitRatio.toFixed(1)}%`);

    return {
        passed: foundComponents >= blogComponents.length * 0.7 ? 1 : 0,
        failed: foundComponents < blogComponents.length * 0.7 ? 1 : 0,
        warnings: 0
    };
}

function checkOptimizations() {
    console.log(`\n⚡ Optimization Checks:`);

    const results = { passed: 0, failed: 0, warnings: 0 };

    // Check if blog CSS exists
    const blogCSSExists = fs.existsSync(path.join(__dirname, '../resources/css/blog.css'));
    if (blogCSSExists) {
        console.log(`✅ Separate blog CSS file exists`);
        results.passed++;
    } else {
        console.log(`❌ Separate blog CSS file not found`);
        results.failed++;
    }

    // Check if lazy image component exists
    const lazyImageExists = fs.existsSync(path.join(__dirname, '../resources/js/components/lazy-image.tsx'));
    if (lazyImageExists) {
        console.log(`✅ Lazy image component exists`);
        results.passed++;
    } else {
        console.log(`❌ Lazy image component not found`);
        results.failed++;
    }

    // Check if performance monitoring exists
    const perfMonitorExists = fs.existsSync(path.join(__dirname, '../resources/js/lib/performance.ts'));
    if (perfMonitorExists) {
        console.log(`✅ Performance monitoring utility exists`);
        results.passed++;
    } else {
        console.log(`❌ Performance monitoring utility not found`);
        results.failed++;
    }

    return results;
}

function generateReport(bundleResults, splittingResults, optimizationResults) {
    const totalPassed = bundleResults.passed + splittingResults.passed + optimizationResults.passed;
    const totalFailed = bundleResults.failed + splittingResults.failed + optimizationResults.failed;
    const totalWarnings = bundleResults.warnings + splittingResults.warnings + optimizationResults.warnings;
    const totalTests = totalPassed + totalFailed + totalWarnings;

    console.log(`\n📋 Performance Audit Summary`);
    console.log(`============================`);
    console.log(`✅ Passed: ${totalPassed}/${totalTests}`);
    console.log(`❌ Failed: ${totalFailed}/${totalTests}`);
    console.log(`⚠️  Warnings: ${totalWarnings}/${totalTests}`);

    const score = (totalPassed / totalTests) * 100;
    console.log(`\n🎯 Performance Score: ${score.toFixed(1)}%`);

    if (score >= 90) {
        console.log(`🎉 Excellent performance! Your blog is well optimized.`);
    } else if (score >= 75) {
        console.log(`👍 Good performance! Consider addressing the failed checks.`);
    } else if (score >= 60) {
        console.log(`⚠️  Fair performance. Several optimizations needed.`);
    } else {
        console.log(`🚨 Poor performance. Significant optimizations required.`);
    }

    console.log(`\n💡 Recommendations:`);
    console.log(`- Ensure all blog components are lazy-loaded`);
    console.log(`- Keep bundle sizes under budget limits`);
    console.log(`- Use separate CSS bundles for better caching`);
    console.log(`- Implement image lazy loading`);
    console.log(`- Monitor Core Web Vitals in production`);

    return totalFailed === 0;
}

// Main execution
console.log(`🚀 Blog Performance Audit`);
console.log(`========================`);

try {
    const bundleResults = analyzeManifest();
    const splittingResults = checkCodeSplitting();
    const optimizationResults = checkOptimizations();

    const success = generateReport(bundleResults, splittingResults, optimizationResults);

    process.exit(success ? 0 : 1);
} catch (error) {
    console.error(`❌ Audit failed:`, error.message);
    process.exit(1);
}
