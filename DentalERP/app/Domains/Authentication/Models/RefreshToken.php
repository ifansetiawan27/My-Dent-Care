<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Models;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Refresh Token — Revocable Security Data.
 *
 * Per-session token family. One active token per session (partial unique
 * index). Rotation replaces the predecessor via `replaced_by_id` chain.
 * Reuse revokes entire family + owning Session + descendant Access Token.
 *
 * @property string            $id
 * @property string            $session_id
 * @property string            $token_hash
 * @property \Carbon\Carbon    $expires_at
 * @property \Carbon\Carbon|null $last_used_at
 * @property \Carbon\Carbon|null $revoked_at
 * @property string|null       $replaced_by_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class RefreshToken extends Model
{
    use HasUuid;

    protected $table = 'refresh_tokens';

    protected $fillable = [
        'session_id',
        'token_hash',
        'expires_at',
        'last_used_at',
        'revoked_at',
        'replaced_by_id',
    ];

    protected $hidden = [
        'token_hash',
    ];

    protected $casts = [
        'expires_at'    => 'datetime',
        'last_used_at'  => 'datetime',
        'revoked_at'    => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(UserSession::class, 'session_id', 'id');
    }

    public function replacedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaced_by_id', 'id');
    }
}
