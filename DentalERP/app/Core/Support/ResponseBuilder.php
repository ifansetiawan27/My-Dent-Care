<?php

declare(strict_types=1);

namespace App\Core\Support;

use App\Core\DTO\ApiResponseDTO;
use Illuminate\Http\JsonResponse;

/**
 * Fluent builder for constructing custom API responses.
 *
 * Usage:
 *   ResponseBuilder::make()
 *       ->success()
 *       ->message('Done.')
 *       ->data($payload)
 *       ->meta(['extra' => 'value'])
 *       ->statusCode(200)
 *       ->build();
 */
final class ResponseBuilder
{
    private bool    $success    = true;
    private string  $message    = '';
    private mixed   $data       = null;
    private ?array  $errors     = null;  // @phpstan-ignore-line
    private ?array  $meta       = null;  // @phpstan-ignore-line
    private int     $statusCode = 200;

    /**
     * Static factory method.
     */
    public static function make(): static
    {
        return new static();
    }

    /**
     * Mark response as successful.
     */
    public function success(bool $value = true): static
    {
        $this->success = $value;

        return $this;
    }

    /**
     * Mark response as failed.
     */
    public function failure(): static
    {
        $this->success = false;

        return $this;
    }

    /**
     * Set the response message.
     */
    public function message(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Set the response data payload.
     */
    public function data(mixed $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Set validation / field errors.
     *
     * @param  array<string, mixed> $errors
     */
    public function errors(array $errors): static
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * Set response meta (pagination, totals, etc).
     *
     * @param  array<string, mixed> $meta
     */
    public function meta(array $meta): static
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * Set the HTTP status code.
     */
    public function statusCode(int $code): static
    {
        $this->statusCode = $code;

        return $this;
    }

    /**
     * Build the DTO.
     */
    public function toDTO(): ApiResponseDTO
    {
        return new ApiResponseDTO(
            success:    $this->success,
            message:    $this->message,
            data:       $this->data,
            errors:     $this->errors,
            meta:       $this->meta,
            statusCode: $this->statusCode,
        );
    }

    /**
     * Build and return a JsonResponse.
     */
    public function build(): JsonResponse
    {
        $dto = $this->toDTO();

        return new JsonResponse($dto->toArray(), $dto->statusCode);
    }
}
