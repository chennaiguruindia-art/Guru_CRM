<?php

namespace App\Filesystem;

use Illuminate\Filesystem\Filesystem as BaseFilesystem;

/**
 * Windows-compatible Filesystem override.
 *
 * Laravel's built-in replace() uses tempnam() in the same directory as $path.
 * On Windows/IIS (Plesk), the IIS application-pool user may not have write
 * permission to create a temp file inside storage/framework/views — even when
 * it CAN write the final file.  PHP then silently falls back to C:\Windows\Temp
 * and emits a PHP Notice which Laravel converts into an ErrorException (HTTP 500).
 *
 * This override writes directly to $path using file_put_contents() with LOCK_EX,
 * which is safe and avoids tempnam() entirely on Windows hosts.
 */
class WindowsFilesystem extends BaseFilesystem
{
    /**
     * Write the contents of a file, replacing it atomically if it already exists.
     *
     * On Windows/IIS we skip the tempnam() dance (which fails under strict
     * open_basedir / limited IIS permissions) and write directly with an
     * exclusive lock, which is sufficient for Blade cache files.
     *
     * @param  string  $path
     * @param  string  $content
     * @param  int|null  $mode
     * @return void
     */
    public function replace($path, $content, $mode = null): void
    {
        clearstatcache(true, $path);

        $path = realpath($path) ?: $path;

        file_put_contents($path, $content, LOCK_EX);

        if (! is_null($mode)) {
            @chmod($path, $mode);
        }
    }
}
