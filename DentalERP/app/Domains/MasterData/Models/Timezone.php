<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Models;

/**
 * Timezone
 *
 * IANA timezone reference. `code` stores the IANA identifier,
 * `name` stores the human-readable label. `offset` stores the UTC offset.
 *
 * @property string $offset
 */
class Timezone extends BaseMasterDataModel
{
    protected $table = 'timezones';

    /**
     * @var array<string>
     */
    protected $fillable = [
        'code',
        'name',
        'offset',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
