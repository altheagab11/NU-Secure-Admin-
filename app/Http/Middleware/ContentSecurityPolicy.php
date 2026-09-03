<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(16));

        $request->attributes->set('cspNonce', $nonce);
        View::share('cspNonce', $nonce);

        /** @var Response $response */
        $response = $next($request);

        $policy = $this->policy($nonce);

        $response->headers->set('Content-Security-Policy', $policy);

        // Required by the current .htaccess CSP forwarding workaround.
        $response->headers->set('X-CSP-Generated', $policy);

        return $response;
    }

    protected function policy(string $nonce): string
    {
        $imgSrc = "'self' data: blob:";

        $supabaseOrigin = $this->supabaseImageOrigin();

        if ($supabaseOrigin !== null) {
            $imgSrc .= ' '.$supabaseOrigin;
        }

        return implode('; ', [
            "default-src 'self'",

            "script-src 'self' 'nonce-{$nonce}' https://challenges.cloudflare.com https://cdn.jsdelivr.net https://unpkg.com",

            "style-src 'self' 'nonce-{$nonce}' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",

            "img-src {$imgSrc}",

            "connect-src 'self'",

            "font-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",

            "media-src 'self' blob:",

            "manifest-src 'none'",

            "frame-src 'self' https://challenges.cloudflare.com",

            "worker-src 'self' blob:",

            "form-action 'self'",

            "frame-ancestors 'self'",

            "base-uri 'self'",

            "object-src 'none'",

            "upgrade-insecure-requests",
        ]);
    }

    protected function supabaseImageOrigin(): ?string
    {
        $url = rtrim(
            (string) (config('services.supabase.url') ?: env('SUPABASE_URL', '')),
            '/'
        );

        if ($url === '') {
            return null;
        }

        $parts = parse_url($url);

        if (
            empty($parts['scheme']) ||
            empty($parts['host'])
        ) {
            return null;
        }

        $origin = $parts['scheme'].'://'.$parts['host'];

        if (! empty($parts['port'])) {
            $origin .= ':'.$parts['port'];
        }

        return $origin;
    }
}