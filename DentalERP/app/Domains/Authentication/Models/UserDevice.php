<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Models;

use App\Core\Traits\HasUuid;
use App\Domains\Authentication\Enums\DeviceType;
use App\Domains\Branch\Models\Branch;
use App\Domains\Organization\Models\Organization;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * User Device — Mutable Operational State (Revocable).
 *
 * Represents a recognized client device (browser, mobile, tablet, API).
 * Device lifecycle is independent from Session lifecycle per DD-AUTH-007.
 * API field `is_active` is derived from `revoked_at IS NULL` — not stored.
 *
 * @property string            $id
 * @property string            $user_id
 * @property string            $organization_id
 * @property string            $branch_id
 * @property string            $device_uuid
 * @property string|null       $device_name
 * @property DeviceType        $device_type
 * @property string|null       $platform
 * @property string|null       $user_agent
 * @property string|null       $browser
 * @property string|null       $operating_system
 * @property string|null       $ip_address
 * @property \Carbon\Carbon|null $last_login_at
 * @property \Carbon\Carbon|null $last_activity_at
 * @property bool              $is_trusted
 * @property \Carbon\Carbon|null $revoked_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class UserDevice extends Model
{
    use HasUuid;

    protected $table = 'user_devices';

    protected $fillable = [
        'user_id',
        'organization_id',
        'branch_id',
        'device_uuid',
        'device_name',
        'device_type',
        'platform',
        'user_agent',
        'browser',
        'operating_system',
        'ip_address',
        'last_login_at',
        'last_activity_at',
        'is_trusted',
        'revoked_at',
    ];

    protected $casts = [
        'device_type'      => DeviceType::class,
        'is_trusted'       => 'boolean',
        'last_login_at'    => 'datetime',
        'last_activity_at' => 'datetime',
        'revoked_at'       => 'datetime',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
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

    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class, 'user_device_id', 'id');
    }

    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class, 'device_id', 'id');
    }
}
