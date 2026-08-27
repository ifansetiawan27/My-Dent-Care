<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Policies;

use App\Domains\Radiology\Models\RadiologyReport;
use App\Domains\User\Models\User;

final class RadiologyReportPolicy
{
    public function viewAny(User $u): bool
    {
        return true;
    }

    public function view(User $u, RadiologyReport $report): bool
    {
        return $u->organization_id === $report->radiologyOrder?->organization_id;
    }

    public function update(User $u, RadiologyReport $report): bool
    {
        return $u->organization_id === $report->radiologyOrder?->organization_id;
    }

    public function delete(User $u, RadiologyReport $report): bool
    {
        return $u->organization_id === $report->radiologyOrder?->organization_id;
    }
}
