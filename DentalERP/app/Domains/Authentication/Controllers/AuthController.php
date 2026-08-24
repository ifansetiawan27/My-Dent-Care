<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Controllers;

use App\Core\Base\BaseController;
use App\Core\Exceptions\BusinessException;
use App\Core\Support\ApiResponse;
use App\Domains\Authentication\DTO\LoginDTO;
use App\Domains\Authentication\Interfaces\AuthServiceInterface;
use App\Domains\Authentication\Interfaces\TokenServiceInterface;
use App\Domains\Authentication\Requests\ChangePasswordRequest;
use App\Domains\Authentication\Requests\DeviceListRequest;
use App\Domains\Authentication\Requests\ForgotPasswordRequest;
use App\Domains\Authentication\Requests\LoginHistoryRequest;
use App\Domains\Authentication\Requests\LoginRequest;
use App\Domains\Authentication\Requests\RefreshTokenRequest;
use App\Domains\Authentication\Requests\ResetPasswordRequest;
use App\Domains\Authentication\Requests\UpdateProfileRequest;
use App\Domains\Authentication\Resources\DeviceResource;
use App\Domains\Authentication\Resources\LoginHistoryResource;
use App\Domains\Authentication\Resources\LoginResource;
use App\Domains\Authentication\Resources\ProfileResource;
use App\Domains\Authentication\Resources\TokenPairResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AuthController extends BaseController
{
    public function __construct(
        private readonly AuthServiceInterface $service,
        private readonly TokenServiceInterface $tokenService,
    ) {}

    public function lookup(Request $request): JsonResponse
    {
        $identifier = $request->input('identifier');
        if (empty($identifier)) {
            return ApiResponse::error(message: 'Identifier is required.', code: 422);
        }

        $user = \App\Domains\User\Models\User::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if (! $user) {
            return ApiResponse::error(message: 'User not found.', code: 404);
        }

        $org    = $user->organization;
        $branch = $user->branch;

        if (! $org || ! $branch) {
            return ApiResponse::error(message: 'Organization or branch not found.', code: 404);
        }

        return ApiResponse::success(data: [
            'organization_id' => $org->id,
            'branch_id'       => $branch->id,
            'organization'    => $org->company_name,
            'branch'          => $branch->branch_name,
        ], message: 'Lookup successful.');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $dto = new LoginDTO(
                identifier:     $request->validated('identifier'),
                password:       $request->validated('password'),
                organizationId: $request->validated('organization_id'),
                branchId:       $request->validated('branch_id'),
                deviceUuid:     $request->validated('device_uuid'),
                deviceName:     $request->validated('device_name'),
                deviceType:     $request->validated('device_type'),
                platform:       $request->validated('platform'),
            );

            $result = $this->service->login($dto);

            return ApiResponse::success(data: new LoginResource((object) $result), message: 'Login successful.');
        } catch (BusinessException $e) {
            $message = $e->getMessage();
            $status  = match (true) {
                str_contains($message, 'locked')    => 423,
                str_contains($message, 'inactive')  => 403,
                str_contains($message, 'tenant')    => 403,
                str_contains($message, 'branch')    => 403,
                str_contains($message, 'credentials') => 401,
                default => 401,
            };

            return ApiResponse::error(message: $message, code: $status);
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }

    public function logout(): JsonResponse
    {
        try {
            $this->service->logout();

            return ApiResponse::success(data: null, message: 'Logout successful.');
        } catch (BusinessException $e) {
            return ApiResponse::error(message: $e->getMessage(), code: 401);
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }

    public function logoutAll(): JsonResponse
    {
        try {
            $this->service->logoutAll();

            return ApiResponse::success(data: null, message: 'All sessions revoked successfully.');
        } catch (BusinessException $e) {
            return ApiResponse::error(message: $e->getMessage(), code: 401);
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        try {
            $tokenPair = $this->tokenService->refresh($request->validated('refresh_token'));

            return ApiResponse::success(data: new TokenPairResource($tokenPair), message: 'Token refreshed successfully.');
        } catch (BusinessException $e) {
            $message = $e->getMessage();
            $status  = str_contains($message, 'reuse') ? 409 : 401;

            return ApiResponse::error(message: $message, code: $status);
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $this->service->forgotPassword($request->validated('email'));

            return ApiResponse::success(data: null, message: 'If the email is registered, password reset instructions will be sent.');
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $this->service->resetPassword($request->validated('email'), $request->validated('token'), $request->validated('password'));

            return ApiResponse::success(data: null, message: 'Password reset successfully. Please log in again.');
        } catch (BusinessException $e) {
            return ApiResponse::error(message: $e->getMessage(), code: 400);
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $this->service->changePassword($request->validated('current_password'), $request->validated('password'));

            return ApiResponse::success(data: [
                'current_session_active'    => true,
                'other_sessions_revoked'    => true,
                'registered_devices_retained' => true,
            ], message: 'Password changed successfully.', status: 200);
        } catch (BusinessException $e) {
            return ApiResponse::error(message: $e->getMessage(), code: 401);
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }

    public function profile(): JsonResponse
    {
        try {
            $result = $this->service->getProfile();

            return ApiResponse::success(data: new ProfileResource($result), message: 'Profile retrieved successfully.', status: 200);
        } catch (BusinessException $e) {
            return ApiResponse::error(message: $e->getMessage(), code: 401);
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $this->service->updateProfile($request->validated());

            $result = $this->service->getProfile();

            return ApiResponse::success(data: new ProfileResource($result), message: 'Profile updated successfully.', status: 200);
        } catch (BusinessException $e) {
            return ApiResponse::error(message: $e->getMessage(), status: 403);
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }

    public function loginHistory(LoginHistoryRequest $request): JsonResponse
    {
        try {
            $paginator = $this->service->getLoginHistory($request->validated());

            return ApiResponse::paginate(
                paginator: $paginator,
                data:      LoginHistoryResource::collection($paginator),
                message:   'Login history retrieved successfully.',
            );
        } catch (BusinessException $e) {
            return ApiResponse::error(message: $e->getMessage(), code: 401);
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }

    public function devices(DeviceListRequest $request): JsonResponse
    {
        try {
            $paginator = $this->service->getDevices($request->validated());

            return ApiResponse::paginate(
                paginator: $paginator,
                data:      DeviceResource::collection($paginator),
                message:   'Devices retrieved successfully.',
            );
        } catch (BusinessException $e) {
            return ApiResponse::error(message: $e->getMessage(), code: 401);
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }

    public function revokeDevice(string $deviceId): JsonResponse
    {
        try {
            $this->service->revokeDevice($deviceId);

            return ApiResponse::success(data: null, message: 'Device revoked successfully.', status: 200);
        } catch (BusinessException $e) {
            $message = $e->getMessage();
            $status  = match (true) {
                str_contains($message, 'Device not found') => 404,
                str_contains($message, 'current device')  => 409,
                default => 403,
            };

            return ApiResponse::error(message: $message, code: $status);
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }
}
