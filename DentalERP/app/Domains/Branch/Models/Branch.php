<?php

declare(strict_types=1);

namespace App\Domains\Branch\Models;

use App\Core\Base\BaseModel;
use App\Domains\Branch\Enums\BranchStatus;
use App\Domains\Branch\Factories\BranchFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Branch Model
 *
 * Represents a physical clinic location belonging to an Organization.
 * A Branch is the core operational unit in a multi-branch dental ERP.
 * All clinical, financial, and patient data is scoped to a Branch.
 *
 * @property string              $id
 * @property string              $organization_id
 * @property string              $branch_code
 * @property string              $branch_name
 * @property string              $branch_type
 * @property string|null         $email
 * @property string              $phone
 * @property string              $address
 * @property string              $city
 * @property string              $province
 * @property string              $country
 * @property string              $postal_code
 * @property string|null         $latitude
 * @property string|null         $longitude
 * @property string              $timezone
 * @property BranchStatus        $status
 * @property string|null         $created_by
 * @property string|null         $updated_by
 * @property string|null         $deleted_by
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Branch extends BaseModel
{
    // -------------------------------------------------------------------------
    // Table
    // -------------------------------------------------------------------------

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'branches';

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
        'branch_code',
        'branch_name',
        'branch_type',
        'email',
        'phone',
        'address',
        'city',
        'province',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'timezone',
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
     *
     * @var array<string>
     */
    protected $hidden = [
        'deleted_at',
        'deleted_by',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    /**
     * The attributes that should be cast to native types.
     * latitude and longitude use decimal:8 — returns string with 8 decimal places.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status'    => BranchStatus::class,
        'latitude'  => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // -------------------------------------------------------------------------
    // Factory
    // -------------------------------------------------------------------------

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): BranchFactory
    {
        return BranchFactory::new();
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * A Branch belongs to one Organization.
     *
     * @return BelongsTo<\App\Domains\Organization\Models\Organization, Branch>
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
     * A Branch has many Users.
     *
     * @return HasMany<\App\Domains\Authentication\Models\User>
     */
    public function users(): HasMany
    {
        /** @phpstan-ignore-next-line */
        return $this->hasMany(
            \App\Domains\Authentication\Models\User::class,
            'branch_id',
            'id',
        );
    }

    /**
     * A Branch has many Patients.
     *
     * @return HasMany<\App\Domains\Patient\Models\Patient>
     */
    public function patients(): HasMany
    {
        /** @phpstan-ignore-next-line */
        return $this->hasMany(
            \App\Domains\Patient\Models\Patient::class,
            'branch_id',
            'id',
        );
    }

    /**
     * A Branch has many Appointments.
     *
     * @return HasMany<\App\Domains\Appointment\Models\Appointment>
     */
    public function appointments(): HasMany
    {
        /** @phpstan-ignore-next-line */
        return $this->hasMany(
            \App\Domains\Appointment\Models\Appointment::class,
            'branch_id',
            'id',
        );
    }

    /**
     * A Branch has many Inventory items.
     *
     * @return HasMany<\App\Domains\Inventory\Models\Inventory>
     */
    public function inventories(): HasMany
    {
        /** @phpstan-ignore-next-line */
        return $this->hasMany(
            \App\Domains\Inventory\Models\Inventory::class,
            'branch_id',
            'id',
        );
    }

    /**
     * A Branch has many Finance Transactions.
     *
     * @return HasMany<\App\Domains\Finance\Models\FinanceTransaction>
     */
    public function financeTransactions(): HasMany
    {
        /** @phpstan-ignore-next-line */
        return $this->hasMany(
            \App\Domains\Finance\Models\FinanceTransaction::class,
            'branch_id',
            'id',
        );
    }
}
