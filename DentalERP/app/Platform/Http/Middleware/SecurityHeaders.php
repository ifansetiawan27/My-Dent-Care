<?php

declare(strict_types=1);

namespace App\Platform\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies baseline security response headers to every API response.
 *
 * The values are configurable through config/security.php so that staging and
 * production can tighten or relax individual headers without a code change.
 */
final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        foreach ($this->headers() as $header => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $response->headers->set($header, $value, false);
        }

        if ((bool) config('security.hsts.enabled', false) && $request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                $this->hstsValue(),
                false,
            );
        }

        return $response;
    }

    /**
     * @return array<string, string|null>
     */
    private function headers(): array
    {
        return [
            'X-Content-Type-Options'    => config('security.headers.x_content_type_options'),
            'X-Frame-Options'           => config('security.headers.x_frame_options'),
            'Referrer-Policy'           => config('security.headers.referrer_policy'),
            'Permissions-Policy'        => config('security.headers.permissions_policy'),
            'Content-Security-Policy'   => config('security.headers.content_security_policy'),
            'Cross-Origin-Opener-Policy'   => config('security.headers.cross_origin_opener_policy'),
            'Cross-Origin-Resource-Policy' => config('security.headers.cross_origin_resource_policy'),
        ];
    }

    private function hstsValue(): string
    {
        $value = 'max-age=' . (int) config('security.hsts.max_age', 31536000);

        if ((bool) config('security.hsts.include_subdomains', true)) {
            $value .= '; includeSubDomains';
        }

        if ((bool) config('security.hsts.preload', false)) {
            $value .= '; preload';
        }

        return $value;
    }
}
