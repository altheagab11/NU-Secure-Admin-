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

        $response->headers->set(
            'Content-Security-Policy',
            $this->policy($nonce)
        );

        return $response;
    }

    protected function policy(string $nonce): string
    {
        return implode('; ', [
            "script-src 'self' 'nonce-{$nonce}' https://challenges.cloudflare.com https://cdn.jsdelivr.net https://unpkg.com",
            "frame-src 'self' https://challenges.cloudflare.com",
            "worker-src 'self' blob:",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "upgrade-insecure-requests",
        ]);
    }
}