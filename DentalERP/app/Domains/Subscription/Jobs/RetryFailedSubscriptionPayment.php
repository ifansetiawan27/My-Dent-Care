<?php
declare(strict_types=1);
namespace App\Domains\Subscription\Jobs;
use App\Domains\Subscription\Enums\SubscriptionStatus;
use App\Domains\Subscription\Enums\SubscriptionTrigger;
use App\Domains\Subscription\Models\Subscription;
use App\Domains\Subscription\Services\IdempotencyService;
use App\Domains\Subscription\Services\SubscriptionTransitionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

final class RetryFailedSubscriptionPayment implements ShouldQueue
{
    use Queueable;
    public function handle(SubscriptionTransitionService $ts, IdempotencyService $idem): void {
        $pastDue = Subscription::where('status', SubscriptionStatus::PastDue)->where('updated_at', '<=', now()->subDays(3))->get();
        foreach ($pastDue as $sub) {
            $key = $idem->jobKey('RetryFailedPayment', $sub->id, now()->format('Y-m-d'));
            if ($idem->isProcessed($key)) continue;
            // Payment retry deferred to STEP_28_35. Transition to GRACE.
            $ts->transition($sub, SubscriptionStatus::Grace, SubscriptionTrigger::GraceStarted, 'system', null, [
                'grace_ends_at' => now()->addDays(7)->toISOString(),
            ], $key);
            $sub->update(['grace_starts_at' => now(), 'grace_ends_at' => now()->addDays(7)]);
            Log::info('[RetryFailedPayment] Entered grace period.', ['subscription_id' => $sub->id]);
        }
    }
}