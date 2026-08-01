<?php

declare(strict_types=1);

namespace App\Core\Base;

use App\Core\Traits\HasAudit;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class BaseModel extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasAudit;

    /**
     * Disable mass assignment protection.
     * Security is enforced via FormRequest validation.
     *
     * @var array<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
        'deleted_at'        => 'datetime',
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    /**
     * The attributes that should be treated as dates.
     *
     * @var array<string>
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Default audit columns appended to every model.
     * Child models should merge with their own appends if needed.
     *
     * @var array<string>
     */
    protected $auditColumns = [
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();
    }

    /**
     * Get the primary key type.
     */
    public function getKeyType(): string
    {
        return 'string';
    }

    /**
     * Determine if the model uses auto-incrementing IDs.
     */
    public function getIncrementing(): bool
    {
        return false;
    }
}
