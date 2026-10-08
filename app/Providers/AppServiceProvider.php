<?php

namespace App\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Automatically ensure missing columns exist on the users table (e.g., soft deletes on production)
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
                    $table->softDeletes();
                });
            }
        } catch (\Throwable) {
            // Silently ignore if database connection is unavailable
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
