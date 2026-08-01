<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use Exception;
use Illuminate\Http\Response;

class ApiException extends Exception
{
    /**
     * Additional context data for debugging / logging.
     *
     * @var array<string, mixed>
     */
    protected array $context;

    /**
     * Create a new ApiException.
     *
     * @param  array<string, mixed> $context
     */
    public function __construct(
        string $message = 'An unexpected error occurred.',
        int $code = Response::HTTP_INTERNAL_SERVER_ERROR,
        array $context = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);

        $this->context = $context;
    }

    /**
     * Get additional context data.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Create a 400 Bad Request exception.
     */
    public static function badRequest(string $message = 'Bad request.'): static
    {
        return new static($message, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Create a 401 Unauthorized exception.
     */
    public static function unauthorized(string $message = 'Unauthorized.'): static
    {
        return new static($message, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Create a 403 Forbidden exception.
     */
    public static function forbidden(string $message = 'Forbidden.'): static
    {
        return new static($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Create a 500 Internal Server Error exception.
     */
    public static function serverError(string $message = 'Internal server error.'): static
    {
        return new static($message, Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
