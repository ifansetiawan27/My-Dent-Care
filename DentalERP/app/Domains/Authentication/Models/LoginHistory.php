<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Models;

use App\Core\Traits\HasUuid;
use App\Domains\Authentication\Enums\LoginStatus;
use App\Domains\Branch\Models\Branch;
use App\Domains\Organization\Models\Organization;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Login History — Operational History Projection.
 *
 * Immutable by default after creation. Only `logout_at` permits a
 * controlled one-time mutation from NULL to timestamp under DD-AUTH-017.
 * DD-AUTH-018: credential-change revocation does NOT invoke `logout_at`
 * mutation. Login History is NOT canonical audit evidence — that is
 * provided by separate Append Only + Immutable Audit Events (ADR-006).
 *
 * @property string            $id
 * @property string|null       $user_id
 * @property string|null       $organization_id
 * @property string|null       $branch_id
 * @property string|null       $device_id
 * @property string            $identifier
 * @property LoginStatus       $login_status
 * @property string|null       $failure_reason
 * @property string|null       $ip_address
 * @property string|null       $browser
 * @property string|null       $operating_system
 * @property string|null       $device_name
 * @property string|null       $country
 * @property string|null       $city
 * @property \Carbon\Carbon    $login_at
 * @property \Carbon\Carbon|null $logout_at
 */
class LoginHistory extends Model
{
    use HasUuid;

    public const UPDATED_AT = null;

    protected $table = 'login_histories';

    protected $fillable = [
        'user_id',
        'organization_id',
        'branch_id',
        'device_id',
        'identifier',
        'login_status',
        'failure_reason',
        'ip_address',
        'browser',
        'operating_system',
        'device_name',
        'country',
        'city',
        'login_at',
        'logout_at',
    ];

    protected $casts = [
        'login_status'  => LoginStatus::class,
        'login_at'      => 'datetime',
        'logout_at'     => 'datetime',
        'created_at'    => 'datetime',
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
        return $this->belongsTo(UserDevice::class, 'device_id', 'id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class, 'login_history_id', 'id');
    }
}
