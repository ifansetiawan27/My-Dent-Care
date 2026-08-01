<?php

declare(strict_types=1);

namespace App\Core\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

/**
 * Standard API response helper.
 *
 * All responses follow the envelope:
 * {
 *   "success": bool,
 *   "message": string,
 *   "data":    mixed|null,
 *   "errors":  object|null,
 *   "meta":    object|null
 * }
 */
final class ApiResponse
{
    // -------------------------------------------------------------------------
    // Success responses
    // -------------------------------------------------------------------------

    /**
     * 200 OK — general success with optional data.
     */
    public static function success(
        mixed  $data    = null,
        string $message = 'Success.',
        int    $code    = Response::HTTP_OK,
    ): JsonResponse {
        return ResponseBuilder::make()
            ->success()
            ->message($message)
            ->data($data)
            ->statusCode($code)
            ->build();
    }

    /**
     * 201 Created — resource created successfully.
     */
    public static function created(
        mixed  $data    = null,
        string $message = 'Resource created successfully.',
    ): JsonResponse {
        return self::success($data, $message, Response::HTTP_CREATED);
    }

    /**
     * 200 OK — paginated resource list.
     *
     * Attaches pagination meta automatically from LengthAwarePaginator.
     *
     * @param  LengthAwarePaginator                        $paginator
     * @param  ResourceCollection|JsonResource|array|null  $data      Pass null to use raw paginator items.
     * @param  string                                      $message
     * @param  array<string, mixed>                        $extraMeta Additional meta to merge.
     */
    public static function paginate(
        LengthAwarePaginator               $paginator,
        ResourceCollection|JsonResource|array|null $data      = null,
        string                             $message   = 'Data retrieved successfully.',
        array                              $extraMeta = [],
    ): JsonResponse {
        $payload = $data ?? $paginator->items();

        $meta = [
            'pagination' => [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
            ...$extraMeta,
        ];

        return ResponseBuilder::make()
            ->success()
            ->message($message)
            ->data($payload)
            ->meta($meta)
            ->statusCode(Response::HTTP_OK)
            ->build();
    }

    // -------------------------------------------------------------------------
    // Error responses
    // -------------------------------------------------------------------------

    /**
     * Generic error response.
     *
     * @param  array<string, mixed>|null $errors
     */
    public static function error(
        string $message = 'An error occurred.',
        int    $code    = Response::HTTP_BAD_REQUEST,
        ?array $errors  = null,
    ): JsonResponse {
        return ResponseBuilder::make()
            ->failure()
            ->message($message)
            ->errors($errors ?? [])
            ->statusCode($code)
            ->build();
    }

    /**
     * 422 Unprocessable Entity — validation errors.
     *
     * @param  array<string, mixed> $errors  Field-level error messages.
     */
    public static function validationError(
        array  $errors  = [],
        string $message = 'The given data was invalid.',
    ): JsonResponse {
        return ResponseBuilder::make()
            ->failure()
            ->message($message)
            ->errors($errors)
            ->statusCode(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->build();
    }

    /**
     * 401 Unauthorized — missing or invalid authentication.
     */
    public static function unauthorized(
        string $message = 'Unauthenticated.',
    ): JsonResponse {
        return ResponseBuilder::make()
            ->failure()
            ->message($message)
            ->statusCode(Response::HTTP_UNAUTHORIZED)
            ->build();
    }

    /**
     * 403 Forbidden — authenticated but not authorized.
     */
    public static function forbidden(
        string $message = 'This action is unauthorized.',
    ): JsonResponse {
        return ResponseBuilder::make()
            ->failure()
            ->message($message)
            ->statusCode(Response::HTTP_FORBIDDEN)
            ->build();
    }

    /**
     * 404 Not Found — resource does not exist.
     */
    public static function notFound(
        string $message = 'Resource not found.',
    ): JsonResponse {
        return ResponseBuilder::make()
            ->failure()
            ->message($message)
            ->statusCode(Response::HTTP_NOT_FOUND)
            ->build();
    }

    /**
     * 500 Internal Server Error — unexpected server failure.
     */
    public static function serverError(
        string $message = 'Internal server error.',
    ): JsonResponse {
        return ResponseBuilder::make()
            ->failure()
            ->message($message)
            ->statusCode(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->build();
    }

    // -------------------------------------------------------------------------
    // Fluent builder access
    // -------------------------------------------------------------------------

    /**
     * Return a fresh ResponseBuilder for custom responses.
     */
    public static function builder(): ResponseBuilder
    {
        return ResponseBuilder::make();
    }
}
