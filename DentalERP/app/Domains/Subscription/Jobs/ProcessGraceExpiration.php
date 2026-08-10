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

final class ProcessGraceExpiration implements ShouldQueue
{
    use Queueable;
    public function handle(SubscriptionTransitionService $ts, IdempotencyService $idem): void {
        $expired = Subscription::where('status', SubscriptionStatus::Grace)->where('grace_ends_at', '<=', now())->get();
        foreach ($expired as $sub) {
            $key = $idem->jobKey('ProcessGraceExpiration', $sub->id, $sub->grace_ends_at->format('Y-m-d'));
            if ($idem->isProcessed($key)) continue;
            $ts->transition($sub, SubscriptionStatus::Expired, SubscriptionTrigger::GraceExpired, 'system', null, [], $key);
            Log::info('[ProcessGraceExpiration] Grace expired.', ['subscription_id' => $sub->id]);
        }
    }
}