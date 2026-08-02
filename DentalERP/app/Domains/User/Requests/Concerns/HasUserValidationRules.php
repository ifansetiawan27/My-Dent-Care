<?php

declare(strict_types=1);

namespace App\Domains\User\Requests\Concerns;

use App\Domains\User\Enums\UserGender;
use App\Domains\User\Enums\UserStatus;

/**
 * HasUserValidationRules
 *
 * Shared validation rule helpers for User FormRequests.
 * Provides single source of truth for allowed enum values.
 * Used by StoreUserRequest and UpdateUserRequest.
 */
trait HasUserValidationRules
{
    /**
     * Allowed gender values from UserGender Enum.
     *
     * @return array<string>
     */
    protected function genderValues(): array
    {
        return UserGender::values();
    }

    /**
     * Allowed status values from UserStatus Enum.
     *
     * @return array<string>
     */
    protected function statusValues(): array
    {
        return UserStatus::values();
    }
}
