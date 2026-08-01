<?php

declare(strict_types=1);

namespace App\Core\Base;

use App\Core\Contracts\RepositoryInterface;
use App\Core\Contracts\ServiceInterface;
use App\Core\Exceptions\ApiException;
use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class BaseService implements ServiceInterface
{
    /**
     * The repository instance.
     */
    protected RepositoryInterface $repository;

    /**
     * Service name used in log context.
     * Defaults to the child class short name.
     */
    protected string $serviceName;

    /**
     * Inject the repository via constructor.
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository  = $repository;
        $this->serviceName = class_basename(static::class);
    }

    // -------------------------------------------------------------------------
    // ServiceInterface implementation
    // -------------------------------------------------------------------------

    /**
     * Paginate records with optional search, filter, and sort.
     *
     * Accepted $params keys:
     *   per_page  int     default 15
     *   search    string  optional
     *   sort_by   string  default created_at
     *   sort_dir  string  asc|desc  default desc
     *   filters   array   key-value pairs
     *
     * @param  array<string, mixed> $params
     */
    public function paginate(array $params = []): LengthAwarePaginator
    {
        try {
            return $this->repository->paginate(
                perPage: (int) ($params['per_page'] ?? 15),
                filters: (array)  ($params['filters']  ?? []),
                search:  $params['search']   ?? null,
                sortBy:  (string) ($params['sort_by']  ?? 'created_at'),
                sortDir: (string) ($params['sort_dir'] ?? 'desc'),
            );
        } catch (Throwable $e) {
            $this->logError('paginate', $e);
            throw $this->wrapException($e);
        }
    }

    /**
     * Find a record by primary key or throw NotFoundException.
     *
     * @throws NotFoundException
     */
    public function getById(string $id): Model
    {
        try {
            return $this->repository->findOrFail($id);
        } catch (NotFoundException $e) {
            $this->logWarning('getById', $e->getMessage(), ['id' => $id]);
            throw $e;
        } catch (Throwable $e) {
            $this->logError('getById', $e, ['id' => $id]);
            throw $this->wrapException($e);
        }
    }

    /**
     * Create a new record inside a database transaction.
     *
     * @param  array<string, mixed> $data
     * @throws ApiException|BusinessException
     */
    public function create(array $data): Model
    {
        try {
            return DB::transaction(function () use ($data): Model {
                $record = $this->repository->create($data);

                $this->logInfo('create', 'Record created.', [
                    'id' => $record->getKey(),
                ]);

                return $record;
            });
        } catch (BusinessException $e) {
            $this->logWarning('create', $e->getMessage(), $e->getContext());
            throw $e;
        } catch (Throwable $e) {
            $this->logError('create', $e, ['data_keys' => array_keys($data)]);
            throw $this->wrapException($e);
        }
    }

    /**
     * Update an existing record inside a database transaction.
     *
     * @param  array<string, mixed> $data
     * @throws NotFoundException|ApiException|BusinessException
     */
    public function update(string $id, array $data): Model
    {
        try {
            return DB::transaction(function () use ($id, $data): Model {
                $record = $this->repository->update($id, $data);

                $this->logInfo('update', 'Record updated.', ['id' => $id]);

                return $record;
            });
        } catch (NotFoundException | BusinessException $e) {
            $this->logWarning('update', $e->getMessage(), ['id' => $id]);
            throw $e;
        } catch (Throwable $e) {
            $this->logError('update', $e, ['id' => $id]);
            throw $this->wrapException($e);
        }
    }

    /**
     * Soft delete a record inside a database transaction.
     *
     * @throws NotFoundException|ApiException
     */
    public function delete(string $id): bool
    {
        try {
            return DB::transaction(function () use ($id): bool {
                $result = $this->repository->delete($id);

                $this->logInfo('delete', 'Record deleted.', ['id' => $id]);

                return $result;
            });
        } catch (NotFoundException $e) {
            $this->logWarning('delete', $e->getMessage(), ['id' => $id]);
            throw $e;
        } catch (Throwable $e) {
            $this->logError('delete', $e, ['id' => $id]);
            throw $this->wrapException($e);
        }
    }

    // -------------------------------------------------------------------------
    // Transaction helper
    // -------------------------------------------------------------------------

    /**
     * Execute a callable inside a database transaction.
     * Use this in child services for complex multi-step operations.
     *
     * @template T
     * @param  callable(): T $callback
     * @return T
     */
    protected function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    // -------------------------------------------------------------------------
    // Logging helpers
    // -------------------------------------------------------------------------

    /**
     * Log an informational message with context.
     *
     * @param  array<string, mixed> $context
     */
    protected function logInfo(string $action, string $message, array $context = []): void
    {
        Log::info("[{$this->serviceName}::{$action}] {$message}", $this->buildContext($context));
    }

    /**
     * Log a warning message with context.
     *
     * @param  array<string, mixed> $context
     */
    protected function logWarning(string $action, string $message, array $context = []): void
    {
        Log::warning("[{$this->serviceName}::{$action}] {$message}", $this->buildContext($context));
    }

    /**
     * Log an error with full exception details.
     *
     * @param  array<string, mixed> $context
     */
    protected function logError(string $action, Throwable $e, array $context = []): void
    {
        Log::error("[{$this->serviceName}::{$action}] {$e->getMessage()}", $this->buildContext([
            ...$context,
            'exception' => $e::class,
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
        ]));
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Build a consistent log context array.
     *
     * @param  array<string, mixed> $extra
     * @return array<string, mixed>
     */
    private function buildContext(array $extra = []): array
    {
        return [
            'service' => $this->serviceName,
            ...$extra,
        ];
    }

    /**
     * Wrap an unknown Throwable into an ApiException.
     * Domain exceptions (NotFoundException, BusinessException) are never wrapped.
     */
    private function wrapException(Throwable $e): ApiException|NotFoundException|BusinessException
    {
        if ($e instanceof NotFoundException || $e instanceof BusinessException) {
            return $e; // @phpstan-ignore-line
        }

        return new ApiException(
            message:  'An unexpected error occurred.',
            previous: $e,
        );
    }
}
