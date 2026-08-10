<?php
declare(strict_types=1);
namespace App\Domains\Subscription\Services;
use App\Domains\Subscription\Enums\SubscriptionStatus;
use App\Domains\Subscription\Enums\SubscriptionTrigger;
use App\Domains\Subscription\Exceptions\InvalidTransitionException;
use App\Domains\Subscription\Models\Subscription;
use App\Domains\Subscription\Models\SubscriptionTransition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class SubscriptionTransitionService
{
    /** @var array<string, array<string>> */
    private const ALLOWED = [
        'trial'      => ['active','expired'],
        'active'     => ['active','past_due','cancelled'],
        'past_due'   => ['active','grace'],
        'grace'      => ['active','expired'],
        'expired'    => ['active'],
        'cancelled'  => ['active'],
    ];

    /**
     * Execute a subscription state transition atomically.
     * The ONLY authorized mechanism for changing subscription status.
     */
    public function transition(
        Subscription $subscription,
        SubscriptionStatus $newState,
        SubscriptionTrigger $trigger,
        string $actorType = 'system',
        ?string $actorId = null,
        array $metadata = [],
        ?string $idempotencyKey = null,
    ): SubscriptionTransition {
        $previousState = $subscription->status;

        if ($this->isAlreadyProcessed($idempotencyKey)) {
            return $this->findExistingTransition($idempotencyKey);
        }

        $this->validateTransition($previousState, $newState);

        return DB::transaction(function () use ($subscription, $newState, $trigger, $previousState, $actorType, $actorId, $metadata, $idempotencyKey) {
            $subscription->update(['status' => $newState->value]);
            $subscription->refresh();

            $transition = SubscriptionTransition::create([
                'subscription_id'  => $subscription->id,
                'organization_id'  => $subscription->organization_id,
                'previous_state'   => $previousState->value,
                'new_state'        => $newState->value,
                'trigger'          => $trigger->value,
                'actor_type'       => $actorType,
                'actor_id'         => $actorId,
                'idempotency_key'  => $idempotencyKey,
                'metadata'         => $metadata,
                'created_at'       => now(),
            ]);

            Log::info('[SubscriptionTransitionService] Transition executed.', [
                'subscription_id'   => $subscription->id,
                'organization_id'   => $subscription->organization_id,
                'previous_state'    => $previousState->value,
                'new_state'         => $newState->value,
                'trigger'           => $trigger->value,
                'actor_type'        => $actorType,
            ]);

            return $transition;
        });
    }

    /** Validate the transition matrix. Throws on invalid. */
    public function validateTransition(SubscriptionStatus $from, SubscriptionStatus $to): void {
        $allowed = self::ALLOWED[$from->value] ?? [];
        if (!in_array($to->value, $allowed, true)) {
            throw new InvalidTransitionException($from->value, $to->value);
        }
    }

    /** Check whether a given transition is valid without executing. */
    public function isValid(SubscriptionStatus $from, SubscriptionStatus $to): bool {
        $allowed = self::ALLOWED[$from->value] ?? [];
        return in_array($to->value, $allowed, true);
    }

    /** Return the list of allowed next states for a given current state. */
    /** @return array<string> */
    public function allowedNextStates(SubscriptionStatus $from): array {
        return self::ALLOWED[$from->value] ?? [];
    }

    private function isAlreadyProcessed(?string $key): bool {
        return $key !== null && SubscriptionTransition::where('idempotency_key', $key)->exists();
    }

    private function findExistingTransition(?string $key): SubscriptionTransition {
        return SubscriptionTransition::where('idempotency_key', $key)->firstOrFail();
    }
}