<?php

declare(strict_types=1);

namespace App\Domains\Branch\Controllers;

use App\Core\Base\BaseController;
use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Core\Support\ApiResponse;
use App\Domains\Branch\Interfaces\BranchServiceInterface;
use App\Domains\Branch\Requests\StoreBranchRequest;
use App\Domains\Branch\Requests\UpdateBranchRequest;
use App\Domains\Branch\Resources\BranchResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * BranchController
 *
 * Handles REST API endpoints for the Branch domain.
 * Delegates all business logic to BranchServiceInterface.
 * Returns standardized JSON responses via ApiResponse.
 *
 * Layer rule: No business logic. No direct DB queries.
 */
class BranchController extends BaseController
{
    /**
     * Inject the Branch service interface.
     */
    public function __construct(
        private readonly BranchServiceInterface $service,
    ) {}

    // -------------------------------------------------------------------------
    // GET /api/v1/branches?organization_id=&search=&per_page=&sort_by=&sort_dir=
    // -------------------------------------------------------------------------

    /**
     * List branches paginated, scoped to an organization.
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $organizationId = (string) $request->input('organization_id', '');

            $paginator = $this->service->getPaginated(
                organizationId: $organizationId,
                params: [
                    'per_page' => $request->integer('per_page', 15),
                    'search'   => $request->string('search')->toString() ?: null,
                    'sort_by'  => $request->string('sort_by', 'branch_name')->toString(),
                    'sort_dir' => $request->string('sort_dir', 'asc')->toString(),
                    'filters'  => $request->only(['branch_type', 'city', 'status']),
                ],
            );

            return ApiResponse::paginate(
                paginator: $paginator,
                data:      BranchResource::collection($paginator),
                message:   'Branches retrieved successfully.',
            );
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // GET /api/v1/branches/{id}
    // -------------------------------------------------------------------------

    /**
     * Show a single branch by UUID.
     *
     * @param  string       $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $branch = $this->service->getByUuid($id);
            $branch->load('organization');

            return ApiResponse::success(
                data:    new BranchResource($branch),
                message: 'Branch retrieved successfully.',
            );
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // POST /api/v1/branches
    // -------------------------------------------------------------------------

    /**
     * Create a new branch.
     *
     * @param  StoreBranchRequest $request
     * @return JsonResponse
     */
    public function store(StoreBranchRequest $request): JsonResponse
    {
        try {
            $branch = $this->service->create($request->toDTO());

            return ApiResponse::created(
                data:    new BranchResource($branch),
                message: 'Branch created successfully.',
            );
        } catch (BusinessException $e) {
            return ApiResponse::error(
                message: $e->getMessage(),
                code:    $e->getCode(),
            );
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // PUT /api/v1/branches/{id}
    // -------------------------------------------------------------------------

    /**
     * Update an existing branch.
     *
     * @param  UpdateBranchRequest $request
     * @param  string              $id
     * @return JsonResponse
     */
    public function update(UpdateBranchRequest $request, string $id): JsonResponse
    {
        try {
            $branch = $this->service->update($id, $request->toDTO());

            return ApiResponse::success(
                data:    new BranchResource($branch),
                message: 'Branch updated successfully.',
            );
        } catch (BusinessException $e) {
            return ApiResponse::error(
                message: $e->getMessage(),
                code:    $e->getCode(),
            );
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // DELETE /api/v1/branches/{id}
    // -------------------------------------------------------------------------

    /**
     * Soft delete a branch.
     * Enforces delete guards via Service layer.
     *
     * @param  string       $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return ApiResponse::success(
                message: 'Branch deleted successfully.',
            );
        } catch (BusinessException $e) {
            return ApiResponse::error(
                message: $e->getMessage(),
                code:    $e->getCode(),
            );
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // POST /api/v1/branches/{id}/restore
    // -------------------------------------------------------------------------

    /**
     * Restore a soft-deleted branch.
     *
     * @param  string       $id
     * @return JsonResponse
     */
    public function restore(string $id): JsonResponse
    {
        try {
            $this->service->restore($id);

            return ApiResponse::success(
                message: 'Branch restored successfully.',
            );
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }
}
