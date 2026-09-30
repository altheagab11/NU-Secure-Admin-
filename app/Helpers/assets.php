<?php

if (! function_exists('versioned_asset')) {
    /**
     * Generate a stable cache-busted asset URL.
     * Uses filemtime when the public file exists; otherwise falls back to config.
     * Never uses time() (that would reload the asset on every request).
     */
    function versioned_asset(string $path): string
    {
        $relative = ltrim(str_replace('\\', '/', $path), '/');
        $absolute = public_path($relative);

        $version = is_file($absolute)
            ? (string) filemtime($absolute)
            : (string) config('app.asset_version', '1');

        return asset($relative).'?v='.$version;
    }
}
