<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use Exception;
use Illuminate\Http\Response;

class BusinessException extends Exception
{
    /**
     * Additional context data for logging / API response.
     *
     * @var array<string, mixed>
     */
    protected array $context;

    /**
     * Create a new BusinessException.
     *
     * @param  array<string, mixed> $context
     */
    public function __construct(
        string $message = 'Business rule violation.',
        int $code = Response::HTTP_UNPROCESSABLE_ENTITY,
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
}
