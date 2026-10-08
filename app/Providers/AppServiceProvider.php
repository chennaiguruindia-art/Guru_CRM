<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
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
