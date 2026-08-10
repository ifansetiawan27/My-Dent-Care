<?php
declare(strict_types=1);
namespace App\Domains\Subscription\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Subscription\Models\Subscription */
class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $trial = $this->trial_ends_at ? [
            'start_date'    => $this->trial_starts_at?->toISOString(),
            'end_date'      => $this->trial_ends_at->toISOString(),
            'days_remaining' => max(0, (int) now()->diffInDays($this->trial_ends_at, false)),
        ] : null;

        $totalStorage = match ($this->plan_code) {
            'professional' => 50, 'enterprise' => 500, default => 10,
        };

        return [
            'id'                    => $this->id,
            'organization_id'       => $this->organization_id,
            'plan'                  => $this->plan_code,
            'status'                => $this->status->value,
            'status_label'          => $this->status->label(),
            'price'                => match ($this->plan_code) {
                'professional' => 399000, 'enterprise' => 499000, default => 299000,
            },
            'trial'                 => $trial,
            'is_trial'              => $this->status->value === 'trial',
            'is_restricted'         => $this->isAccessRestricted(),
            'billing'               => [
                'current_period_start' => $this->current_period_starts_at?->toISOString(),
                'current_period_end'   => $this->current_period_ends_at?->toISOString(),
                'next_billing_at'      => $this->next_billing_at?->toISOString(),
                'grace_ends_at'        => $this->grace_ends_at?->toISOString(),
            ],
            'storage'               => [
                'limit_gb'     => $totalStorage,
                'used_gb'      => 0, // Future: query FileStorage aggregate
                'remaining_gb' => $totalStorage,
            ],
            'created_at'            => $this->created_at?->toISOString(),
            'updated_at'            => $this->updated_at?->toISOString(),
        ];
    }
}