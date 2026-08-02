<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Models;

/**
 * Language
 *
 * ISO 639-1 language reference.
 *
 * @property string|null $name_local
 */
class Language extends BaseMasterDataModel
{
    protected $table = 'languages';

    /**
     * @var array<string>
     */
    protected $fillable = [
        'code',
        'name',
        'name_local',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
