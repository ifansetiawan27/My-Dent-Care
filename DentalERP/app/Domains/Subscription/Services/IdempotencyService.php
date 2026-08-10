<?php
declare(strict_types=1);
namespace App\Domains\Subscription\Services;
use App\Domains\Subscription\Models\SubscriptionTransition;

final class IdempotencyService
{
    /** Check if an idempotency key has already been processed for subscription transitions. */
    public function isProcessed(string $key): bool {
        return SubscriptionTransition::where('idempotency_key', $key)->exists();
    }

    /** Retrieve the previously processed transition for a given key. Returns null if not found. */
    public function findTransition(string $key): ?SubscriptionTransition {
        return SubscriptionTransition::where('idempotency_key', $key)->first();
    }

    /** Generate a deterministic idempotency key for subscription operations. */
    public function key(string $prefix, string $resourceId, string $operation): string {
        return \sprintf('%s:%s:%s', $prefix, $resourceId, $operation);
    }

    /** Generate a key for webhook/payment events. */
    public function webhookKey(string $provider, string $eventId): string {
        return \sprintf('webhook:%s:%s', $provider, $eventId);
    }

    /** Generate a key for scheduled jobs. */
    public function jobKey(string $jobName, string $subscriptionId, string $period): string {
        return \sprintf('job:%s:%s:%s', $jobName, $subscriptionId, $period);
    }
}