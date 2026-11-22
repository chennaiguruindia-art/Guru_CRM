#!/bin/bash
# ==============================================================================
# Production Warmup & Optimization Script for Guru Horticulture CRM
# Run this on deployment or restart to enable ultra-fast response times
# ==============================================================================

echo "🚀 [1/5] Clearing legacy caches..."
php artisan optimize:clear

echo "⚡ [2/5] Caching Configuration files..."
php artisan config:cache

echo "⚡ [3/5] Compiling and Caching Routes..."
php artisan route:cache

echo "⚡ [4/5] Pre-compiling Blade Views..."
php artisan view:cache

echo "⚡ [5/5] Caching Application Events..."
php artisan event:cache

echo "✅ Production optimization complete! App is primed for high-concurrency traffic."
