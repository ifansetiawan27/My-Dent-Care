<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * City
 *
 * City / regency reference. Belongs to a Province.
 *
 * @property string $province_id
 * @property string $type
 */
class City extends BaseMasterDataModel
{
    protected $table = 'cities';

    /**
     * @var array<string>
     */
    protected $fillable = [
        'province_id',
        'code',
        'name',
        'type',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * @return BelongsTo<Province, City>
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }

    /**
     * @return HasMany<District>
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class, 'city_id', 'id');
    }
}
