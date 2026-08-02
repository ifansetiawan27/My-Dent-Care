<?php

declare(strict_types=1);

namespace App\Platform\IntegrationHub\DTO;

/**
 * IntegrationResponseDTO
 *
 * Immutable value object describing the result of an external integration call.
 * Normalizes provider-specific responses into a single shape for domains.
 */
final readonly class IntegrationResponseDTO
{
    /**
     * @param  bool                  $success       Whether the call succeeded.
     * @param  int                   $statusCode    HTTP-like status code.
     * @param  array<string, mixed>  $data          Normalized response data.
     * @param  string|null           $errorMessage  Error message when unsuccessful.
     * @param  array<string, mixed>  $raw           Raw provider response (for debugging).
     */
    public function __construct(
        public bool    $success,
        public int     $statusCode,
        public array   $data         = [],
        public ?string $errorMessage = null,
        public array   $raw          = [],
    ) {}

    /**
     * Create a successful response.
     *
     * @param  array<string, mixed> $data
     * @param  array<string, mixed> $raw
     */
    public static function success(array $data = [], int $statusCode = 200, array $raw = []): self
    {
        return new self(true, $statusCode, $data, null, $raw);
    }

    /**
     * Create a failed response.
     *
     * @param  array<string, mixed> $raw
     */
    public static function failure(string $errorMessage, int $statusCode = 500, array $raw = []): self
    {
        return new self(false, $statusCode, [], $errorMessage, $raw);
    }
}
