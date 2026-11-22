<?php

namespace App\Providers;

use App\Filesystem\WindowsFilesystem;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * Swap in our Windows-compatible Filesystem so that Blade cache writes
     * never hit the tempnam() / open_basedir crash on Plesk / IIS hosts.
     */
    public function register(): void
    {
        $this->app->singleton('files', fn () => new WindowsFilesystem());
        $this->app->bind(Filesystem::class, WindowsFilesystem::class);

        // On Windows / IIS (Plesk) the Application Pool user often cannot write
        // to storage/framework/views. We detect this and redirect compiled Blade
        // views to the system temp directory which IIS *can* always write to.
        $defaultCompiled = storage_path('framework/views');

        if (! is_writable($defaultCompiled)) {
            $compiledPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'guruindia_views';

            if (! is_dir($compiledPath)) {
                @mkdir($compiledPath, 0755, true);
            }

            // Override the compiled view path before the view service provider boots
            $this->app['config']->set('view.compiled', $compiledPath);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enforce Indian Standard Time (IST UTC+5:30) across entire application
        date_default_timezone_set('Asia/Kolkata');

        // Configure Rate Limiters for High-Concurrency Protection
        RateLimiter::for('login', function (Request $request) {
            $phone = (string) ($request->input('phone') ?: $request->input('login'));
            return Limit::perMinute(15)->by($phone . '|' . $request->ip());
        });

        RateLimiter::for('employee-login', function (Request $request) {
            $code = (string) $request->input('employee_code');
            return Limit::perMinute(20)->by($code . '|' . $request->ip());
        });

        RateLimiter::for('global-web', function (Request $request) {
            return Limit::perMinute(200)->by($request->user()?->id ?: $request->ip());
        });

        // Automatically ensure missing columns exist on users table only during console/migrations
        // (Avoids running slow information_schema queries on every user web request)
        if ($this->app->runningInConsole()) {
            try {
                if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'deleted_at')) {
                    Schema::table('users', function (Blueprint $table) {
                        if (! Schema::hasColumn('users', 'phone')) {
                            $table->string('phone')->nullable();
                        }
                        if (! Schema::hasColumn('users', 'avatar')) {
                            $table->string('avatar')->nullable();
                        }
                        if (! Schema::hasColumn('users', 'status')) {
                            $table->string('status')->default('active');
                        }
                        if (! Schema::hasColumn('users', 'username')) {
                            $table->string('username')->nullable()->unique();
                        }
                        if (! Schema::hasIndex('users', 'users_phone_unique')) {
                            $table->unique('phone');
                        }
                        $table->softDeletes();
                    });
                }
            } catch (\Throwable) {
                // Silently ignore if database connection is unavailable
            }
        }
        /**
         * Permission slugs are dotted ("leads.create"), which is the same shape
         * Laravel uses for policy abilities. This app defines no policies, so
         * any dotted ability is mapped straight onto the RBAC table via
         * User::hasPermission(). Without this, @can('branches.create') and the
         * pre-existing @can('roles.create') silently evaluate to false for
         * everyone, including Super Admin.
         */
        Gate::before(function ($user, string $ability) {
            if (! str_contains($ability, '.')) {
                return null; // not a permission slug — fall through normally
            }

            return $user ? $user->hasPermission($ability) : false;
        });
    }
}
