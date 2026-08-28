<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCacheControlHeaders
{
    public const CACHE_CONTROL =
        'no-store, no-cache, must-revalidate, private, max-age=0';

    /**
     * @var array<int, string>
     */
    private const STATIC_PATHS = [
        'robots.txt',
        'favicon.ico',
    ];

    /**
     * @var array<int, string>
     */
    private const STATIC_EXTENSIONS = [
        'css',
        'js',
        'mjs',
        'map',
        'png',
        'jpg',
        'jpeg',
        'gif',
        'webp',
        'svg',
        'ico',
        'woff',
        'woff2',
        'ttf',
        'eot',
        'otf',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($this->shouldSkip($request, $response)) {
            return $response;
        }

        $response->headers->remove('Cache-Control');

        $response->headers->set(
            'Cache-Control',
            self::CACHE_CONTROL
        );

        $response->headers->set('Pragma', 'no-cache', true);
        $response->headers->set('Expires', '0', true);

        return $response;
    }

    protected function shouldSkip(
        Request $request,
        Response $response
    ): bool {
        if ($this->isStaticAssetRequest($request)) {
            return true;
        }

        $contentType = strtolower(
            (string) $response->headers->get('Content-Type', '')
        );

        if ($contentType === '') {
            return true;
        }

        return ! (
            str_contains($contentType, 'text/html') ||
            str_contains($contentType, 'application/json')
        );
    }

    protected function isStaticAssetRequest(Request $request): bool
    {
        $path = strtolower($request->path());

        if (in_array($path, self::STATIC_PATHS, true)) {
            return true;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return $extension !== ''
            && in_array(
                strtolower($extension),
                self::STATIC_EXTENSIONS,
                true
            );
    }
}