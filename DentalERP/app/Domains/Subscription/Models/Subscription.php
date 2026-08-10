<?php
declare(strict_types=1);
namespace App\Domains\Subscription\Models;
use App\Core\Base\BaseModel;
use App\Domains\Subscription\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property string $organization_id @property SubscriptionStatus $status */
class Subscription extends BaseModel {
    protected $table = 'subscriptions';
    protected $fillable = ['organization_id','plan_code','status','trial_starts_at','trial_ends_at','current_period_starts_at','current_period_ends_at','next_billing_at','grace_starts_at','grace_ends_at','cancelled_at','reactivated_at','created_by','updated_by'];
    protected $casts = ['status' => SubscriptionStatus::class,'trial_starts_at'=>'datetime','trial_ends_at'=>'datetime','current_period_starts_at'=>'datetime','current_period_ends_at'=>'datetime','next_billing_at'=>'datetime','grace_starts_at'=>'datetime','grace_ends_at'=>'datetime','cancelled_at'=>'datetime','reactivated_at'=>'datetime','created_at'=>'datetime','updated_at'=>'datetime'];
    public function organization(): BelongsTo { return $this->belongsTo(\App\Domains\Organization\Models\Organization::class); }
    public function transitions(): HasMany { return $this->hasMany(SubscriptionTransition::class); }
    public function isAccessRestricted(): bool { return in_array($this->status, SubscriptionStatus::restrictedStates(), true); }
}