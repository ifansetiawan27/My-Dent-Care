<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

/**
 * Access Token — Revocable Security Data.
 *
 * Extends Laravel Sanctum's PersonalAccessToken to add `session_id`
 * relationship. Each active Session owns exactly one active Access Token
 * enforced by UNIQUE (session_id) constraint.
 *
 * @property string            $id
 * @property string            $tokenable_type
 * @property string            $tokenable_id
 * @property string            $session_id
 * @property string            $name
 * @property string            $token
 * @property string|null       $abilities
 * @property \Carbon\Carbon|null $last_used_at
 * @property \Carbon\Carbon    $expires_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $fillable = [
        'tokenable_type',
        'tokenable_id',
        'name',
        'token',
        'abilities',
        'expires_at',
        'last_used_at',
        'session_id',
    ];

    protected $casts = [
        'abilities'    => 'json',
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(UserSession::class, 'session_id', 'id');
    }
}
