<?php

declare(strict_types=1);

namespace Tests\Feature\Platform\Http;

use Tests\TestCase;

/**
 * The application serves an API only and defines no `login` route, so an
 * unauthenticated request must always produce a JSON 401 — never a redirect.
 *
 * These cases deliberately use plain requests instead of the JSON helpers,
 * because the failure only appears when `Accept: application/json` is absent.
 */
class ApiErrorRenderingTest extends TestCase
{
    public function test_unauthenticated_request_without_json_accept_header_returns_401(): void
    {
        $response = $this->get('/api/v1/branches');

        $response->assertStatus(401);
    }

    public function test_unauthenticated_request_without_json_accept_header_returns_json(): void
    {
        $response = $this->get('/api/v1/branches');

        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_unauthenticated_request_is_not_redirected(): void
    {
        $response = $this->get('/api/v1/branches');

        $response->assertHeaderMissing('Location');
    }

    public function test_unknown_route_returns_json_404(): void
    {
        $response = $this->get('/api/v1/this-route-does-not-exist');

        $response->assertStatus(404);
        $response->assertHeader('Content-Type', 'application/json');
    }

    public function test_health_endpoint_is_available(): void
    {
        $this->get('/up')->assertStatus(200);
    }

    public function test_health_endpoint_carries_security_headers(): void
    {
        $this->get('/up')->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
