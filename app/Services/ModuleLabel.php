<?php

namespace App\Services;

/**
 * Display wording for stored module slugs.
 *
 * A slug is a key, not a label: permission rows, audit rows, routes and the
 * `permission:` middleware all reference it, so it must never change. Only
 * what a human reads is mapped here — which is where the
 * Leads -> Visits and Customers -> Clients rename surfaces for the
 * Roles screens and the audit log.
 */
class ModuleLabel
{
    public static function for(string $slug): string
    {
        static $overrides = [
            'leads'     => 'Visits',
            'customers' => 'Clients',
            'amc'       => 'AMC',
        ];

        return $overrides[$slug] ?? ucwords(str_replace('_', ' ', $slug));
    }
}
