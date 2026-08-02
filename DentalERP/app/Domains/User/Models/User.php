<?php

declare(strict_types=1);

namespace App\Domains\User\Models;

use App\Core\Base\BaseModel;
use App\Domains\User\Enums\UserGender;
use App\Domains\User\Enums\UserStatus;
use App\Domains\User\Factories\UserFactory;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * User Model
 *
 * Represents an authenticated system user (staff / employee) of a dental clinic branch.
 * Implements Laravel Sanctum for API token authentication.
 * Roles and permissions are managed via Spatie Laravel Permission.
 *
 * @property string                  $id
 * @property string                  $organization_id
 * @property string                  $branch_id
 * @property string                  $employee_code
 * @property string                  $name
 * @property string                  $username
 * @property string                  $email
 * @property string|null             $phone
 * @property string                  $password
 * @property string|null             $photo
 * @property UserGender|null         $gender
 * @property \Carbon\Carbon|null     $birth_date
 * @property \Carbon\Carbon|null     $last_login_at
 * @property \Carbon\Carbon|null     $email_verified_at
 * @property UserStatus              $status
 * @property string|null             $created_by
 * @property string|null             $updated_by
 * @property string|null             $deleted_by
 * @property \Carbon\Carbon|null     $created_at
 * @property \Carbon\Carbon|null     $updated_at
 * @property \Carbon\Carbon|null     $deleted_at
 */
class User extends BaseModel implements
    AuthenticatableContract,
    AuthorizableContract,
    MustVerifyEmailContract
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use AuthenticatableTrait;
    use Authorizable;
    use MustVerifyEmail;
    use Notifiable;

    // -------------------------------------------------------------------------
    // Table
    // -------------------------------------------------------------------------

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    // -------------------------------------------------------------------------
    // Mass Assignment
    // -------------------------------------------------------------------------

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'organization_id',
        'branch_id',
        'employee_code',
        'name',
        'username',
        'email',
        'phone',
        'password',
        'photo',
        'gender',
        'birth_date',
        'last_login_at',
        'email_verified_at',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // -------------------------------------------------------------------------
    // Hidden
    // -------------------------------------------------------------------------

    /**
     * The attributes that should be hidden for serialization.
     * Password and tokens are always hidden from API responses.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'deleted_at',
        'deleted_by',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    /**
     * The attributes that should be cast to native types.
     * Overrides BaseModel casts to add User-specific casts.
     * Note: All parent casts must be re-declared here due to PHP property inheritance.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // User-specific
        'status'            => UserStatus::class,
        'gender'            => UserGender::class,
        'birth_date'        => 'date',
        'last_login_at'     => 'datetime',
        // Inherited from BaseModel (must be re-declared)
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
        'deleted_at'        => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Factory
    // -------------------------------------------------------------------------

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * User belongs to one Organization.
     *
     * @return BelongsTo<\App\Domains\Organization\Models\Organization, User>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(
            \App\Domains\Organization\Models\Organization::class,
            'organization_id',
            'id',
        );
    }

    /**
     * User is assigned to one Branch.
     *
     * @return BelongsTo<\App\Domains\Branch\Models\Branch, User>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(
            \App\Domains\Branch\Models\Branch::class,
            'branch_id',
            'id',
        );
    }

    /**
     * User who created this record (self-referential).
     *
     * @return BelongsTo<User, User>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'created_by',
            'id',
        );
    }

    /**
     * User who last updated this record (self-referential).
     *
     * @return BelongsTo<User, User>
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'updated_by',
            'id',
        );
    }

    // -------------------------------------------------------------------------
    // Sanctum — API Token Authentication
    // -------------------------------------------------------------------------

    /**
     * Override: User model uses UUID as primary key.
     * Sanctum needs to know the key is a string, not integer.
     */
    public function getKeyType(): string
    {
        return 'string';
    }

    /**
     * Override: Disable auto-incrementing for UUID primary key.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    // -------------------------------------------------------------------------
    // Authenticatable Overrides
    // -------------------------------------------------------------------------

    /**
     * Override: No remember_token column — API-only auth via Sanctum.
     * Returns empty string to prevent DB lookup for non-existent column.
     */
    public function getRememberTokenName(): string
    {
        return '';
    }
}
