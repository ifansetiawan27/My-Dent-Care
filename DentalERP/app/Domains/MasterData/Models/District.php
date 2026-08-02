<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * District
 *
 * District (Kecamatan) reference. Belongs to a City.
 *
 * @property string $city_id
 */
class District extends BaseMasterDataModel
{
    protected $table = 'districts';

    /**
     * @var array<string>
     */
    protected $fillable = [
        'city_id',
        'code',
        'name',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * @return BelongsTo<City, District>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    /**
     * @return HasMany<Village>
     */
    public function villages(): HasMany
    {
        return $this->hasMany(Village::class, 'district_id', 'id');
    }
}
