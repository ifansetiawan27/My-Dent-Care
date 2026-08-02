<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Village
 *
 * Village (Kelurahan/Desa) reference. Belongs to a District.
 * Leaf of the geographic hierarchy.
 *
 * @property string      $district_id
 * @property string|null $postal_code
 */
class Village extends BaseMasterDataModel
{
    protected $table = 'villages';

    /**
     * @var array<string>
     */
    protected $fillable = [
        'district_id',
        'code',
        'name',
        'postal_code',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * @return BelongsTo<District, Village>
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id', 'id');
    }
}
