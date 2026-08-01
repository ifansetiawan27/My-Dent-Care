<?php

declare(strict_types=1);

namespace App\Domains\Branch\Requests\Concerns;

/**
 * HasBranchValidationRules
 *
 * Shared validation rule helpers for Branch FormRequests.
 * Provides a single source of truth for branch_type allowed values.
 */
trait HasBranchValidationRules
{
    /**
     * Allowed branch type values.
     *
     * @return array<string>
     */
    protected function branchTypes(): array
    {
        return ['clinic', 'mobile', 'hospital'];
    }
}
