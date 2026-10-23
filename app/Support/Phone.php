<?php

namespace App\Support;

/**
 * A mobile number is the login identifier, so it has to match however somebody
 * happened to type it. "98765 43210", "09876543210" and "+91 98765 43210" are
 * the same person, and locking a field marketer out because they dropped the
 * country code would be a poor way to explain the change.
 */
class Phone
{
    /**
     * Reduce a raw mobile number to the one form it is stored and compared in.
     *
     * Returns null when there is nothing usable, so callers can tell "no
     * number" apart from an empty string rather than storing ''.
     */
    public static function normalize(?string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $raw);

        if ($digits === null || $digits === '') {
            return null;
        }

        // Drop the national trunk prefix (0…) or the country code (91…), but
        // only when what remains is a full mobile number. A shorter number
        // that merely begins with those digits must be left alone, or it would
        // be silently truncated into somebody else's.
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return substr($digits, 1);
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return substr($digits, 2);
        }

        return $digits;
    }
}
