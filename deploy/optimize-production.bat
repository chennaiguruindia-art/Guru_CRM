@echo off
echo ==============================================================================
echo Production Warmup and Optimization for Guru Horticulture CRM
echo ==============================================================================

echo [1/5] Clearing legacy caches...
php artisan optimize:clear

echo [2/5] Caching Configuration files...
php artisan config:cache

echo [3/5] Compiling and Caching Routes...
php artisan route:cache

echo [4/5] Pre-compiling Blade Views...
php artisan view:cache

echo [5/5] Caching Application Events...
php artisan event:cache

echo ==============================================================================
echo Production optimization complete! App is primed for high-concurrency traffic.
echo ==============================================================================
