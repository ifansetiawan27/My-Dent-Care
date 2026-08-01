<?php

declare(strict_types=1);

namespace App\Domains\Branch\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Branch\DTO\CreateBranchDTO;
use App\Domains\Branch\DTO\UpdateBranchDTO;
use App\Domains\Branch\Interfaces\BranchRepositoryInterface;
use App\Domains\Branch\Interfaces\BranchServiceInterface;
use App\Domains\Branch\Models\Branch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * BranchService
 *
 * Implements all business operations for the Branch domain.
 * Enforces business rules, wraps writes in transactions, and
 * delegates all data access to BranchRepositoryInterface.
 *
 * Layer rule: No direct DB queries. All data access via repository.
 */
class BranchService implements BranchServiceInterface
{
    /**
     * Service name used in structured log messages.
     */
    private const SERVICE_NAME = 'BranchService';

    /**
     * Inject the Branch repository interface.
     */
    public function __construct(
        private readonly BranchRepositoryInterface $repository,
    ) {}

    // -------------------------------------------------------------------------
    // Read Operations
    // -------------------------------------------------------------------------

    /**
     * Get all branches for a given organization.
     *
     * @param  string             $organizationId
     * @return Collection<int, Branch>
     */
    public function getAll(string $organizationId): Collection
    {
        return $this->getByOrganization($organizationId);
    }

    /**
     * Get a paginated list of branches for a given organization.
     *
     * @param  string               $organizationId
     * @param  array<string, mixed> $params
     */
    public function getPaginated(
        string $organizationId,
        array  $params = [],
    ): LengthAwarePaginator {
        try {
            $filters = array_merge(
                (array) ($params['filters'] ?? []),
                ['organization_id' => $organizationId],
            );

            return $this->repository->paginate(
                perPage: (int) ($params['per_page'] ?? 15),
                filters: $filters,
                search:  $params['search'] ?? null,
                sortBy:  (string) ($params['sort_by']  ?? 'branch_name'),
                sortDir: (string) ($params['sort_dir'] ?? 'asc'),
            );
        } catch (Throwable $e) {
            $this->logError('getPaginated', $e, ['organization_id' => $organizationId]);
            throw $e;
        }
    }

    /**
     * Find a branch by primary key.
     * Returns null when not found.
     */
    public function getById(string $id): ?Branch
    {
        try {
            return $this->repository->findById($id);
        } catch (Throwable $e) {
            $this->logError('getById', $e, ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Find a branch by UUID or throw NotFoundException.
     *
     * @throws NotFoundException
     */
    public function getByUuid(string $uuid): Branch
    {
        try {
            return $this->repository->findByUuid($uuid);
        } catch (NotFoundException $e) {
            $this->logWarning('getByUuid', $e->getMessage(), ['uuid' => $uuid]);
            throw $e;
        } catch (Throwable $e) {
            $this->logError('getByUuid', $e, ['uuid' => $uuid]);
            throw $e;
        }
    }

    /**
     * Get all branches belonging to a specific organization.
     *
     * @param  string             $organizationId
     * @return Collection<int, Branch>
     */
    public function getByOrganization(string $organizationId): Collection
    {
        try {
            return $this->repository->findByOrganization($organizationId);
        } catch (Throwable $e) {
            $this->logError('getByOrganization', $e, ['organization_id' => $organizationId]);
            throw $e;
        }
    }

    /**
     * Search branches by keyword within a specific organization.
     */
    public function search(
        string $organizationId,
        string $keyword,
        int    $perPage = 15,
    ): LengthAwarePaginator {
        try {
            return $this->repository->search($organizationId, $keyword, $perPage);
        } catch (Throwable $e) {
            $this->logError('search', $e, [
                'organization_id' => $organizationId,
                'keyword'         => $keyword,
            ]);
            throw $e;
        }
    }

    // -------------------------------------------------------------------------
    // Write Operations
    // -------------------------------------------------------------------------

    /**
     * Create a new branch inside a database transaction.
     *
     * Business Rules:
     * - branch_code must be unique within the organization.
     *
     * @throws BusinessException  When branch_code already exists in the organization.
     * @throws NotFoundException  When the organization does not exist.
     */
    public function create(CreateBranchDTO $dto): Branch
    {
        try {
            return DB::transaction(function () use ($dto): Branch {

                // Rule: branch_code must be unique within the organization
                if ($this->isBranchCodeTaken($dto->organizationId, $dto->branchCode)) {
                    throw new BusinessException(
                        "Branch code [{$dto->branchCode}] already exists in this organization.",
                        context: [
                            'organization_id' => $dto->organizationId,
                            'branch_code'     => $dto->branchCode,
                        ],
                    );
                }

                $branch = $this->repository->create($dto->toArray());

                $this->logInfo('create', 'Branch created.', [
                    'id'              => $branch->id,
                    'branch_code'     => $branch->branch_code,
                    'organization_id' => $branch->organization_id,
                ]);

                return $branch;
            });
        } catch (BusinessException | NotFoundException $e) {
            $this->logWarning('create', $e->getMessage(), [
                'organization_id' => $dto->organizationId,
                'branch_code'     => $dto->branchCode,
            ]);
            throw $e;
        } catch (Throwable $e) {
            $this->logError('create', $e, [
                'organization_id' => $dto->organizationId,
                'branch_code'     => $dto->branchCode,
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing branch inside a database transaction.
     *
     * Business Rules:
     * - branch_code must remain unique within the organization when changed.
     *
     * @throws NotFoundException  When the branch does not exist.
     * @throws BusinessException  When updated branch_code conflicts with existing one.
     */
    public function update(string $id, UpdateBranchDTO $dto): Branch
    {
        try {
            return DB::transaction(function () use ($id, $dto): Branch {
                $branch = $this->repository->findByUuid($id);

                // Rule: branch_code must be unique within org when changed
                if (
                    $dto->branchCode !== null
                    && $dto->branchCode !== $branch->branch_code
                    && $this->isBranchCodeTaken($branch->organization_id, $dto->branchCode, $id)
                ) {
                    throw new BusinessException(
                        "Branch code [{$dto->branchCode}] already exists in this organization.",
                        context: [
                            'branch_id'       => $id,
                            'organization_id' => $branch->organization_id,
                            'branch_code'     => $dto->branchCode,
                        ],
                    );
                }

                $updated = $this->repository->update($id, $dto->toArray());

                $this->logInfo('update', 'Branch updated.', [
                    'id'          => $id,
                    'branch_code' => $updated->branch_code,
                ]);

                return $updated;
            });
        } catch (NotFoundException | BusinessException $e) {
            $this->logWarning('update', $e->getMessage(), ['id' => $id]);
            throw $e;
        } catch (Throwable $e) {
            $this->logError('update', $e, ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Soft delete a branch inside a database transaction.
     *
     * Business Rules (Delete Guards):
     * - Cannot delete if branch has active Users.
     * - Cannot delete if branch has active Patients.
     * - Cannot delete if branch has active Appointments.
     * - Cannot delete if branch has Inventory records.
     * - Cannot delete if branch has Finance Transactions.
     *
     * @throws NotFoundException  When the branch does not exist.
     * @throws BusinessException  When any delete guard is not satisfied.
     */
    public function delete(string $id): bool
    {
        try {
            return DB::transaction(function () use ($id): bool {
                // Ensure branch exists before checking guards
                $this->repository->findByUuid($id);

                // Enforce all delete guards
                $this->assertDeletable($id);

                $result = $this->repository->delete($id);

                $this->logInfo('delete', 'Branch deleted.', ['id' => $id]);

                return $result;
            });
        } catch (NotFoundException | BusinessException $e) {
            $this->logWarning('delete', $e->getMessage(), ['id' => $id]);
            throw $e;
        } catch (Throwable $e) {
            $this->logError('delete', $e, ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Restore a soft-deleted branch inside a database transaction.
     *
     * @throws NotFoundException  When the branch does not exist.
     */
    public function restore(string $id): bool
    {
        try {
            return DB::transaction(function () use ($id): bool {
                $result = $this->repository->restore($id);

                $this->logInfo('restore', 'Branch restored.', ['id' => $id]);

                return $result;
            });
        } catch (NotFoundException $e) {
            $this->logWarning('restore', $e->getMessage(), ['id' => $id]);
            throw $e;
        } catch (Throwable $e) {
            $this->logError('restore', $e, ['id' => $id]);
            throw $e;
        }
    }

    // -------------------------------------------------------------------------
    // Private — Business Rule Helpers
    // -------------------------------------------------------------------------

    /**
     * Check whether a branch_code is already taken within an organization.
     * Optionally excludes a branch ID to allow self-comparison during update.
     *
     * Uses in-memory filtering on findByOrganization() result to avoid
     * adding a new method to the repository interface.
     */
    private function isBranchCodeTaken(
        string  $organizationId,
        string  $branchCode,
        ?string $excludeId = null,
    ): bool {
        $existing = $this->repository
            ->findByOrganization($organizationId)
            ->first(fn (Branch $branch): bool =>
                $branch->branch_code === $branchCode
                && ($excludeId === null || $branch->id !== $excludeId)
            );

        return $existing !== null;
    }

    /**
     * Assert that a branch satisfies all delete guard rules.
     * Throws BusinessException if any guard fails.
     *
     * @throws BusinessException
     */
    private function assertDeletable(string $branchId): void
    {
        $guards = [
            'Users'                => fn (): bool => $this->repository->hasUsers($branchId),
            'Patients'             => fn (): bool => $this->repository->hasPatients($branchId),
            'Appointments'         => fn (): bool => $this->repository->hasAppointments($branchId),
            'Inventory'            => fn (): bool => $this->repository->hasInventories($branchId),
            'Finance Transactions' => fn (): bool => $this->repository->hasFinanceTransactions($branchId),
        ];

        foreach ($guards as $label => $check) {
            if ($check()) {
                throw new BusinessException(
                    "Cannot delete branch. It still has {$label}.",
                    context: ['branch_id' => $branchId, 'guard' => $label],
                );
            }
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
            '[' . self::SERVICE_NAME . '::' . $action . '] ' . $message,
            ['service' => self::SERVICE_NAME, ...$context],
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
            '[' . self::SERVICE_NAME . '::' . $action . '] ' . $message,
            ['service' => self::SERVICE_NAME, ...$context],
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
            '[' . self::SERVICE_NAME . '::' . $action . '] ' . $e->getMessage(),
            [
                'service'   => self::SERVICE_NAME,
                'exception' => $e::class,
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                ...$context,
            ],
        );
    }
}
