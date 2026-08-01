<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use Exception;
use Illuminate\Http\Response;

class NotFoundException extends Exception
{
    /**
     * Create a new NotFoundException.
     */
    public function __construct(
        string $message = 'Resource not found.',
        int $code = Response::HTTP_NOT_FOUND,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
