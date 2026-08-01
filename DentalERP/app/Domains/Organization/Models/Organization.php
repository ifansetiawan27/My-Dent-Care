<?php

declare(strict_types=1);

namespace App\Domains\Organization\Models;

use App\Core\Base\BaseModel;
use App\Domains\Organization\Enums\OrganizationStatus;
use App\Domains\Organization\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Organization Model
 *
 * Represents a top-level company / clinic group entity.
 * One Organization can have many Branches and many Users.
 *
 * @property string                  $id
 * @property string                  $company_code
 * @property string                  $company_name
 * @property string|null             $legal_name
 * @property string|null             $tax_number
 * @property string|null             $email
 * @property string|null             $phone
 * @property string|null             $website
 * @property string|null             $logo
 * @property string|null             $address
 * @property string|null             $city
 * @property string|null             $province
 * @property string                  $country
 * @property string|null             $postal_code
 * @property string                  $timezone
 * @property string                  $currency
 * @property OrganizationStatus      $status
 * @property string|null             $created_by
 * @property string|null             $updated_by
 * @property string|null             $deleted_by
 * @property \Carbon\Carbon|null     $created_at
 * @property \Carbon\Carbon|null     $updated_at
 * @property \Carbon\Carbon|null     $deleted_at
 *
 * @property-read bool               $is_active
 * @property-read string             $full_address
 */
class Organization extends BaseModel
{
    use HasFactory;

    // -------------------------------------------------------------------------
    // Table
    // -------------------------------------------------------------------------

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'organizations';

    // -------------------------------------------------------------------------
    // Mass Assignment
    // -------------------------------------------------------------------------

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'company_code',
        'company_name',
        'legal_name',
        'tax_number',
        'email',
        'phone',
        'website',
        'logo',
        'address',
        'city',
        'province',
        'country',
        'postal_code',
        'timezone',
        'currency',
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
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => OrganizationStatus::class,
    ];

    // -------------------------------------------------------------------------
    // Factory
    // -------------------------------------------------------------------------

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): OrganizationFactory
    {
        return OrganizationFactory::new();
    }

    // -------------------------------------------------------------------------
    // Accessors — Laravel 12 Attribute API
    // -------------------------------------------------------------------------

    /**
     * Determine whether the organization is currently active.
     *
     * @return Attribute<bool, never>
     */
    protected function isActive(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->status === OrganizationStatus::Active,
        );
    }

    /**
     * Get the full formatted address string.
     *
     * @return Attribute<string, never>
     */
    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: fn (): string => implode(', ', array_filter([
                $this->address,
                $this->city,
                $this->province,
                $this->postal_code,
                $this->country,
            ])),
        );
    }

    // -------------------------------------------------------------------------
    // Query Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope: only active organizations.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', OrganizationStatus::Active->value);
    }

    /**
     * Scope: only inactive organizations.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', OrganizationStatus::Inactive->value);
    }

    /**
     * Scope: filter by country.
     */
    public function scopeByCountry(Builder $query, string $country): Builder
    {
        return $query->where('country', $country);
    }

    /**
     * Scope: search by company name or company code.
     */
    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function (Builder $q) use ($keyword): void {
            $q->where('company_name', 'ILIKE', "%{$keyword}%")
              ->orWhere('company_code', 'ILIKE', "%{$keyword}%");
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * An Organization has many Branches.
     *
     * @return HasMany<\App\Domains\Branch\Models\Branch>
     */
    public function branches(): HasMany
    {
        /** @phpstan-ignore-next-line */
        return $this->hasMany(
            \App\Domains\Branch\Models\Branch::class,
            'organization_id',
            'id',
        );
    }

    /**
     * An Organization has many Users.
     *
     * @return HasMany<\App\Domains\Authentication\Models\User>
     */
    public function users(): HasMany
    {
        /** @phpstan-ignore-next-line */
        return $this->hasMany(
            \App\Domains\Authentication\Models\User::class,
            'organization_id',
            'id',
        );
    }
}
