<?php

declare(strict_types=1);

namespace App\Core\DTO;

/**
 * Immutable value object representing a standard API response envelope.
 *
 * JSON shape:
 * {
 *   "success": true,
 *   "message": "...",
 *   "data": {...}|[...]|null,
 *   "errors": null|{...},
 *   "meta": null|{...}
 * }
 */
final readonly class ApiResponseDTO
{
    /**
     * @param  bool                       $success
     * @param  string                     $message
     * @param  mixed                      $data
     * @param  array<string, mixed>|null  $errors
     * @param  array<string, mixed>|null  $meta
     * @param  int                        $statusCode
     */
    public function __construct(
        public bool    $success,
        public string  $message,
        public mixed   $data       = null,
        public ?array  $errors     = null,
        public ?array  $meta       = null,
        public int     $statusCode = 200,
    ) {}

    /**
     * Serialize to array for JSON encoding.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data'    => $this->data,
            'errors'  => $this->errors,
            'meta'    => $this->meta,
        ];
    }
}
