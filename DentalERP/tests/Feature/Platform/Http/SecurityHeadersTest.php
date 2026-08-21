<?php

declare(strict_types=1);

namespace Tests\Feature\Platform\Http;

use Tests\TestCase;

/**
 * Verifies the baseline security headers applied by
 * App\Platform\Http\Middleware\SecurityHeaders.
 */
class SecurityHeadersTest extends TestCase
{
    public function test_baseline_security_headers_are_present_on_api_responses(): void
    {
        $response = $this->getJson('/api/v1/branches');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'no-referrer');
        $response->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
        $response->assertHeader('Cross-Origin-Resource-Policy', 'same-site');
    }

    public function test_content_security_policy_denies_all_sources(): void
    {
        $response = $this->getJson('/api/v1/branches');

        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp);
        $this->assertStringContainsString("default-src 'none'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
    }

    public function test_permissions_policy_disables_sensitive_features(): void
    {
        $response = $this->getJson('/api/v1/branches');

        $policy = $response->headers->get('Permissions-Policy');

        $this->assertNotNull($policy);
        $this->assertStringContainsString('camera=()', $policy);
        $this->assertStringContainsString('microphone=()', $policy);
        $this->assertStringContainsString('geolocation=()', $policy);
    }

    public function test_hsts_is_not_emitted_over_plain_http(): void
    {
        config(['security.hsts.enabled' => true]);

        $response = $this->getJson('http://localhost/api/v1/branches');

        $this->assertNull($response->headers->get('Strict-Transport-Security'));
    }

    public function test_hsts_is_emitted_over_https_when_enabled(): void
    {
        config(['security.hsts.enabled' => true]);

        $response = $this->getJson('https://localhost/api/v1/branches');

        $this->assertSame(
            'max-age=31536000; includeSubDomains',
            $response->headers->get('Strict-Transport-Security'),
        );
    }

    public function test_hsts_is_absent_when_disabled(): void
    {
        config(['security.hsts.enabled' => false]);

        $response = $this->getJson('https://localhost/api/v1/branches');

        $this->assertNull($response->headers->get('Strict-Transport-Security'));
    }
}
