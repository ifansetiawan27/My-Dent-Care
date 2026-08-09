<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Models;

use App\Core\Traits\HasUuid;
use App\Domains\Branch\Models\Branch;
use App\Domains\Organization\Models\Organization;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * User Session — Mutable Operational State (Revocable, Expiring).
 *
 * The Authentication session boundary. Each successful authentication
 * creates exactly one Session. A device may own multiple Sessions.
 * Session lifecycle is independent from Device lifecycle per DD-AUTH-007.
 *
 * @property string            $id
 * @property string            $user_id
 * @property string            $organization_id
 * @property string            $branch_id
 * @property string            $user_device_id
 * @property string|null       $login_history_id
 * @property \Carbon\Carbon    $started_at
 * @property \Carbon\Carbon    $expires_at
 * @property \Carbon\Carbon|null $revoked_at
 * @property string|null       $revoke_reason
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class UserSession extends Model
{
    use HasUuid;

    protected $table = 'user_sessions';

    protected $fillable = [
        'user_id',
        'organization_id',
        'branch_id',
        'user_device_id',
        'login_history_id',
        'started_at',
        'expires_at',
        'revoked_at',
        'revoke_reason',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(UserDevice::class, 'user_device_id', 'id');
    }

    public function loginHistory(): BelongsTo
    {
        return $this->belongsTo(LoginHistory::class, 'login_history_id', 'id');
    }

    public function accessToken(): HasOne
    {
        return $this->hasOne(PersonalAccessToken::class, 'session_id', 'id');
    }

    public function refreshTokens(): HasMany
    {
        return $this->hasMany(RefreshToken::class, 'session_id', 'id');
    }
}
