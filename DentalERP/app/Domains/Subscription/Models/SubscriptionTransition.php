<?php
declare(strict_types=1);
namespace App\Domains\Subscription\Models;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property string $id @property string $subscription_id @property string $organization_id @property string $previous_state @property string $new_state */
class SubscriptionTransition extends Model {
    use HasUuid;
    public $timestamps = false;
    protected $table = 'subscription_transitions';
    protected $fillable = ['subscription_id','organization_id','previous_state','new_state','trigger','actor_type','actor_id','idempotency_key','metadata','created_at'];
    protected $casts = ['metadata'=>'array','created_at'=>'datetime'];
    public function subscription(): BelongsTo { return $this->belongsTo(Subscription::class); }
    public function organization(): BelongsTo { return $this->belongsTo(\App\Domains\Organization\Models\Organization::class); }
}