<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Services;

use App\Core\Exceptions\NotFoundException;
use App\Domains\MasterData\DTO\MasterDataFilterDTO;
use App\Domains\MasterData\Interfaces\MasterDataRepositoryInterface;
use App\Domains\MasterData\Interfaces\MasterDataServiceInterface;
use App\Domains\MasterData\Models\BaseMasterDataModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * BaseMasterDataService
 *
 * Generic business operations for all Master Data reference tables.
 * Wraps writes in transactions, enforces business rules, and logs activity.
 *
 * Usage — extend for each reference table:
 *
 *   class CountryService extends BaseMasterDataService
 *   {
 *       public function __construct(CountryRepository $repository)
 *       {
 *           parent::__construct($repository);
 *           $this->serviceName = 'CountryService';
 *       }
 *   }
 *
 * Layer rule: No direct DB queries. All data access via repository.
 */
abstract class BaseMasterDataService implements MasterDataServiceInterface
{
    /**
     * Service name for structured log messages.
     * Override in child services: $this->serviceName = 'CountryService';
     */
    protected string $serviceName = 'MasterDataService';

    /**
     * Inject the Master Data repository interface.
     */
    public function __construct(
        protected readonly MasterDataRepositoryInterface $repository,
    ) {}

    // -------------------------------------------------------------------------
    // Read Operations
    // -------------------------------------------------------------------------

    /**
     * Get all records, optionally filtered by active status.
     *
     * @return Collection<int, BaseMasterDataModel>
     */
    public function getAll(bool $activeOnly = true): Collection
    {
        try {
            return $this->repository->findAll($activeOnly);
        } catch (Throwable $e) {
            $this->logError('getAll', $e);
            throw $e;
        }
    }

    /**
     * Get active records for dropdown / select lists.
     * Returns minimal fields: id, code, name.
     *
     * @return Collection<int, BaseMasterDataModel>
     */
    public function getForDropdown(): Collection
    {
        try {
            return $this->repository->findActive(['id', 'code', 'name']);
        } catch (Throwable $e) {
            $this->logError('getForDropdown', $e);
            throw $e;
        }
    }

    /**
     * Find a record by primary key or throw NotFoundException.
     *
     * @throws NotFoundException
     */
    public function findById(string $id): BaseMasterDataModel
    {
        try {
            $record = $this->repository->findById($id);

            if ($record === null) {
                throw new NotFoundException("Master data with ID [{$id}] not found.");
            }

            return $record;
        } catch (NotFoundException $e) {
            $this->logWarning('findById', $e->getMessage(), ['id' => $id]);
            throw $e;
        } catch (Throwable $e) {
            $this->logError('findById', $e, ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Find a record by its unique code.
     * Returns null when not found.
     */
    public function findByCode(string $code): ?BaseMasterDataModel
    {
        try {
            return $this->repository->findByCode($code);
        } catch (Throwable $e) {
            $this->logError('findByCode', $e, ['code' => $code]);
            throw $e;
        }
    }

    /**
     * Search records by keyword. Always returns active records only.
     *
     * @return Collection<int, BaseMasterDataModel>
     */
    public function search(string $keyword): Collection
    {
        try {
            return $this->repository->search($keyword, activeOnly: true);
        } catch (Throwable $e) {
            $this->logError('search', $e, ['keyword' => $keyword]);
            throw $e;
        }
    }

    /**
     * Get a paginated list with optional search, active filter, and sort.
     */
    public function paginate(MasterDataFilterDTO $filter): LengthAwarePaginator
    {
        try {
            return $this->repository->paginate(
                perPage:    $filter->perPage,
                search:     $filter->search,
                activeOnly: $filter->activeOnly,
                sortBy:     $filter->sortBy,
                sortDir:    $filter->sortDir,
            );
        } catch (Throwable $e) {
            $this->logError('paginate', $e);
            throw $e;
        }
    }

    // -------------------------------------------------------------------------
    // Write Operations
    // -------------------------------------------------------------------------

    /**
     * Activate a Master Data record inside a database transaction.
     *
     * @throws NotFoundException
     */
    public function activate(string $id): bool
    {
        try {
            return DB::transaction(function () use ($id): bool {
                $result = $this->repository->activate($id);

                $this->logInfo('activate', 'Record activated.', ['id' => $id]);

                return $result;
            });
        } catch (NotFoundException $e) {
            $this->logWarning('activate', $e->getMessage(), ['id' => $id]);
            throw $e;
        } catch (Throwable $e) {
            $this->logError('activate', $e, ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Deactivate a Master Data record inside a database transaction.
     *
     * @throws NotFoundException
     */
    public function deactivate(string $id): bool
    {
        try {
            return DB::transaction(function () use ($id): bool {
                $result = $this->repository->deactivate($id);

                $this->logInfo('deactivate', 'Record deactivated.', ['id' => $id]);

                return $result;
            });
        } catch (NotFoundException $e) {
            $this->logWarning('deactivate', $e->getMessage(), ['id' => $id]);
            throw $e;
        } catch (Throwable $e) {
            $this->logError('deactivate', $e, ['id' => $id]);
            throw $e;
        }
    }

    // -------------------------------------------------------------------------
    // Private — Logging Helpers
    // -------------------------------------------------------------------------

    /**
     * Log an informational message with structured context.
     *
     * @param  array<string, mixed> $context
     */
    private function logInfo(string $action, string $message, array $context = []): void
    {
        Log::info(
            "[{$this->serviceName}::{$action}] {$message}",
            ['service' => $this->serviceName, ...$context],
        );
    }

    /**
     * Log a warning message with structured context.
     *
     * @param  array<string, mixed> $context
     */
    private function logWarning(string $action, string $message, array $context = []): void
    {
        Log::warning(
            "[{$this->serviceName}::{$action}] {$message}",
            ['service' => $this->serviceName, ...$context],
        );
    }

    /**
     * Log an error with full exception details.
     *
     * @param  array<string, mixed> $context
     */
    private function logError(string $action, Throwable $e, array $context = []): void
    {
        Log::error(
            "[{$this->serviceName}::{$action}] {$e->getMessage()}",
            [
                'service'   => $this->serviceName,
                'exception' => $e::class,
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                ...$context,
            ],
        );
    }
}
