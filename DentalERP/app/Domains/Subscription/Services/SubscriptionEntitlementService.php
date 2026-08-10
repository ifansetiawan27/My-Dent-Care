<?php
declare(strict_types=1);
namespace App\Domains\Subscription\Services;
use App\Domains\Subscription\Enums\SubscriptionStatus;
use App\Domains\Subscription\Models\Subscription;

final class SubscriptionEntitlementService
{
    public function resolve(string $organizationId): ?Subscription {
        return Subscription::where('organization_id', $organizationId)->first();
    }

    public function isAccessRestricted(string $organizationId): bool {
        $sub = $this->resolve($organizationId);
        return $sub ? $sub->isAccessRestricted() : false;
    }

    public function getAccessLevel(string $organizationId): string {
        return $this->isAccessRestricted($organizationId) ? 'restricted' : 'full';
    }

    /** Features always allowed regardless of subscription state. */
    public function isAlwaysAllowed(string $routeOrFeature): bool {
        $allowed = ['subscription','billing','payment','plan','organization.settings','clinic.settings','account','profile','logout','webhooks','health','up'];
        foreach ($allowed as $prefix) {
            if (str_starts_with($routeOrFeature, $prefix)) return true;
        }
        return false;
    }
}